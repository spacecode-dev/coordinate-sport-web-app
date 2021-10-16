<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public $upcoming_events = array();
	public $upcoming_events_individuals = array();

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, ['coaching', 'fulltimecoach'], [], ['participants']);

		// get upcoming events (children)
		$where = array(
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings.accountID' => $this->auth->user->accountID,
			'bookings.project' => 1,
			'project_types.exclude_from_participant_booking_lists !=' => 1
		);
		$where_in = array(
			'children',
			'children_bikeability',
			'children_shapeup',
			'adults_children'
		);
		$blocks = $this->db->select('bookings_blocks.blockID, bookings_blocks.startDate, bookings_blocks.endDate, bookings.name as event, bookings_blocks.name as block, bookings.min_age as booking_min_age, bookings.max_age as booking_max_age, bookings_blocks.min_age as block_min_age, bookings_blocks.max_age as block_max_age')
		->from('bookings_blocks')
		->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
		->join('project_types', 'bookings.project_typeID = project_types.typeID', 'inner')
		->where($where)
		->where_in('bookings.register_type', $where_in)
		->order_by('bookings_blocks.startDate asc, bookings.name asc, bookings_blocks.name asc')->get();

		if ($blocks->num_rows() > 0) {
			foreach ($blocks->result() as $block) {
				$min_age = $this->settings_library->get('min_age');
				if (!empty($block->booking_min_age)) {
					$min_age = $block->booking_min_age;
				}
				if (!empty($block->block_min_age)) {
					$min_age = $block->block_min_age;
				}
				$max_age = $this->settings_library->get('max_age');
				if (!empty($block->booking_max_age)) {
					$max_age = $block->booking_max_age;
				}
				if (!empty($block->block_max_age)) {
					$max_age = $block->block_max_age;
				}
				$this->upcoming_events[$block->blockID] = array(
					'label' => mysql_to_uk_date($block->startDate) . ' - ' . $block->event . ' (' . $block->block . ')',
					'min_age' => $min_age,
					'max_age' => $max_age,
					'age_at' => $block->endDate
				);
			}
		}

		// get upcoming events (individuals)
		$where = array(
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d %H:%i:%s'),
			'bookings.accountID' => $this->auth->user->accountID,
			'bookings.project' => 1,
			'project_types.exclude_from_participant_booking_lists !=' => 1
		);
		$where_in = array(
			'individuals',
			'individuals_bikeability',
			'individuals_shapeup',
			'adults_children'
		);
		$blocks = $this->db->select('bookings_blocks.blockID, bookings_blocks.startDate, bookings_blocks.endDate, bookings.name as event, bookings_blocks.name as block, bookings.min_age as booking_min_age, bookings.max_age as booking_max_age, bookings_blocks.min_age as block_min_age, bookings_blocks.max_age as block_max_age')
		->from('bookings_blocks')
		->join('bookings', 'bookings_blocks.bookingID = bookings.bookingID', 'inner')
		->join('project_types', 'bookings.project_typeID = project_types.typeID', 'inner')
		->where($where)
		->where_in('bookings.register_type', $where_in)
		->order_by('bookings_blocks.startDate asc, bookings.name asc, bookings_blocks.name asc')->get();

		if ($blocks->num_rows() > 0) {
			foreach ($blocks->result() as $block) {
				$min_age = $this->settings_library->get('min_age');
				if (!empty($block->booking_min_age)) {
					$min_age = $block->booking_min_age;
				}
				if (!empty($block->block_min_age)) {
					$min_age = $block->block_min_age;
				}
				$max_age = $this->settings_library->get('max_age');
				if (!empty($block->booking_max_age)) {
					$max_age = $block->booking_max_age;
				}
				if (!empty($block->block_max_age)) {
					$max_age = $block->block_max_age;
				}
				$this->upcoming_events_individuals[$block->blockID] = array(
					'label' => mysql_to_uk_date($block->startDate) . ' - ' . $block->event . ' (' . $block->block . ')',
					'min_age' => $min_age,
					'max_age' => $max_age,
					'age_at' => $block->endDate
				);
			}
		}
	}

	/**
	 * show list of families
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'users';
		$current_page = 'participants';
		$section = 'participants';
		$type = 'participants';
		$page_base = 'participants';
		$title = $this->settings_library->get_label('participants');
		$buttons = '<a class="btn btn-success" href="' . site_url('participants/new-account') . '"><i class="far fa-plus"></i> New Account</a>';
		if ($this->settings_library->get_permission_level_label($this->auth->user->department) == "Super User") {
			$buttons .= ' <a class="btn" href="' . site_url('participants/tools') . '"><i class="far fa-cog"></i> Tools</a>';
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'family.accountID' => $this->auth->user->accountID
		);

		$export = FALSE;

		// set up search
		$search_where = NULL;
		$search_fields = array(
			'parent' => NULL,
			'child' => NULL,
			'postcode' => NULL,
			'county' => NULL,
			'phone' => NULL,
			'email' => NULL,
			'org_id' => NULL,
			'filter' => NULL,
			'min_age' => NULL,
			'max_age' => NULL,
			'booking_cart' => NULL,
			'is_active' => 1,
			'is_balance_due' => NULL,
			'transaction_ref' => NULL,
			'search' => NULL,
			'participant_order' => ''
		);

		$form_submitted = false;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_parent', 'Parent', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child', 'Child', 'trim|xss_clean');
			$this->form_validation->set_rules('search_postcode', 'Postcode', 'trim|xss_clean');
			$this->form_validation->set_rules('search_county', localise('county'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_phone', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('search_email', 'Email', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org_id', 'School', 'trim|xss_clean');
			$this->form_validation->set_rules('search_filter', 'Filter', 'trim|xss_clean');
			$this->form_validation->set_rules('search_min_age', 'Min Age', 'trim|xss_clean');
			$this->form_validation->set_rules('search_max_age', 'Max Age', 'trim|xss_clean');
			$this->form_validation->set_rules('search_booking_cart', 'Booking Cart Full', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search_transaction_ref', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('participant_order', 'Particiapnt Order', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['parent'] = set_value('search_parent');
			$search_fields['child'] = set_value('search_child');
			$search_fields['postcode'] = set_value('search_postcode');
			$search_fields['county'] = set_value('search_county');
			$search_fields['phone'] = set_value('search_phone');
			$search_fields['email'] = set_value('search_email');
			$search_fields['org_id'] = set_value('search_org_id');
			$search_fields['filter'] = set_value('search_filter');
			$search_fields['min_age'] = set_value('search_min_age');
			$search_fields['max_age'] = set_value('search_max_age');
			$search_fields['booking_cart'] = set_value('search_booking_cart');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['is_balance_due'] = set_value('search_is_balance_due');
			$search_fields['transaction_ref'] = set_value('search_transaction_ref');
			$search_fields['search'] = set_value('search');
			$search_fields['participant_order'] = set_value('participant_order');

			$is_search = TRUE;
			$form_submitted = TRUE;

		} else if (($this->crm_library->last_segment() == 'recall' || $this->uri->segment(2) == 'page' || $this->uri->segment(2) == 'view') && is_array($this->session->userdata('search-' . $type))) {

			foreach ($this->session->userdata('search-' . $type) as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;
			$form_submitted = TRUE;

		}

		if(isset($is_search) && $is_search !== TRUE){
			$this->session->unset_userdata('search-' . $type);
		}


		if ($this->input->get('order')) {
			$order = 'child_first asc, child_last asc, contact_first asc, contact_last asc';
		} else {
			$order = 'contact_first asc, contact_last asc, child_first asc, child_last asc';
		}


		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			//$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-' . $type, $search_fields);

			if ($search_fields['parent'] != '') {
				$search_where[] = "CONCAT_WS(' ', " . $this->db->dbprefix('family_contacts').".`title`, " . $this->db->dbprefix('family_contacts').".`first_name`, " . $this->db->dbprefix('family_contacts').".`last_name`) LIKE '%" . $this->db->escape_like_str($search_fields['parent']) . "%'";
			}

			if ($search_fields['child'] != '') {
				$search_where[] = "CONCAT_WS(' ', " . $this->db->dbprefix('family_children').".`first_name`, " . $this->db->dbprefix('family_children').".`last_name`) LIKE '%" . $this->db->escape_like_str($search_fields['child']) . "%'";
			}

			if ($search_fields['county'] != '') {
				$search_where[] = $this->db->dbprefix('family_contacts').".`county` LIKE '%" . $this->db->escape_like_str($search_fields['county']) . "%'";
			}

			if ($search_fields['postcode'] != '') {
				$search_where[] = $this->db->dbprefix('family_contacts').".`postcode` LIKE '%" . $this->db->escape_like_str($search_fields['postcode']) . "%'";
			}

			if ($search_fields['phone'] != '') {
				$search_where[] = "(" . $this->db->dbprefix('family_contacts').".`phone` LIKE '%" . $this->db->escape_like_str($search_fields['phone']) . "%' OR " . $this->db->dbprefix('family_contacts').".`mobile` LIKE '%" . $this->db->escape_like_str($search_fields['phone']) . "%' OR " . $this->db->dbprefix('family_contacts').".`workPhone` LIKE '%" . $this->db->escape_like_str($search_fields['phone']) . "%')";
			}

			if ($search_fields['email'] != '') {
				$search_where[] = $this->db->dbprefix('family_contacts').".`email` LIKE '%" . $this->db->escape_like_str($search_fields['email']) . "%'";
			}

			if ($search_fields['org_id'] != '') {
				$search_where[] = $this->db->dbprefix('family_children').".`orgID` = " . $this->db->escape($search_fields['org_id']);
			}

			if ($search_fields['min_age'] != '') {
				$search_fields['min_age'] = intval($search_fields['min_age']);
				$minDOB = date("Y-m-d", strtotime('-' . $search_fields['min_age'] . ' years'));
				$search_where[] = $this->db->dbprefix('family_children').".`dob` <= " . $this->db->escape($minDOB);
			}

			if ($search_fields['max_age'] != '') {
				$search_fields['max_age'] = intval($search_fields['max_age']);
				$maxDOB = date("Y-m-d", strtotime('-' . $search_fields['max_age'] . ' years'));
				$search_where[] = $this->db->dbprefix('family_children').".`dob` > " . $this->db->escape($maxDOB);
			}

			if ($search_fields['booking_cart'] != '') {
				switch ($search_fields['booking_cart']) {
					case 'full':
						$search_where[] = $this->db->dbprefix('bookings_cart_sessions') . ".`sessionID` IS NOT NULL";
						break;
					case 'empty':
						$search_where[] = $this->db->dbprefix('bookings_cart_sessions') . ".`sessionID` IS NULL";
						break;
				}
			}

			if ($search_fields['transaction_ref'] != '') {
				$search_where[] = $this->db->dbprefix('family_payments').".`transaction_ref` = " . $this->db->escape($search_fields['transaction_ref']);
				$group_by = 'family.familyID';

				// store search fields for when view payments page
				$payment_search_fields = [
					'transaction_ref' => $search_fields['transaction_ref'],
					'search' => 'true'
				];
				$this->session->set_userdata('search-family-payments', $payment_search_fields);
			}
		}


		if ($search_fields['is_active'] != '') {
			$search_where[] = $this->db->dbprefix('family_contacts').".`active` = " . $this->db->escape($search_fields['is_active']);
		}

		//Filter by Balance
		if ($search_fields['is_balance_due'] == 1) {
			$search_where[] = $this->db->dbprefix('family').".`account_balance` < 0";
		}else if($search_fields['is_balance_due'] == 2) {
			$search_where[] = $this->db->dbprefix('family').".`account_balance` >= 0 ";
		}else if($search_fields['is_balance_due'] == 3){
			$group_by = 'family.familyID';
			$export = TRUE;
			$this->pagination_library->is_search();
		}

		switch ($search_fields['filter']) {
			default:
				$search_where[] = '(' . $this->db->dbprefix('family_children') . '.`childID` IS NOT NULL OR ' . $this->db->dbprefix('family_contacts'). '.`contactID` IS NOT NULL)';
				break;
			case 'children':
				$search_where[] = '' . $this->db->dbprefix('family_children') . '.`childID` IS NOT NULL';
				break;
			case 'orphanParents':
				$search_where[] = '' . $this->db->dbprefix('family_children') . '.`childID` IS NULL';
				$search_where[] = '' . $this->db->dbprefix('family_contacts'). '.`contactID` IS NOT NULL';
				break;
			case 'orphanChildren':
				$search_where[] = '' . $this->db->dbprefix('family_children') . '.`childID` IS NOT NULL';
				$search_where[] = '' . $this->db->dbprefix('family_contacts'). '.`contactID` IS NULL';
				break;
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// check group by not overridden from search
		if (!isset($group_by)) {
			$group_by = 'family_contacts.contactID, family_children.childID';
		}

		// run query
		$res_all = $this->db->select('family.familyID, family_contacts.contactID, family_contacts.title as contact_title,
		 	family_contacts.first_name as contact_first, family_contacts.last_name as contact_last,
		  	family_contacts.postcode, family_contacts.county, family_contacts.phone, family_contacts.mobile,
		   	family_contacts.workPhone, family_contacts.email, family_children.childID, family_contacts.active,
		    family_children.first_name as child_first, family_children.last_name as child_last, orgs.name as school,
		 	family_children.dob as child_dob, family_contacts.dob as contact_dob, family_contacts.profile_pic as profile_pic,
		  	bookings_cart_sessions.sessionID AS sample_sessionID, family.account_balance')
		->from('family')
		->join('family_contacts', 'family_contacts.familyID = family.familyID', 'left')
		->join('family_children', 'family_children.familyID = family.familyID', 'left')
		->join('orgs', 'family_children.orgID = orgs.orgID', 'left')
		->join('bookings_cart', 'family_contacts.contactID = bookings_cart.contactID and bookings_cart.type = \'cart\'', 'left')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID and bookings_cart_sessions.date >= \'' . date('Y-m-d') . '\'', 'left')
		->join('family_payments', 'family_payments.familyID = family.familyID', 'left')
		->order_by('contact_first asc, contact_last asc, child_first asc, child_last asc')
		->group_by($group_by)
		->where($where)
		->where($search_where, NULL, FALSE)
		->get();

		// if only 1 results and searching for transaction reference, redirect
		if (!empty($search_fields['transaction_ref']) && $res_all->num_rows() === 1) {
			foreach ($res_all->result() as $row) {
				redirect('participants/payments/' . $row->familyID . '/recall#results');
				exit();
			}
		}

		// workout pagination
		$total_items = $res_all->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$this->db->select('family.familyID, family_contacts.contactID, family_contacts.title as contact_title,
		 	family_contacts.first_name as contact_first, family_contacts.last_name as contact_last,
		  	family_contacts.postcode, family_contacts.county, family_contacts.phone, family_contacts.mobile,
		   	family_contacts.workPhone, family_contacts.email, family_children.childID, family_contacts.active,
			family_children.first_name as child_first, family_children.last_name as child_last, orgs.name as school,
		 	family_children.dob as child_dob, family_contacts.dob as contact_dob, family_contacts.profile_pic as profile_pic,
		  	bookings_cart_sessions.sessionID AS sample_sessionID, family.account_balance')
		->from('family')
		->join('family_contacts', 'family_contacts.familyID = family.familyID', 'left')
		->join('family_children', 'family_children.familyID = family.familyID', 'left')
		->join('orgs', 'family_children.orgID = orgs.orgID', 'left')
		->join('bookings_cart', 'family_contacts.contactID = bookings_cart.contactID and bookings_cart.type = \'cart\'', 'left')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID and bookings_cart_sessions.date >= \'' . date('Y-m-d') . '\'', 'left')
		->join('family_payments', 'family_payments.familyID = family.familyID', 'left')
		->order_by($order)
		->group_by($group_by)
		->where($where)
		->where($search_where, NULL, FALSE);
		if($export == FALSE){
			$this->db->limit($this->pagination_library->amount, $this->pagination_library->start);
		}
		$res = $this->db->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$where = array(
			'type' => 'school',
			'accountID' => $this->auth->user->accountID
		);
		$schools = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'type' => $type,
			'families' => $res,
			'schools' => $schools,
			'page_base' => $page_base,
			'upcoming_events' => $this->upcoming_events,
			'upcoming_events_individuals' => $this->upcoming_events_individuals,
			'form_submitted' => $form_submitted,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		if ($export === TRUE) {


			$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->setTitle('participant_oustanding_balance');
			$sheet->setCellValue('A1', 'Account Holder');
			$sheet->setCellValue('B1', 'Account Balance');
			$sheet->setCellValue('C1', 'Email Address');
			$sheet->setCellValue('D1', 'Phone Number');

			// style for header
			$styleArray = [
				'font' => [
					'bold' => true,
				]
			];

			$sheet->getStyle('A1:D1')
				->applyFromArray($styleArray);

			$counter = 2;
			if ($res->num_rows() == 0) {
				$sheet->setCellValue('A2', 'No Data');
			}else{
				foreach ($res->result() as $row) {
					if($row->account_balance < 0){
						$sheet->setCellValue('A'.$counter, $row->contact_first.' '.$row->contact_last);
						$sheet->setCellValue('B'.$counter, $row->account_balance);
						$sheet->setCellValue('C'.$counter, $row->email);
						$sheet->setCellValue('D'.$counter, $row->phone);
						$counter++;
					}
				}
			}

			$spreadsheet->createSheet();
			$spreadsheet->setActiveSheetIndex(1)->setCellValue('A1', 'Account Holder');
			$spreadsheet->setActiveSheetIndex(1)->setCellValue('B1', 'Account Balance');
			$spreadsheet->setActiveSheetIndex(1)->setCellValue('C1', 'Email Address');
			$spreadsheet->setActiveSheetIndex(1)->setCellValue('D1', 'Phone Number');

			$spreadsheet->setActiveSheetIndex(1)->getStyle('A1:D1')
				->applyFromArray($styleArray);

			$counter = 2;
			if ($res->num_rows() == 0) {
				$spreadsheet->setCellValue('A2', 'No Data');
			}else{
				foreach ($res->result() as $row) {
					if($row->account_balance >= 0){
						$spreadsheet->setActiveSheetIndex(1)->setCellValue('A'.$counter, $row->contact_first.' '.$row->contact_last);
						$spreadsheet->setActiveSheetIndex(1)->setCellValue('B'.$counter, $row->account_balance);
						$spreadsheet->setActiveSheetIndex(1)->setCellValue('C'.$counter, $row->email);
						$spreadsheet->setActiveSheetIndex(1)->setCellValue('D'.$counter, $row->phone);
						$counter++;
					}
				}
			}

			$spreadsheet->getActiveSheet()->setTitle('participant_balance');
			$spreadsheet->setActiveSheetIndex(0);

			$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');

			header('Content-Type: application/vnd.ms-excel"');
			header('Content-Disposition: attachment; filename=participant_balances.xls');
			$writer->save('php://output');

		}else{
			// load view
			$this->crm_view('participants/main', $data);
		}
	}

	public function active($contactId = NULL, $value = NULL) {
		// check params
		if (empty($contactId) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'contactID' => $contactId,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$contactInfo = $row;

			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('family_contacts', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}

	/**
	 * view a family
	 * @param  int $familyID
	 * @return void
	 */
	public function view($familyID = NULL) {

		if ($familyID == NULL || !ctype_digit($familyID)) {
			show_404();
		}

		// set defaults
		$icon = 'users';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url('participants') . '"><i class="far fa-angle-left"></i> Return to List</a> <a class="btn btn-success" href="' . site_url('participants/contacts/' . $familyID . '/new') . '"><i class="far fa-plus"></i> Create New Account Holder</a> <a class="btn btn-success" href="' . site_url('participants/participant/' . $familyID . '/new') . '"><i class="far fa-plus"></i> Create New Participant</a>';
		$title = 'Participant Account';
		$tab = 'details';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants'
		);

		// check family exists
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		foreach ($query->result() as $row) {
			$family_info = $row;
		}

		// all ok

		// if updating credit limit
		if ($this->input->post() && $this->input->post('credit_limit') !== NULL && $this->settings_library->get('enable_credit_limits') == 1) {
			$credit_limit = $this->input->post('credit_limit');
			$max_credit_limit = $this->settings_library->get('max_credit_limit');
			if (!is_numeric($credit_limit)) {
				$error = 'Credit limit must be numeric';
			} else if ($credit_limit < 0) {
				$error = 'Credit limit must not be less than zero';
			} else if (!empty($max_credit_limit) && $credit_limit > $max_credit_limit) {
				$error = 'Credit limit must not be over the maximum of ' . currency_symbol() . number_format($max_credit_limit, 2);
			} else {
				// update, use where from above
				$data = array(
					'credit_limit' => $credit_limit,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$this->db->update('family', $data, $where, 1);
				if ($this->db->affected_rows() > 0){
					$success = 'Credit limit updated successfully';
					$this->session->set_flashdata('success', $success);
					redirect('participants/view/' . $familyID);
					return TRUE;
				}
			}
		}

		// get contacts
		$contacts = $this->db->from('family_contacts')->where($where)->order_by('main desc, first_name asc, last_name asc')->get();

		// get children
		unset($where['accountID']);
		$where['family_children.accountID'] = $this->auth->user->accountID;
		$children = $this->db->select('family_children.*, orgs.name as school')->from('family_children')->join('orgs', 'family_children.orgID = orgs.orgID', 'left')->where($where)->order_by('first_name asc, last_name asc')->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'familyID' => $familyID,
			'family' => $family_info,
			'contacts' => $contacts,
			'children' => $children,
			'upcoming_events' => $this->upcoming_events,
			'upcoming_events_individuals' => $this->upcoming_events_individuals,
			'main_contact' => NULL,
			'tab' => $tab,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('participants/family', $data);

	}

	/**
	 * create new family
	 * @return void
	 */
	public function new_family()
	{

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Participant';
		$submit_to = 'participants/new';
		$return_to = 'participants';
		$icon = 'users';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$add_school = 0;
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants'
		);

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {

			if ($this->input->post('add_school') == 1) {
				$add_school = 1;
			}

			// set validation rules
			$this->form_validation->set_rules('child_first_name', 'First Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('child_last_name', 'Last Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('child_dob', 'Date of Birth', 'trim|xss_clean|required|callback_check_dob');
			$this->form_validation->set_rules('child_gender', 'Gender', 'trim|xss_clean');
			if ($add_school != 1) {
				$this->form_validation->set_rules('child_orgID', 'School', 'trim|xss_clean|required');
			} else {
				$this->form_validation->set_rules('new_school', 'School', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('child_medical', 'Medical Notes', 'trim|xss_clean');
			$this->form_validation->set_rules('child_disability_info', 'Disability Information', 'trim|xss_clean');
			$this->form_validation->set_rules('child_behavioural_information', 'Behavioural Information', 'trim|xss_clean');
			$this->form_validation->set_rules('child_ethnic_origin', 'Ethnic Origin', 'trim|xss_clean');
			$this->form_validation->set_rules('child_photoConsent', 'Photo Consent', 'trim|xss_clean');

			$this->form_validation->set_rules('child_emergency_contact_1_name', 'Child Emergency Contact 1 Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('child_emergency_contact_1_phone', 'Child Emergency Contact 1 Phone', 'trim|xss_clean|required');
			$this->form_validation->set_rules('child_emergency_contact_2_name', 'Child Emergency Contact 2 Name', 'trim|xss_clean');
			$this->form_validation->set_rules('child_emergency_contact_2_phone', 'Child Emergency Contact 2 Phone', 'trim|xss_clean');

			$this->form_validation->set_rules('title', 'Title', 'trim|xss_clean');
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean');
			if ($this->settings_library->get('require_dob') == 1) {
				$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean|callback_check_dob');
			} else {
				$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|xss_clean|callback_check_dob');
			}
			$this->form_validation->set_rules('medical', 'Medical Notes', 'trim|xss_clean');
			$this->form_validation->set_rules('behavioural_information', 'Behavioural Information', 'trim|xss_clean');
			$this->form_validation->set_rules('disability_info', 'Disability Information', 'trim|xss_clean');
			$this->form_validation->set_rules('ethnic_origin', 'Ethnic Origin', 'trim|xss_clean');
			$this->form_validation->set_rules('relationship', 'Relationship', 'trim|xss_clean');
			$this->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean');
			$this->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean');
			$this->form_validation->set_rules('address3', 'Address 3', 'trim|xss_clean');
			$this->form_validation->set_rules('town', 'Town', 'trim|xss_clean');
			$this->form_validation->set_rules('county', localise('county'), 'trim|xss_clean');
			$this->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean|required|callback_check_postcode');
			if ($this->settings_library->get('require_mobile') == 1) {
				$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|required|callback_check_mobile');
			} else {
				$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|callback_check_mobile');
			}
			$this->form_validation->set_rules('phone', 'Other Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('workPhone', 'Work Phone', 'trim|xss_clean');
			if ($this->settings_library->get('require_participant_email') == 1) {
				$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email|callback_check_email');
			} else {
				$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|callback_check_email');
			}

			$this->form_validation->set_rules('emergency_contact_1_name', 'Emergency Contact 1 Name', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_1_phone', 'Emergency Contact 1 Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_2_name', 'Emergency Contact 2 Name', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_2_phone', 'Emergency Contact 2 Phone', 'trim|xss_clean');

			$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');
			$this->form_validation->set_rules('notify', 'Notify', 'trim|xss_clean|callback_notify_need_password');

			$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->form_validation->set_rules('privacy_agreed', 'Privacy Agreed', 'trim|required|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				if (set_value('privacy_agreed') != 1 && time() >= strtotime('2018-05-25')) {
					$errors[] = 'Privacy policy must be agreed to';
				}

				// all ok, prepare data
				$family_data = array(
					'byID' => $this->auth->user->staffID,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$child_data = array(
					'first_name' => set_value('child_first_name'),
					'last_name' => set_value('child_last_name'),
					'gender' => NULL,
					'dob' => uk_to_mysql_date(set_value('child_dob')),
					'orgID' => set_value('child_orgID'),
					'medical' => set_value('child_medical'),
					'behavioural_info' => set_value('child_behavioural_information'),
					'disability_info' => set_value('child_disability_info'),
					'ethnic_origin' => set_value('child_ethnic_origin'),
					'photoConsent' => 0,
					'emergency_contact_1_name' => null_if_empty(set_value('child_emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('child_emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('child_emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('child_emergency_contact_2_phone')),
					'byID' => $this->auth->user->staffID,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (in_array(set_value('child_gender'), array('male', 'female', 'other'))) {
					$child_data['gender'] = set_value('child_gender');
				}

				if (set_value('child_photoConsent') == 1) {
					$child_data['photoConsent'] = 1;
				}

				$contact_data = array(
					'main' => '1',
					'title' => NULL,
					'first_name' => set_value('first_name'),
					'last_name' => set_value('last_name'),
					'relationship' => set_value('relationship'),
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'address3' => set_value('address3'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'phone' => set_value('phone'),
					'mobile' => set_value('mobile'),
					'workPhone' => set_value('workPhone'),
					'email' => set_value('email'),
					'gender' => NULL,
					'dob' => NULL,
					'medical' => set_value('medical'),
					'behavioural_info' => set_value('behavioural_information'),
					'disability_info' => set_value('disability_info'),
					'ethnic_origin' => set_value('ethnic_origin'),
					'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
					'marketing_consent' => intval(set_value('marketing_consent')),
					'marketing_consent_date' => mdate('%Y-%m-%d %H:%i:%s'),
					'privacy_agreed' => intval(set_value('privacy_agreed')),
					'privacy_agreed_date' => mdate('%Y-%m-%d %H:%i:%s'),
					'byID' => $this->auth->user->staffID,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('title') != '') {
					$contact_data['title'] = set_value('title');
				}

				if (in_array(set_value('gender'), array('male', 'female', 'other'))) {
					$contact_data['gender'] = set_value('gender');
				}

				if (set_value('dob') != '') {
					$contact_data['dob'] = uk_to_mysql_date(set_value('dob'));
				}

				// insert school
				if ($add_school == 1) {
					$school_data = array(
						'byID' => $this->auth->user->staffID,
						'name' => set_value('new_school'),
						'prospect' => 1,
						'partnership' => 0,
						'type' => 'school',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					$this->db->insert('orgs', $school_data);

					$child_data['orgID'] = $this->db->insert_id();

					// insert empty address
					$school_address_data = array(
						'orgID' => $child_data['orgID'],
						'byID' => $this->auth->user->staffID,
						'type' => 'main',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID
					);

					$this->db->insert('orgs_addresses', $school_address_data);

				}


				// check if password entered
				if (set_value('password') != '') {
					// generate hash
					$password_hash = password_hash(set_value('password'), PASSWORD_BCRYPT);

					// check hash
					if (password_verify(set_value('password'), $password_hash)) {

						// save
						$contact_data['password'] = $password_hash;

					}
				}

				// final check for errors
				if (count($errors) == 0) {

					// insert
					$query = $this->db->insert('family', $family_data);

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						$familyID = $this->db->insert_id();

						// child
						$child_data['familyID'] = $familyID;
						$query = $this->db->insert('family_children', $child_data);
						$childID = $this->db->insert_id();

						// add child tags
						$tags = $this->input->post('child_tags');
						if (is_array($tags) && count($tags) > 0) {
							foreach ($tags as $tag) {
								$tag = trim(strtolower($tag));
								// check if tag in system already
								if (in_array($tag, $tag_list)) {
									$tagID = array_search($tag, $tag_list);
								} else {
									$data = array(
										'name' => $tag,
										'byID' => $this->auth->user->staffID,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									);
									$this->db->insert('settings_tags', $data);
									$tagID = $this->db->insert_id();
									$tag_list[$tagID] = $tag;
								}
								// add link to tag
								$data = array(
									'tagID' => $tagID,
									'childID' => $childID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);
								$this->db->insert('family_children_tags', $data);
							}
						}

						// contact
						$contact_data['familyID'] = $familyID;
						$query = $this->db->insert('family_contacts', $contact_data);
						$contactID = $this->db->insert_id();

						// geocode address
						if ($res_geocode = geocode_address($contact_data['address1'], $contact_data['town'], $contact_data['postcode'])) {
							$where = array(
								'contactID' => $contactID,
								'accountID' => $this->auth->user->accountID
							);
							$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
						}

						// add contact tags
						$tags = $this->input->post('tags');
						if (is_array($tags) && count($tags) > 0) {
							foreach ($tags as $tag) {
								$tag = trim(strtolower($tag));
								// check if tag in system already
								if (in_array($tag, $tag_list)) {
									$tagID = array_search($tag, $tag_list);
								} else {
									$data = array(
										'name' => $tag,
										'byID' => $this->auth->user->staffID,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									);
									$this->db->insert('settings_tags', $data);
									$tagID = $this->db->insert_id();
									$tag_list[$tagID] = $tag;
								}
								// add link to tag
								$data = array(
									'tagID' => $tagID,
									'contactID' => $contactID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);
								$this->db->insert('family_contacts_tags', $data);
							}
						}

						// insert note
						$details = 'Contact: ' . set_value('first_name') . ' ' . set_value('last_name') . '
						By: ' . $this->auth->user->first . ' ' . $this->auth->user->surname . ' (Staff)
						IP: ' . get_ip_address() . '
						Hostname: ' . gethostbyaddr(get_ip_address());
						$summary = 'Marketing Consent: ';
						if (set_value('marketing_consent') == 1) {
							$summary .= 'Yes';
						} else {
							$summary .= 'No';
						}
						$summary .= ', Privacy Agreed: ';
						if (set_value('privacy_agreed') == 1) {
							$summary .= 'Yes';
						} else {
							$summary .= 'No';
						}
						$data = array(
							'type' => 'privacy',
							'summary' => $summary,
							'content' => $details,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'familyID' => $familyID,
							'accountID' => $this->auth->user->accountID,
							'byID' => $this->auth->user->byID
						);
						$query = $this->db->insert('family_notes', $data);

						// update newsletter
						if ($brands->num_rows() > 0) {
							$newsletters = $this->input->post('newsletters');
							if (!is_array($newsletters)) {
								$newsletters = array();
							}
							foreach ($brands->result() as $brand) {
								if (in_array($brand->brandID, $newsletters)) {
									// insert
									$data = array(
										'brandID' => $brand->brandID,
										'contactID' => $contactID,
										'accountID' => $this->auth->user->accountID
									);
									$this->db->insert('family_contacts_newsletters', $data);
								}
							}
						}

						// tell user
						$success = 'Family has been created';
						if ($this->settings_library->get('send_new_participant') == 1 && set_value('notify') == 1 && $this->crm_library->send_participant_welcome_email($contactID, $this->input->post('password'))) {
							$success .= ' and contact notified';
						}
						$success .= ' successfully.';
						$this->session->set_flashdata('success', $success);

						redirect('participants/view/' . $familyID);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// if an error, keep tags in list that are not already stored
		if (count($errors) > 0 && isset($_POST)) {
			$child_tags = $this->input->post('child_tags');
			if (is_array($child_tags)) {
				$tag_list = array_merge($tag_list, $child_tags);
			}
			$tags = $this->input->post('tags');
			if (is_array($tags)) {
				$tag_list = array_merge($tag_list, $tags);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// schools
		$where = array(
			'type' => 'school',
			'accountID' => $this->auth->user->accountID
		);
		$schools = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'schools' => $schools,
			'add_school' => $add_school,
			'brands' => $brands,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'tag_list' => $tag_list
		);

		// load view
		$this->crm_view('participants/new', $data);
	}

	/**
	 * create new individual
	 * @return void
	 */
	public function new_account()
	{
		$account_holder_fields = get_fields('account_holder');
		$participant_fields = get_fields('participant');

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Account';
		$submit_to = 'participants/new-account';
		$return_to = 'participants';
		$icon = 'user';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>&nbsp;<a class="btn btn-success submit-new-account" href="javascript: void(0);"><i class="far fa-angle-left"></i> Save & Exit</a>';
		$add_school = 0;
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants'
		);

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// if an error, keep tags in list that are not already stored
		if (count($errors) > 0 && isset($_POST)) {
			$tags = $this->input->post('tags');
			if (is_array($tags)) {
				$tag_list = array_merge($tag_list, $tags);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// schools
		$where = array(
			'type' => 'school',
			'accountID' => $this->auth->user->accountID
		);
		$schools = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'brands' => $brands,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'tag_list' => $tag_list,
			'add_school' => $add_school,
			'schools' => $schools,
			'account_holder_fields' => $account_holder_fields,
			'participant_fields' => $participant_fields
		);

		// load view
		$this->crm_view('participants/new-account', $data);
	}

	/**
	 * Submit account holder form
	 * @return mixed
	 */
	public function account_holder_submit(){
		if (!$this->input->is_ajax_request()) {
			$errors[] = 'Unexpected error occurred. Please contact support team.';
			echo json_encode(['code' => 201, 'data' => $errors]);
			exit;
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// if posted - No need to validation it's been already done before
		if ($this->input->post() && $this->input->post("main_ah") === '1') {

			// all ok, prepare data
			$family_data = array(
				'byID' => $this->auth->user->staffID,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->auth->user->accountID
			);

			$contact_data = array(
				'main' => 1,
				'title' => NULL,
				'first_name' => trim($this->input->post('first_name')),
				'last_name' => trim($this->input->post('last_name')),
				'relationship' => 'individual',
				'address1' => trim($this->input->post('address1')),
				'address2' => trim($this->input->post('address2')),
				'address3' => trim($this->input->post('address3')),
				'town' => trim($this->input->post('town')),
				'county' => trim($this->input->post('county')),
				'postcode' => trim($this->input->post('postcode')),
				'phone' => trim($this->input->post('phone')),
				'mobile' => trim($this->input->post('mobile')),
				'workPhone' => trim($this->input->post('workPhone')),
				'email' => trim($this->input->post('email')),
				'gender' => NULL,
				'gender_specify' => NULL,
				'dob' => NULL,
				'medical' => trim($this->input->post('medical')),
				'behavioural_info' => trim($this->input->post('behavioural_information')),
				'disability_info' => trim($this->input->post('disability_info')),
				'ethnic_origin' => trim($this->input->post('ethnic_origin')),
				'religion' => NULL,
				'religion_specify' => NULL,
				'emergency_contact_1_name' => null_if_empty(trim($this->input->post('emergency_contact_1_name'))),
				'emergency_contact_1_phone' => null_if_empty(trim($this->input->post('emergency_contact_1_phone'))),
				'emergency_contact_2_name' => null_if_empty(trim($this->input->post('emergency_contact_2_name'))),
				'emergency_contact_2_phone' => null_if_empty(trim($this->input->post('emergency_contact_2_phone'))),
				'marketing_consent' => intval($this->input->post('marketing_consent')),
				'marketing_consent_date' => mdate('%Y-%m-%d %H:%i:%s'),
				'privacy_agreed' => intval($this->input->post('privacy_agreed')),
				'privacy_agreed_date' => mdate('%Y-%m-%d %H:%i:%s'),
				'byID' => $this->auth->user->staffID,
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->auth->user->accountID
			);

			if ($this->input->post('title') != '') {
				$contact_data['title'] = trim($this->input->post('title'));
			}

			if (in_array($this->input->post('gender'), array('male', 'female', 'please_specify', 'other'))) {
				$contact_data['gender'] = $this->input->post('gender');
				if (set_value('gender')=="please_specify") {
					$contact_data['gender_specify'] = set_value('gender_specify');
				}
			}

			if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
				$contact_data['religion'] = set_value('religion');
				if (set_value('religion')=="please_specify") {
					$contact_data['religion_specify'] = set_value('religion_specify');
				}
			}

			if ($this->input->post('dob') != '') {
				$contact_data['dob'] = uk_to_mysql_date($this->input->post('dob'));
			}

			// check if password entered
			if ($this->input->post('password') != '') {
				// generate hash
				$password_hash = password_hash($this->input->post('password'), PASSWORD_BCRYPT);

				// check hash
				if (password_verify($this->input->post('password'), $password_hash)) {
					// save
					$contact_data['password'] = $password_hash;
				}
			}

			// update profile picture
			$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->auth->user->accountID, 500, 500, 50, 50, TRUE);

			if ($upload_res !== NULL) {
				$image_data = array(
					'name' => $upload_res['client_name'],
					'path' => $upload_res['raw_name'],
					'type' => $upload_res['file_type'],
					'size' => $upload_res['file_size']*1024,
					'ext' => substr($upload_res['file_ext'], 1)
				);
				$contact_data['profile_pic'] = serialize($image_data);
			}

			// insert
			$query = $this->db->insert('family', $family_data);

			// if inserted
			if ($this->db->affected_rows() == 1) {

				$familyID = $this->db->insert_id();

				// contact
				$contact_data['familyID'] = $familyID;
				$query = $this->db->insert('family_contacts', $contact_data);
				$contactID = $this->db->insert_id();

				//Add disability data
				if (is_array($this->input->post('disability'))) {
					$disability_data = array(
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $this->auth->user->accountID,
						'contactID' => $contactID
					);
					foreach ($this->input->post('disability') as $disability => $v) {
						$disability_data[$disability] = ($v=="1" ? 1 : NULL);
					}

					$this->db->insert('family_disabilities', $disability_data);
				}

				// geocode address
				if ($res_geocode = geocode_address($contact_data['address1'], $contact_data['town'], $contact_data['postcode'])) {
					$where = array(
						'contactID' => $contactID,
						'accountID' => $this->auth->user->accountID
					);
					$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
				}

				// add contact tags
				$tags = $this->input->post('tags');
				if (is_array($tags) && count($tags) > 0) {
					foreach ($tags as $tag) {
						$tag = trim(strtolower($tag));
						// check if tag in system already
						if (in_array($tag, $tag_list)) {
							$tagID = array_search($tag, $tag_list);
						} else {
							$data = array(
								'name' => $tag,
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('settings_tags', $data);
							$tagID = $this->db->insert_id();
							$tag_list[$tagID] = $tag;
						}
						// add link to tag
						$data = array(
							'tagID' => $tagID,
							'contactID' => $contactID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('family_contacts_tags', $data);
					}
				}

				// insert note
				$details = 'Contact: ' . $this->input->post('first_name') . ' ' . $this->input->post('last_name') . '
				By: ' . $this->auth->user->first . ' ' . $this->auth->user->surname . ' (Staff)
				IP: ' . get_ip_address() . '
				Hostname: ' . gethostbyaddr(get_ip_address());
				$summary = 'Marketing Consent: ';
				if ($this->input->post('marketing_consent') == 1) {
					$summary .= 'Yes';
				} else {
					$summary .= 'No';
				}
				$summary .= ', Privacy Agreed: ';
				if ($this->input->post('privacy_agreed') == 1) {
					$summary .= 'Yes';
				} else {
					$summary .= 'No';
				}
				$data = array(
					'type' => 'privacy',
					'summary' => $summary,
					'content' => $details,
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'familyID' => $familyID,
					'accountID' => $this->auth->user->accountID,
					'byID' => $this->auth->user->byID
				);
				$query = $this->db->insert('family_notes', $data);

				// update newsletter
				if ($brands->num_rows() > 0) {
					$newsletters = $this->input->post('newsletters');
					if (!is_array($newsletters)) {
						$newsletters = array();
					}
					foreach ($brands->result() as $brand) {
						if (in_array($brand->brandID, $newsletters)) {
							// insert
							$data = array(
								'brandID' => $brand->brandID,
								'contactID' => $contactID,
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('family_contacts_newsletters', $data);
						}
					}
				}

				// tell user
				$success = $contact_data['first_name'] . ' ' . $contact_data['last_name'] . ' has been created';
				if ($this->settings_library->get('send_new_participant') == 1 && $this->input->post('notify') == 1 && $this->crm_library->send_participant_welcome_email($contactID, $this->input->post('password'))) {
					$success .= ' and contact notified';
				}
				$success .= ' successfully.';
				$this->session->set_flashdata('success', $success);
				echo json_encode(['code' => 1, 'data' => $success, 'familyId' => $familyID]);
			}else {
				//Info Error
				$errors[] = 'Error saving data, please try again.';
				echo json_encode(['code' => 202, 'data' => $errors]);
			}
		}
		elseif($this->input->post() && $this->input->post("family_id") != '0' && $this->input->post("main_ah") === '0'){

			$familyID = $this->input->post("family_id");
			// look up family
			$where = array(
				'familyID' => $familyID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('family')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				$errors[] = 'Unexpected error occurred. Please contact support team.';
				echo json_encode(['code' => 101, 'data' => $errors]);
				exit;
			}

			// match
			foreach ($query->result() as $row) {
				$family_info = $row;
			}

			// all ok, prepare data
			$data = array(
				'title' => NULL,
				'first_name' => trim($this->input->post('first_name')),
				'last_name' => trim($this->input->post('last_name')),
				'relationship' => trim($this->input->post('relationship')),
				'address1' => trim($this->input->post('address1')),
				'address2' => trim($this->input->post('address2')),
				'address3' => trim($this->input->post('address3')),
				'town' => trim($this->input->post('town')),
				'county' => trim($this->input->post('county')),
				'postcode' => trim($this->input->post('postcode')),
				'phone' => trim($this->input->post('phone')),
				'mobile' => trim($this->input->post('mobile')),
				'workPhone' => trim($this->input->post('workPhone')),
				'email' => trim($this->input->post('email')),
				'gender' => NULL,
				'dob' => NULL,
				'medical' => trim($this->input->post('medical')),
				'behavioural_info' => trim($this->input->post('behavioural_information')),
				'disability_info' => trim($this->input->post('disability_info')),
				'ethnic_origin' => trim($this->input->post('ethnic_origin')),
				'emergency_contact_1_name' => null_if_empty(trim($this->input->post('emergency_contact_1_name'))),
				'emergency_contact_1_phone' => null_if_empty(trim($this->input->post('emergency_contact_1_phone'))),
				'emergency_contact_2_name' => null_if_empty(trim($this->input->post('emergency_contact_2_name'))),
				'emergency_contact_2_phone' => null_if_empty(trim($this->input->post('emergency_contact_2_phone'))),
				'blacklisted' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->auth->user->accountID
			);

			if ($this->input->post('title') != '') {
				$data['title'] = trim($this->input->post('title'));
			}

			if (in_array($this->input->post('gender'), array('male', 'female', 'other'))) {
				$data['gender'] = $this->input->post('gender');
			}

			if ($this->input->post('dob') != '') {
				$data['dob'] = uk_to_mysql_date($this->input->post('dob'));
			}

			$data['byID'] = $this->auth->user->staffID;
			$data['familyID'] = $familyID;
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['marketing_consent'] = intval($this->input->post('marketing_consent'));
			$data['marketing_consent_date'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['privacy_agreed'] = intval($this->input->post('privacy_agreed'));
			$data['privacy_agreed_date'] = mdate('%Y-%m-%d %H:%i:%s');


			// check if password entered
			if ($this->input->post('password') != '') {
				// generate hash
				$password_hash = password_hash($this->input->post('password'), PASSWORD_BCRYPT);

				// check hash
				if (password_verify($this->input->post('password'), $password_hash)) {

					// save
					$data['password'] = $password_hash;

				}
			}

			if ($this->input->post('blacklisted') == '1') {
				$data['blacklisted'] = 1;
			}

			// update profile picture
			$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->auth->user->accountID, 500, 500, 50, 50, TRUE);

			if ($upload_res !== NULL) {
				$image_data = array(
					'name' => $upload_res['client_name'],
					'path' => $upload_res['raw_name'],
					'type' => $upload_res['file_type'],
					'size' => $upload_res['file_size']*1024,
					'ext' => substr($upload_res['file_ext'], 1)
				);
				$data['profile_pic'] = serialize($image_data);
			}

			// insert
			$query = $this->db->insert('family_contacts', $data);
			$contactID = $this->db->insert_id();
			$just_added = TRUE;

			// if inserted/updated
			if ($this->db->affected_rows() == 1) {

				// geocode address
				if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
					$where = array(
						'contactID' => $contactID,
						'accountID' => $this->auth->user->accountID
					);
					$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
				}

				// add/update tags
				$tags = $this->input->post('tags');
				if (!is_array($tags)) {
					$tags = array();
				}
				// remove existing
				$where = array(
					'contactID' => $contactID,
					'accountID' => $this->auth->user->accountID
				);
				$this->db->delete('family_contacts_tags', $where);
				if (count($tags) > 0) {
					foreach ($tags as $tag) {
						$tag = trim(strtolower($tag));
						// check if tag in system already
						if (in_array($tag, $tag_list)) {
							$tagID = array_search($tag, $tag_list);
						} else {
							$data = array(
								'name' => $tag,
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('settings_tags', $data);
							$tagID = $this->db->insert_id();
							$tag_list[$tagID] = $tag;
						}
						// add link to tag
						$data = array(
							'tagID' => $tagID,
							'contactID' => $contactID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('family_contacts_tags', $data);
					}
				}

				// if just added
				if (isset($just_added)) {
					// insert note
					$details = 'Contact: ' . $this->input->post('first_name') . ' ' . $this->input->post('last_name') . '
						By: ' . $this->auth->user->first . ' ' . $this->auth->user->surname . ' (Staff)
						IP: ' . get_ip_address() . '
						Hostname: ' . gethostbyaddr(get_ip_address());
					$summary = 'Marketing Consent: ';
					if ($this->input->post('marketing_consent') == 1) {
						$summary .= 'Yes';
					} else {
						$summary .= 'No';
					}
					$summary .= ', Privacy Agreed: ';
					if ($this->input->post('privacy_agreed') == 1) {
						$summary .= 'Yes';
					} else {
						$summary .= 'No';
					}
					$data = array(
						'type' => 'privacy',
						'summary' => $summary,
						'content' => $details,
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'familyID' => $familyID,
						'accountID' => $this->auth->user->accountID,
						'byID' => $this->auth->user->byID
					);
					$query = $this->db->insert('family_notes', $data);

					// update newsletter
					if ($brands->num_rows() > 0) {
						$newsletters = $this->input->post('newsletters');
						if (!is_array($newsletters)) {
							$newsletters = array();
						}
						foreach ($brands->result() as $brand) {
							// set where
							$where = array(
								'brandID' => $brand->brandID,
								'contactID' => $contactID,
								'accountID' => $this->auth->user->accountID
							);

							// process
							if (in_array($brand->brandID, $newsletters)) {
								// check if exists
								$res = $this->db->from('family_contacts_newsletters')->where($where)->limit(1)->get();

								// if not, insert
								if ($res->num_rows() == 0) {
									$data = $where;
									$this->db->insert('family_contacts_newsletters', $data);
								}
							} else {
								// remove
								$this->db->delete('family_contacts_newsletters', $where, 1);
							}
						}
					}
				}

				// tell user
				$success = $this->input->post('first_name') . ' ' . $this->input->post('last_name') . ' has been ';
				if (isset($just_added)) {
					$success .= 'created';
				} else {
					$success .= 'updated';
				}

				if ($this->settings_library->get('send_new_participant') == 1 && $this->input->post('notify') == 1 && $this->crm_library->send_participant_welcome_email($contactID, $this->input->post('password'))) {
					$success .= ' and contact notified';
				}
				$success .= ' successfully.';
				echo json_encode(['code' => 1, 'data' => $success]);
			} else {
				$errors[] = 'Error saving data, please try again.';
				echo json_encode(['code' => 102, 'data' => $errors]);
				exit;
			}

		}else{
			$errors[] = 'Unexpected error occurred. Please contact support team.';
			echo json_encode(['code' => 103, 'data' => $errors]);
			exit;
		}
	}

	/**
	 * validate account holder form fields
	 * @return mixed
	 */
	public function account_holder_validator(){
		if (!$this->input->is_ajax_request()) {
			$errors[] = 'Unexpected error occurred. Please contact support team.';
			echo json_encode(['code' => 2, 'data' => $errors]);
			exit;
		}
		$fields = get_fields('account_holder');

		// load libraries
		$this->load->library('form_validation');

		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('title', field_label('title', $fields, TRUE), 'trim|xss_clean' . required_field('title', $fields, 'validation'));
			$this->form_validation->set_rules('first_name', field_label('first_name', $fields, TRUE), 'trim|xss_clean' . required_field('first_name', $fields, 'validation'));
			$this->form_validation->set_rules('last_name', field_label('last_name', $fields, TRUE), 'trim|xss_clean' . required_field('last_name', $fields, 'validation'));
			$this->form_validation->set_rules('gender', field_label('gender', $fields, TRUE), 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
			$this->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . ($this->input->post('gender')=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('gender_since_birth', 'Gender Since Birth', 'trim|xss_clean' . required_field('gender_since_birth', $fields, 'validation'));
			$this->form_validation->set_rules('sexual_orientation', 'Sexual Orientation', 'trim|xss_clean'. required_field('sexual_orientation', $fields, 'validation'));
			$this->form_validation->set_rules('sexual_orientation_specify', 'Specific Sexual Orientation', 'trim|xss_clean' . ($this->input->post('sexual_orientation')=="please_specify" ? required_field('sexual_orientation_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('dob', field_label('dob', $fields, TRUE), 'trim|xss_clean' . required_field('dob', $fields, 'validation') . '|callback_check_dob');
			$this->form_validation->set_rules('medical', field_label('medical', $fields, TRUE), 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$disabilityFailed = false;
			if (empty($this->input->post('disability')) && required_field('disability', $fields)) {
				$disabilityFailed = true;
			}
			$this->form_validation->set_rules('behavioural_information', field_label('behavioural_information', $fields, TRUE), 'trim|xss_clean' . required_field('behavioural_information', $fields, 'validation'));
			$this->form_validation->set_rules('disability_info', field_label('disability_info', $fields, TRUE), 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
			$this->form_validation->set_rules('ethnic_origin', field_label('ethnic_origin', $fields, TRUE), 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
			$this->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
			$this->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . ($this->input->post('religion')=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('eRelationship', field_label('eRelationship', $fields, TRUE), 'trim|xss_clean' . required_field('eRelationship', $fields, 'validation'));
			$this->form_validation->set_rules('address1', field_label('address1', $fields, TRUE), 'trim|xss_clean' . required_field('address1', $fields, 'validation'));
			$this->form_validation->set_rules('address2', field_label('address2', $fields, TRUE), 'trim|xss_clean' . required_field('address2', $fields, 'validation'));
			$this->form_validation->set_rules('address3', field_label('address3', $fields, TRUE), 'trim|xss_clean' . required_field('address3', $fields, 'validation'));
			$this->form_validation->set_rules('town', field_label('town', $fields, TRUE), 'trim|xss_clean' . required_field('town', $fields, 'validation'));
			$this->form_validation->set_rules('county', field_label('county', $fields, TRUE), 'trim|xss_clean' . required_field('county', $fields, 'validation'));
			$this->form_validation->set_rules('postcode', field_label('postcode', $fields, TRUE), 'trim|xss_clean' . required_field('postcode', $fields, 'validation') . '|callback_check_postcode');
			$this->form_validation->set_rules('mobile', field_label('mobile', $fields, TRUE), 'trim|xss_clean' . required_field('mobile', $fields, 'validation') . '|callback_check_mobile');
			$this->form_validation->set_rules('phone', field_label('phone', $fields, TRUE), 'trim|xss_clean' . required_field('phone', $fields, 'validation'));
			$this->form_validation->set_rules('workPhone', field_label('workPhone', $fields, TRUE), 'trim|xss_clean' . required_field('workPhone', $fields, 'validation'));
			$this->form_validation->set_rules('email', field_label('email', $fields, TRUE), 'trim|xss_clean' . required_field('email', $fields, 'validation') . '|valid_email|callback_check_email[]');
			$this->form_validation->set_rules('emergency_contact_1_name', field_label('emergency_contact_1_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_phone', field_label('emergency_contact_1_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_phone', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_name', field_label('emergency_contact_2_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_phone', field_label('emergency_contact_2_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_phone', $fields, 'validation'));

			$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');

			$this->form_validation->set_rules('notify', field_label('notify', $fields, TRUE), 'trim|xss_clean' . required_field('notify', $fields, 'validation').'|callback_notify_need_password');
			$this->form_validation->set_rules('blacklisted', field_label('blacklisted', $fields, TRUE), 'trim|xss_clean' . required_field('blacklisted', $fields, 'validation'));

			$this->form_validation->set_rules('tags', field_label('tags', $fields, TRUE), 'trim|xss_clean' . required_field('tags', $fields, 'validation'));
			$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
			$this->form_validation->set_rules('privacy_agreed', 'Privacy Agreed', 'trim|required|xss_clean');


			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
				if ($this->input->post('privacy_agreedprivacy_agreed') != 1 && time() >= strtotime('2018-05-25')) {
					$errors[] = 'Privacy policy must be agreed to';
				}
				echo json_encode(['code' => 0, 'data' => $errors]);
			}else{
				echo json_encode(['code' => 1, 'data'=> '']);
			}
		}else{
			$errors[] = 'Unexpect request submitted. Please contact support team.';
			echo json_encode(['code' => 2, 'data' => $errors]);
		}
	}

	/**
	 * validate participant form fields
	 * @return mixed
	 */
	public function participant_validator(){
		if (!$this->input->is_ajax_request()) {
			$errors[] = 'Unexpected error occurred. Please contact support team.';
			echo json_encode(['code' => 2, 'data' => $errors]);
			exit;
		}

		$fields = get_fields('participant');

		// load libraries
		$this->load->library('form_validation');

		// if posted
		if ($this->input->post()) {
			$add_school = 0;
			if ($this->input->post('add_school') == 1) {
				$add_school = 1;
			}
			// set validation rules
			$this->form_validation->set_rules('first_name', field_label('first_name', $fields, TRUE), 'trim|xss_clean' . required_field('first_name', $fields, 'validation'));
			$this->form_validation->set_rules('last_name', field_label('last_name', $fields, TRUE), 'trim|xss_clean' . required_field('last_name', $fields, 'validation'));
			$this->form_validation->set_rules('dob', field_label('dob', $fields, TRUE), 'trim|xss_clean' . required_field('dob', $fields, 'validation') . '|callback_check_dob');
			$this->form_validation->set_rules('gender', field_label('gender', $fields, TRUE), 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
			$this->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . ($this->input->post('gender')=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));

			if ($add_school != 1) {
				$this->form_validation->set_rules('orgID', field_label('orgID', $fields, TRUE), 'trim|xss_clean' . required_field('orgID', $fields, 'validation'));
			} else {
				$this->form_validation->set_rules('new_school', 'School', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('medical', field_label('medical', $fields, TRUE), 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$disabilityFailed = false;
			if (empty($this->input->post('disability')) && required_field('disability', $fields)) {
				$disabilityFailed = true;
			}
			$this->form_validation->set_rules('behavioural_information', field_label('behavioural_information', $fields, TRUE), 'trim|xss_clean' . required_field('behavioural_information', $fields, 'validation'));
			$this->form_validation->set_rules('disability_info', field_label('disability_info', $fields, TRUE), 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
			$this->form_validation->set_rules('ethnic_origin', field_label('ethnic_origin', $fields, TRUE), 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
			$this->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
			$this->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . ($this->input->post('religion_specify')=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('photoConsent', field_label('photoConsent', $fields, TRUE), 'trim|xss_clean' . required_field('photoConsent', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_name', field_label('emergency_contact_1_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_phone', field_label('emergency_contact_1_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_phone', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_name', field_label('emergency_contact_2_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_phone', field_label('emergency_contact_2_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_phone', $fields, 'validation'));
			$this->form_validation->set_rules('profile_pic', field_label('profile_pic', $fields, TRUE), 'trim|xss_clean' . required_field('profile_pic', $fields, 'validation'));
			$this->form_validation->set_rules('tags', field_label('tags', $fields, TRUE), 'trim|xss_clean' . required_field('tags', $fields, 'validation'));
			$this->form_validation->set_rules('pin', field_label('pin', $fields, TRUE), 'trim|xss_clean' . required_field('pin', $fields, 'validation'));


			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
				echo json_encode(['code' => 0, 'data' => $errors]);
			}else {
				echo json_encode(['code' => 1, 'data' => '']);
			}
			} else{
			echo json_encode(['code' => 2, 'data' => 'Unexpect request submitted. Please contact support team.']);
		}
	}

	/**
	 * Submit Participant form
	 * @return mixed
	 */
	public function participant_submit(){
		//Check the request is AJAX
		if (!$this->input->is_ajax_request()) {
			$errors[] = 'Unexpected error occurred. Please contact support team.';
			echo json_encode(['code' => 2, 'data' => $errors]);
			exit;
		}

		$add_school = $this->input->post('add_school');

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// if inserted
		if ($this->input->post() && $this->input->post('family_id')) {
			$familyID = $this->input->post('family_id');

			// look up family
			$where = array(
				'familyID' => $familyID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('family')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				$errors[] = 'Unexpected error occurred. Please contact support team.';
				echo json_encode(['code' => 2, 'data' => $errors]);
				exit;
			}

			// match
			foreach ($query->result() as $row) {
				$family_info = $row;
			}

			// all ok, prepare data
			$data = array(
				'first_name' => trim($this->input->post('first_name')),
				'last_name' => trim($this->input->post('last_name')),
				'gender' => NULL,
				'gender_specify' => NULL,
				'dob' => uk_to_mysql_date($this->input->post('dob')),
				'orgID' => trim($this->input->post('orgID')),
				'medical' => trim($this->input->post('medical')),
				'behavioural_info' => trim($this->input->post('behavioural_information')),
				'disability_info' => trim($this->input->post('disability_info')),
				'ethnic_origin' => trim($this->input->post('ethnic_origin')),
				'photoConsent' => 0,
				'emergency_contact_1_name' => null_if_empty(trim($this->input->post('emergency_contact_1_name'))),
				'emergency_contact_1_phone' => null_if_empty(trim($this->input->post('emergency_contact_1_phone'))),
				'emergency_contact_2_name' => null_if_empty(trim($this->input->post('emergency_contact_2_name'))),
				'emergency_contact_2_phone' => null_if_empty(trim($this->input->post('emergency_contact_2_phone'))),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->auth->user->accountID
			);

			if (in_array($this->input->post('gender'), array('male', 'female', 'please_specify', 'other'))) {
				$data['gender'] = $this->input->post('gender');

				if (set_value('gender')=="please_specify") {
					$data['gender_specify'] = set_value('gender_specify');
				}
			}

			if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
				$data['religion'] = set_value('religion');
				if (set_value('religion')=="please_specify") {
					$data['religion_specify'] = set_value('religion_specify');
				}
			}

			if ($this->input->post('photoConsent') == 1) {
				$data['photoConsent'] = 1;
			}

			$data['byID'] = $this->auth->user->staffID;
			$data['familyID'] = $familyID;
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');

			// insert school
			if ($add_school == 1) {
				$school_data = array(
					'byID' => $this->auth->user->staffID,
					'name' => $this->input->post('new_school'),
					'prospect' => 1,
					'partnership' => 0,
					'type' => 'school',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$this->db->insert('orgs', $school_data);

				$data['orgID'] = $this->db->insert_id();

				// insert empty address
				$school_address_data = array(
					'orgID' => $data['orgID'],
					'byID' => $this->auth->user->staffID,
					'type' => 'main',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$this->db->insert('orgs_addresses', $school_address_data);

			}

			// update profile picture
			$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->auth->user->accountID, 500, 500, 50, 50, TRUE);

			if ($upload_res !== NULL) {
				$image_data = array(
					'name' => $upload_res['client_name'],
					'path' => $upload_res['raw_name'],
					'type' => $upload_res['file_type'],
					'size' => $upload_res['file_size']*1024,
					'ext' => substr($upload_res['file_ext'], 1)
				);
				$data['profile_pic'] = serialize($image_data);
			}

			// insert
			$query = $this->db->insert('family_children', $data);
			$childID = $this->db->insert_id();

			//Add disability data
			if (is_array($this->input->post('disability'))) {
				$disability_data = array(
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID,
					'childID' => $childID
				);
				foreach ($this->input->post('disability') as $disability => $v) {
					$disability_data[$disability] = ($v=="1" ? 1 : NULL);
				}

				$this->db->insert('family_disabilities', $disability_data);
			}

			// if inserted/updated
			if ($this->db->affected_rows() == 1) {

				// add/update tags
				$tags = $this->input->post('tags');
				if (!is_array($tags)) {
					$tags = array();
				}
				// remove existing
				$where = array(
					'childID' => $childID,
					'accountID' => $this->auth->user->accountID
				);
				$this->db->delete('family_children_tags', $where);
				if (count($tags) > 0) {
					foreach ($tags as $tag) {
						$tag = trim(strtolower($tag));
						// check if tag in system already
						if (in_array($tag, $tag_list)) {
							$tagID = array_search($tag, $tag_list);
						} else {
							$data = array(
								'name' => $tag,
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('settings_tags', $data);
							$tagID = $this->db->insert_id();
							$tag_list[$tagID] = $tag;
						}
						// add link to tag
						$data = array(
							'tagID' => $tagID,
							'childID' => $childID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('family_children_tags', $data);
					}
				}
				echo json_encode(['code' => 1, 'data' => $this->input->post('first_name') . ' ' . $this->input->post('last_name') . ' has been created successfully.']);
			} else {
				//Info
				$errors[] = 'Error saving data, please try again.';
				echo json_encode(['code' => 2, 'data' => $errors]);
				exit;
			}
		}
	}

	/**
	 * photo consent
	 * @param  int $childID
	 * @param string $value
	 * @return mixed
	 */
	public function photoconsent($childID = NULL, $value = NULL) {

		// check params
		if (empty($childID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'childID' => $childID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_children')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {

			$data = array(
				'photoConsent' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['photoConsent'] = 1;
			}

			// run query
			$query = $this->db->update('family_children', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * make a contact, the main contact
	 * @param  int $contactID
	 * @return mixed
	 */
	public function maincontact($contactID = NULL) {

		// check params
		if (empty($contactID)) {
			show_404();
		}

		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$contact_info = $row;

			// remove main from all contacts in family
			$data = array(
				'main' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$where = array(
				'familyID' => $contact_info->familyID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->update('family_contacts', $data, $where);

			// add back to spcicif contact
			$data['main'] = 1;
			$where['contactID'] = $contactID;

			// run query
			$query = $this->db->update('family_contacts', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {

				$this->session->set_flashdata('success', $contact_info->first_name . ' ' . $contact_info->last_name . ' is now the main contact.');

				redirect('participants/view/' . $contact_info->familyID);
			}
		}

	}

	/**
	 * check if a contact already exists
	 * @return mixed
	 */
	public function contactcheck() {

		$first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');

		// check params
		if (empty($first_name) || empty($last_name)) {
			show_404();
		}

		$where = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_contacts')->where($where)->order_by('postcode asc')->get();

		// no match
		if ($query->num_rows() == 0) {
			echo "OK";
			return TRUE;
		}

		// match
		$return_data = '<p>The following have been identified as possible matches for this contact, please review them before adding a new one:</p><ul>';

		foreach ($query->result() as $row) {
			$return_data .= '<li>' . anchor('participants/view/' . $row->familyID, trim(ucwords($row->title) . ' ' . $row->first_name . ' ' . $row->last_name) . ' (' . $row->postcode . ')') . '</li>';
		}

		$return_data .= '</ul>';

		echo $return_data;

	}

	/**
	 * check if a child already exists
	 * @return mixed
	 */
	public function childcheck() {

		$first_name = $this->input->post('first_name');
		$last_name = $this->input->post('last_name');

		// check params
		if (empty($first_name) || empty($last_name)) {
			show_404();
		}

		$where = array(
			'first_name' => $first_name,
			'last_name' => $last_name,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_children')->where($where)->order_by('dob asc')->get();

		// no match
		if ($query->num_rows() == 0) {
			echo "OK";
			return TRUE;
		}

		// match
		$return_data = '<p>The following have been identified as possible matches for this child, please review them before adding a new one:</p><ul>';

		foreach ($query->result() as $row) {
			$return_data .= '<li>' . anchor('participants/view/' . $row->familyID, trim($row->first_name . ' ' . $row->last_name) . ' (' . mysql_to_uk_date($row->dob) . ')') . '</li>';
		}

		$return_data .= '</ul>';

		echo $return_data;

	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		return $this->crm_library->check_postcode($postcode);

	}

	/**
	 * check if either this or another field is filled in
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function phone_or_mobile($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// if both empty, not valid
		if (empty($value) && empty($value2)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for checking email is unique
	 * @param  string $email
	 * @param  int $user_id
	 * @return bool
	 */
	public function check_email($email = NULL) {
		// if not email specified, skip
		if (empty($email)) {
			return TRUE;
		}

		// check email not in use with anyone on this account
		$where = array(
			'email' => $email,
			'accountID' => $this->auth->user->accountID
		);

		// check
		$query = $this->db->get_where('family_contacts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * check mobile number is valid
	 * @param  string $number
	 * @return mixed
	 */
	public function check_mobile($number = NULL) {
		return $this->crm_library->check_mobile($number);
	}

	/**
	 * validation function for check a dob is valid and in past
	 * @param  string $date
	 * @return bool
	 */
	public function check_dob($date) {

		// valid if empty
		if (empty($date)) {
			return TRUE;
		}

		// check valid date
		if (!check_uk_date($date)) {
			return FALSE;
		}

		// check date is in future
		if (strtotime(uk_to_mysql_date($date)) > time()) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for notify as need password
	 * @param  string $val
	 * @return bool
	 */
	public function notify_need_password($val) {

		// valid if empty
		if (empty($val)) {
			return TRUE;
		}

		// check has email and password
		if (empty($this->input->post('email')) || empty($this->input->post('password'))) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file main.php */
/* Location: ./application/controllers/participants/main.php */
