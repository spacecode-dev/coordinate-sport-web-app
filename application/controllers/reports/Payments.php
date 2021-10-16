<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Payments extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports', 'participant_billing'));
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'payments';
		$section = 'reports';
		$tab = 'billing';
		$page_base = 'reports/payments';
		$title = 'Booking Payments & Transactions';
		$buttons = ' <a class="btn btn-primary export-search-submit" href="' . site_url('reports/payments/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;
		$group_by = 'family_payments.paymentID';

		// check if exporting
		if ($action == 'export') {
			$export = TRUE;
		}

		// set up search
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'amount' => NULL,
			'transaction_ref' => NULL,
			'payment_method' => NULL,
			'participant_name' => NULL,
			'project_name' => NULL,
			'project_code' => NULL,
			'block' => NULL,
			'session_type' => NULL,
			'note' => NULL,
			'brand' => NULL,
			'by_project' => NULL,
			'search' => NULL
		);
		$is_search = FALSE;
		$search_where = array();

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_amount', 'Amount', 'trim|xss_clean');
			$this->form_validation->set_rules('search_transaction_ref', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search_payment_method', 'Payment Method', 'trim|xss_clean');
			$this->form_validation->set_rules('search_participant_name', 'Participant Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_name', 'Project Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_code', 'Project Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search_block', 'Block', 'trim|xss_clean');
			$this->form_validation->set_rules('search_session_type', 'Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Note', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand', $this->settings_library->get_label('brand'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_by_project', 'Group By Project', 'trim|xss_clean');

			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			foreach ($search_fields as $key => $val) {
				$search_fields[$key] = set_value('search_' . $key);
			}

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-reports-payments-billing'))) {

			foreach ($this->session->userdata('search-reports-payments-billing') as $key => $value) {
				$search_fields[$key] = $value;
			}

		}

		// if dates empty, add default
		$offset = '-1 month';
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// if from after to, reset
		if (strtotime(uk_to_mysql_date($search_fields['date_from'])) > strtotime(uk_to_mysql_date($search_fields['date_to']))) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset, strtotime(uk_to_mysql_date($search_fields['date_to']))));
		}

		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata('search-reports-payments-billing', $search_fields);

		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			if ($date_from !== FALSE) {
				$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`added` >= " . $this->db->escape($date_from . ' 00:00:00');
			}
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			if ($date_to !== FALSE) {
				$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`added` <= " . $this->db->escape($date_to . ' 23:59:59');
			}
		}

		if ($search_fields['amount'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`amount` = " . $this->db->escape($search_fields['amount']);
		}

		if ($search_fields['transaction_ref'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`transaction_ref` = " . $this->db->escape($search_fields['transaction_ref']);
		}

		if ($search_fields['payment_method'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`method` = " . $this->db->escape($search_fields['payment_method']);
		}

		if ($search_fields['note'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";
		}

		if ($search_fields['participant_name'] != '') {
			if (substr($search_fields['participant_name'], 0, 6) == 'child_') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`childID` = " . $this->db->escape(substr($search_fields['participant_name'], 6));
			} else if (substr($search_fields['participant_name'], 0, 8) == 'contact_') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`contactID` = " . $this->db->escape(substr($search_fields['participant_name'], 8));
			}
		}

		if ($search_fields['project_name'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`bookingID` = " . $this->db->escape($search_fields['project_name']);
		}

		if ($search_fields['project_code'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`project_codeID` = " . $this->db->escape($search_fields['project_code']);
		}

		if ($search_fields['block'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`blockID` = " . $this->db->escape($search_fields['block']);
		}

		if ($search_fields['session_type'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_lessons") . "`.`typeID` = " . $this->db->escape($search_fields['session_type']);
		}

		if ($search_fields['brand'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brand']);
		}

		if ($search_fields['by_project'] == 1) {
			$group_by = 'bookings_cart_sessions.bookingID, family_payments.paymentID';
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// get report data
		$where = [
			'family_payments.accountID' => $this->auth->user->accountID
		];
		$res = $this->db->select('family_payments.*,
			SUM(' .  $this->db->dbprefix("family_payments_sessions") . '.amount) AS amount_partial, family_payments_sessions.is_sub,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("family_children") . '.first_name, " ", ' .  $this->db->dbprefix("family_children") . '.last_name) SEPARATOR "###") as children_names,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("family_contacts") . '.first_name, " ", ' .  $this->db->dbprefix("family_contacts") . '.last_name) SEPARATOR "###") as contact_names,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("bookings") . '.name ORDER BY ' . $this->db->dbprefix("bookings") . '.name ASC SEPARATOR ",") as project_names,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("bookings_blocks") . '.name ORDER BY ' . $this->db->dbprefix("bookings_blocks") . '.name ASC SEPARATOR ",") as blocks,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("lesson_types") . '.name ORDER BY ' . $this->db->dbprefix("lesson_types") . '.name ASC SEPARATOR ",") as session_types,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("brands") . '.name ORDER BY ' . $this->db->dbprefix("brands") . '.name ASC SEPARATOR ",") as departments,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("project_codes") . '.code ORDER BY ' . $this->db->dbprefix("project_codes") . '.code ASC SEPARATOR ",") as project_codes,
			COUNT(DISTINCT ' .  $this->db->dbprefix("family_payments_sessions") . '.sessionID) AS session_count,
			CONCAT(payment_contact.first_name, " ", payment_contact.last_name) as payment_contact,
			staff.first as staff_first_name, staff.surname as staff_last_name,
			GROUP_CONCAT(DISTINCT CONCAT(booking_contact.first_name, " ", booking_contact.last_name) SEPARATOR "###") as booking_contact_names')
			->from('family_payments')
			->join('family_payments_sessions', 'family_payments.paymentID = family_payments_sessions.paymentID', 'left')
			->join('bookings_cart_sessions', 'family_payments_sessions.sessionID = bookings_cart_sessions.sessionID', 'left')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_contacts as payment_contact', 'family_payments.contactID = payment_contact.contactID', 'left')
			->join('family_contacts as booking_contact', 'bookings_cart.contactID = booking_contact.contactID', 'left')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'left')
			->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'left')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('project_codes', 'bookings.project_codeID = project_codes.codeID', 'left')
			->join('staff', 'family_payments.byID = staff.staffID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->group_by($group_by)
			->order_by('family_payments.added desc')
			->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get selected participant
		$participants = [];
		if (!empty($search_fields['participant_name'])) {
			if (substr($search_fields['participant_name'], 0, 8) == 'contact_') {
				$res_contacts = $this->db->select('contactID, first_name, last_name')
					->from('family_contacts')
					->where([
						'accountID' => $this->auth->user->accountID,
						'active' => 1,
						'contactID' => substr($search_fields['participant_name'], 8)
					])
					->limit(1)
					->get();
				if ($res_contacts->num_rows() > 0) {
					foreach ($res_contacts->result() as $row) {
						$participants['contact_' . $row->contactID] = $row->first_name . ' ' . $row->last_name;
					}
				}
			} else if (substr($search_fields['participant_name'], 0, 6) == 'child_') {
				$res_children = $this->db->select('childID, first_name, last_name')
					->from('family_children')
					->where([
						'accountID' => $this->auth->user->accountID,
						'childID' => substr($search_fields['participant_name'], 6)
					])
					->limit(1)
					->get();
				if ($res_children->num_rows() > 0) {
					foreach ($res_children->result() as $row) {
						$participants['child_' . $row->childID] = $row->first_name . ' ' . $row->last_name;
					}
				}
			}
		}

		// get searched project
		$projects = [];
		if (!empty($search_fields['project_name'])) {
			$res_projects = $this->db->select('bookingID, name')
				->from('bookings')
				->where([
					'accountID' => $this->auth->user->accountID,
					'project' => 1,
					'bookingID' => $search_fields['project_name']
				])
				->limit(1)
				->get();
			if ($res_projects->num_rows() > 0) {
				foreach ($res_projects->result() as $row) {
					$projects[$row->bookingID] = $row->name;
				}
			}
		}

		// get searched block
		$blocks = [];
		if (!empty($search_fields['block'])) {
			$res_blocks = $this->db->select('blockID, name')
				->from('bookings_blocks')
				->where([
					'accountID' => $this->auth->user->accountID,
					'blockID' => $search_fields['block']
				])
				->limit(1)
				->get();
			if ($res_blocks->num_rows() > 0) {
				foreach ($res_blocks->result() as $row) {
					$blocks[$row->blockID] = $row->name;
				}
			}
		}

		// get project codes
		$project_codes = $this->db->from('project_codes')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('desc asc')->get();

		// get session types
		$session_types = $this->db->from('lesson_types')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('name asc')->get();

		// get session types
		$brands = $this->db->from('brands')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'payments' => $res,
			'participants' => $participants,
			'projects' => $projects,
			'blocks' => $blocks,
			'project_codes' => $project_codes,
			'session_types' => $session_types,
			'brands' => $brands,
			'page_base' => $page_base,
			'tab' => $tab,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('reports/payments-export', $data);
		} else {
			$this->crm_view('reports/payments', $data);
		}
	}

	public function bookings($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'payments';
		$section = 'reports';
		$tab = 'bookings';
		$page_base = 'reports/payments/bookings';
		$title = 'Booking Payments & Transactions';
		$buttons = ' <a class="btn btn-primary export-search-submit" href="' . site_url('reports/payments/bookings/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;

		// check if exporting
		if ($action == 'export') {
			$export = TRUE;
		}

		// set up search
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'amount' => NULL,
			'transaction_ref' => NULL,
			'payment_method' => NULL,
			'participant_name' => NULL,
			'project_name' => NULL,
			'project_code' => NULL,
			'block' => NULL,
			'session_type' => NULL,
			'note' => NULL,
			'brand' => NULL,
			'search' => NULL
		);
		$is_search = FALSE;
		$search_where = array();

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_amount', 'Amount', 'trim|xss_clean');
			$this->form_validation->set_rules('search_transaction_ref', 'Transaction Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search_payment_method', 'Payment Method', 'trim|xss_clean');
			$this->form_validation->set_rules('search_participant_name', 'Participant Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_name', 'Project Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_code', 'Project Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search_block', 'Block', 'trim|xss_clean');
			$this->form_validation->set_rules('search_session_type', 'Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_note', 'Note', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand', $this->settings_library->get_label('brand'), 'trim|xss_clean');

			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			foreach ($search_fields as $key => $val) {
				$search_fields[$key] = set_value('search_' . $key);
			}

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-reports'))) {

			foreach ($this->session->userdata('search-reports-payments-booking') as $key => $value) {
				$search_fields[$key] = $value;
			}
		}

		// if dates empty, add default
		$offset = '-1 month';
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset));
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// if from after to, reset
		if (strtotime(uk_to_mysql_date($search_fields['date_from'])) > strtotime(uk_to_mysql_date($search_fields['date_to']))) {
			$search_fields['date_from'] = date('d/m/Y', strtotime($offset, strtotime(uk_to_mysql_date($search_fields['date_to']))));
		}

		if (isset($is_search) && $is_search === TRUE) {
			// store search fields
			$this->session->set_userdata('search-reports-payments-booking', $search_fields);
		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
			if ($date_from !== FALSE) {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart") . "`.`booked` >= " . $this->db->escape($date_from . ' 00:00:00');
			}
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
			if ($date_to !== FALSE) {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart") . "`.`booked` <= " . $this->db->escape($date_to . ' 23:59:59');
			}
		}

		if ($search_fields['amount'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`amount` = " . $this->db->escape($search_fields['amount']);
		}

		if ($search_fields['transaction_ref'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`transaction_ref` = " . $this->db->escape($search_fields['transaction_ref']);
		}

		if ($search_fields['payment_method'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`method` = " . $this->db->escape($search_fields['payment_method']);
		}

		if ($search_fields['note'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("family_payments") . "`.`note` LIKE '%" . $this->db->escape_like_str($search_fields['note']) . "%'";
		}

		if ($search_fields['participant_name'] != '') {
			if (substr($search_fields['participant_name'], 0, 6) == 'child_') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`childID` = " . $this->db->escape(substr($search_fields['participant_name'], 6));
			} else if (substr($search_fields['participant_name'], 0, 8) == 'contact_') {
				$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`contactID` = " . $this->db->escape(substr($search_fields['participant_name'], 8));
			}
		}

		if ($search_fields['project_name'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`bookingID` = " . $this->db->escape($search_fields['project_name']);
		}

		if ($search_fields['project_code'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`project_codeID` = " . $this->db->escape($search_fields['project_code']);
		}

		if ($search_fields['block'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_cart_sessions") . "`.`blockID` = " . $this->db->escape($search_fields['block']);
		}

		if ($search_fields['session_type'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings_lessons") . "`.`typeID` = " . $this->db->escape($search_fields['session_type']);
		}

		if ($search_fields['brand'] != '') {
			$search_where[] = "`" . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brand']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// get report data
		$where = [
			'bookings_cart.accountID' => $this->auth->user->accountID
		];
		$res = $this->db->select('bookings_cart.*, SUM(' .  $this->db->dbprefix("family_payments_sessions") . '.amount) AS amount_partial,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("family_children") . '.first_name, " ", ' .  $this->db->dbprefix("family_children") . '.last_name) SEPARATOR "###") as children_names,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("family_contacts") . '.first_name, " ", ' .  $this->db->dbprefix("family_contacts") . '.last_name) SEPARATOR "###") as contact_names,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("bookings") . '.name ORDER BY ' . $this->db->dbprefix("bookings") . '.name ASC SEPARATOR ",") as project_names,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("bookings_blocks") . '.name ORDER BY ' . $this->db->dbprefix("bookings_blocks") . '.name ASC SEPARATOR ",") as blocks,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("lesson_types") . '.name ORDER BY ' . $this->db->dbprefix("lesson_types") . '.name ASC SEPARATOR ",") as session_types,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("brands") . '.name ORDER BY ' . $this->db->dbprefix("brands") . '.name ASC SEPARATOR ",") as departments,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("project_codes") . '.code ORDER BY ' . $this->db->dbprefix("project_codes") . '.code ASC SEPARATOR ",") as project_codes,
			COUNT(DISTINCT ' .  $this->db->dbprefix("bookings_cart_sessions") . '.sessionID) AS session_count,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("staff") . '.first, " ", ' . $this->db->dbprefix("staff") . '.surname, "@", ' . $this->db->dbprefix("family_payments") . '.internal) SEPARATOR "###") as staff,
			GROUP_CONCAT(DISTINCT CONCAT(booking_contact.first_name, " ", booking_contact.last_name) SEPARATOR "###") as booking_contact_names,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("family_payments") . '.method ORDER BY ' . $this->db->dbprefix("family_payments") . '.method ASC SEPARATOR "###") as payment_methods,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("family_payments") . '.internal ORDER BY ' . $this->db->dbprefix("family_payments") . '.internal ASC SEPARATOR ",") as payment_types,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix("family_payments") . '.transaction_ref, "@", ' . $this->db->dbprefix("family_payments") . '.paymentID) ORDER BY ' . $this->db->dbprefix("family_payments") . '.transaction_ref ASC SEPARATOR ",") as transaction_refs,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix("family_payments") . '.note ORDER BY ' . $this->db->dbprefix("family_payments") . '.note ASC SEPARATOR ",") as notes')
			->from('bookings_cart_sessions')
			->join('family_payments_sessions', 'bookings_cart_sessions.sessionID = family_payments_sessions.sessionID', 'left')
			->join('family_payments', 'family_payments_sessions.paymentID = family_payments.paymentID', 'left')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_contacts as booking_contact', 'bookings_cart.contactID = booking_contact.contactID', 'left')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'left')
			->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'left')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'left')
			->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')
			->join('brands', 'bookings.brandID = brands.brandID', 'left')
			->join('project_codes', 'bookings.project_codeID = project_codes.codeID', 'left')
			->join('staff', 'family_payments.byID = staff.staffID', 'left')
			->where($where)
			->where($search_where, NULL, FALSE)
			->group_by('bookings_cart_sessions.cartID, bookings_cart_sessions.bookingID')
			->order_by('bookings_cart.booked desc')
			->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get selected participant
		$participants = [];
		if (!empty($search_fields['participant_name'])) {
			if (substr($search_fields['participant_name'], 0, 8) == 'contact_') {
				$res_contacts = $this->db->select('contactID, first_name, last_name')
					->from('family_contacts')
					->where([
						'accountID' => $this->auth->user->accountID,
						'active' => 1,
						'contactID' => substr($search_fields['participant_name'], 8)
					])
					->limit(1)
					->get();
				if ($res_contacts->num_rows() > 0) {
					foreach ($res_contacts->result() as $row) {
						$participants['contact_' . $row->contactID] = $row->first_name . ' ' . $row->last_name;
					}
				}
			} else if (substr($search_fields['participant_name'], 0, 6) == 'child_') {
				$res_children = $this->db->select('childID, first_name, last_name')
					->from('family_children')
					->where([
						'accountID' => $this->auth->user->accountID,
						'childID' => substr($search_fields['participant_name'], 6)
					])
					->limit(1)
					->get();
				if ($res_children->num_rows() > 0) {
					foreach ($res_children->result() as $row) {
						$participants['child_' . $row->childID] = $row->first_name . ' ' . $row->last_name;
					}
				}
			}
		}

		// get searched project
		$projects = [];
		if (!empty($search_fields['project_name'])) {
			$res_projects = $this->db->select('bookingID, name')
				->from('bookings')
				->where([
					'accountID' => $this->auth->user->accountID,
					'project' => 1,
					'bookingID' => $search_fields['project_name']
				])
				->limit(1)
				->get();
			if ($res_projects->num_rows() > 0) {
				foreach ($res_projects->result() as $row) {
					$projects[$row->bookingID] = $row->name;
				}
			}
		}

		// get searched block
		$blocks = [];
		if (!empty($search_fields['block'])) {
			$res_blocks = $this->db->select('blockID, name')
				->from('bookings_blocks')
				->where([
					'accountID' => $this->auth->user->accountID,
					'blockID' => $search_fields['block']
				])
				->limit(1)
				->get();
			if ($res_blocks->num_rows() > 0) {
				foreach ($res_blocks->result() as $row) {
					$blocks[$row->blockID] = $row->name;
				}
			}
		}

		// get project codes
		$project_codes = $this->db->from('project_codes')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('desc asc')->get();

		// get session types
		$session_types = $this->db->from('lesson_types')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('name asc')->get();

		// get session types
		$brands = $this->db->from('brands')->where([
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		])->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'payments' => $res,
			'participants' => $participants,
			'projects' => $projects,
			'blocks' => $blocks,
			'project_codes' => $project_codes,
			'session_types' => $session_types,
			'brands' => $brands,
			'page_base' => $page_base,
			'tab' => $tab,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			$this->load->view('reports/payments-bookings-export', $data);
		} else {
			$this->crm_view('reports/payments-bookings', $data);
		}
	}

}

/* End of file Payments.php */
/* Location: ./application/controllers/reports/Payments.php */
