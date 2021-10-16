<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any booking types
		if (!$this->auth->has_features('bookings_bookings') && !$this->auth->has_features('bookings_projects')) {
			show_403();
		}

		$this->load->model('Settings/ProjectCodesModel');
		$this->load->model('Settings/Brands');
		$this->load->model('Settings/ProjectTypes');
		$this->load->model('Settings/Orgs');

		$this->load->library('gocardless_library');
	}

	/**
	 * show list of bookings
	 * @return void
	 */
	public function index($type = 'booking', $projects = FALSE) {

		if (!in_array($type, array('booking', 'event'))) {
			show_404();
		}

		// check permission
		if (($type == 'booking' && $projects != TRUE && !$this->auth->has_features('bookings_bookings'))
		|| ($projects == TRUE && !$this->auth->has_features('bookings_projects'))) {
			show_403();
		}

		// set defaults
		$icon = 'calendar-alt';
		$current_page = $type . 's';
		if ($projects == TRUE) {
			$current_page = 'projects';
		}
		$section = 'bookings';
		$page_base = 'bookings';
		$add_url = 'bookings/contract/new';
		$title = 'Contracts';
		if ($projects == TRUE) {
			$title = 'Projects';
		}
		$buttons = '<a class="btn btn-success" href="' . site_url($add_url) . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$search_store = 'search-' . $type;
		if ($projects == TRUE) {
			$search_store = 'search-projects';
		}

		// set where
		$where = array(
			'bookings.type' => $type,
			'bookings.project !=' => 1,
			'bookings.accountID' => $this->auth->user->accountID
		);

		// check if projects
		if ($projects === 'true') {
			$projects = TRUE;

			// update vars
			$title = 'Projects';
			$page_base = 'bookings/projects';
			$buttons = '<a class="btn btn-success" href="' . site_url('bookings/course/new') . '"><i class="far fa-plus"></i> New Course</a> <a class="btn btn-success" href="' . site_url('bookings/event/new') . '"><i class="far fa-plus"></i> New Event</a>';

			// change where clause
			$where = array(
				'bookings.project' => 1,
				'bookings.accountID' => $this->auth->user->accountID
			);
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'org_id' => NULL,
			'org_type' => NULL,
			'event' => NULL,
			'child_id' => NULL,
			'confirmed' => NULL,
			'completed' => NULL,
			'invoiced' => NULL,
			'cancelled' => NULL,
			'project_type_id' => NULL,
			'brand_id' => NULL,
			'booking_type' => NULL,
			'register_type' => NULL,
			'bookings_site' => NULL,
			'search' => NULL,
			'project_code' => NULL
		);

		// hide completed
		$search_where['completed'] = '`' . $this->db->dbprefix("bookings") . "`.`endDate` >= CURDATE()";
		$order_by = 'bookings.startDate asc, bookings.endDate asc, org asc';

		// if search
		// if search
		if ($this->input->post('s') == "cancel") {
			$this->session->unset_userdata($search_store);
		}else if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_event', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_child_id', 'Child', 'trim|xss_clean');
			$this->form_validation->set_rules('search_org_id', $this->settings_library->get_label('customer'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_org_type', $this->settings_library->get_label('customer') . ' Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_confirmed', 'Confirmed', 'trim|xss_clean');
			$this->form_validation->set_rules('search_completed', 'Completed', 'trim|xss_clean');
			$this->form_validation->set_rules('search_cancelled', 'Cancelled', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_type_id', 'Project Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', $this->settings_library->get_label('brand'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_booking_type', 'Booking Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_register_type', 'Register Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_bookings_site', 'Bookings Site', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_code', 'Project Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$is_search = TRUE;

			$search_fields['date_from'] = set_value('date_from');
			$search_fields['date_to'] = set_value('date_to');
			$search_fields['end_from'] = set_value('search_end_from');
			$search_fields['end_to'] = set_value('search_end_to');
			$search_fields['event'] = set_value('search_event');
			$search_fields['child_id'] = set_value('search_child_id');
			$search_fields['org_id'] = set_value('search_org_id');
			$search_fields['org_type'] = set_value('search_org_type');
			$search_fields['confirmed'] = set_value('search_confirmed');
			$search_fields['completed'] = set_value('search_completed');
			$search_fields['cancelled'] = set_value('search_cancelled');
			$search_fields['project_type_id'] = set_value('search_project_type_id');
			$search_fields['brand_id'] = set_value('search_brand_id');
			$search_fields['booking_type'] = set_value('search_booking_type');
			$search_fields['register_type'] = set_value('search_register_type');
			$search_fields['bookings_site'] = set_value('search_bookings_site');
			$search_fields['project_code'] = set_value('search_project_code');
			$search_fields['search'] = set_value('search');

		} else if (($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata($search_store))) || is_array($this->session->userdata($search_store))) {

			foreach ($this->session->userdata($search_store) as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		$search_child = FALSE;

		if (isset($is_search) && $is_search === TRUE) {

			// store search fields
			$this->session->set_userdata($search_store, $search_fields);

			//processing date filters
			if ($search_fields['date_from'] != '') {
				$start_from = uk_to_mysql_date($search_fields['date_from']);
				if ($start_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`endDate` >= " . $this->db->escape($start_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$end_to = uk_to_mysql_date($search_fields['date_to']);
				if ($end_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`startDate` <= " . $this->db->escape($end_to);
				}
			}

			if ($search_fields['event'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['event']) . "%'";
			}

			if ($search_fields['child_id'] != '') {
				$search_child = TRUE;
				$search_where[] = '`' . $this->db->dbprefix("bookings_cart_sessions") . "`.`childID` = " . $this->db->escape($search_fields['child_id']);
			}

			if ($search_fields['org_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`orgID` = " . $this->db->escape($search_fields['org_id']);
			}

			if ($search_fields['org_type'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`type` = " . $this->db->escape($search_fields['org_type']);
			}

			if ($search_fields['confirmed'] != '') {
				if ($search_fields['confirmed'] == 'yes') {
					$confirmed = 1;
				} else {
					$confirmed = 0;
				}
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`confirmed` = " . $this->db->escape($confirmed);
			}

			if ($search_fields['cancelled'] != '') {
				if ($search_fields['cancelled'] == 'yes') {
					$cancelled = 1;
				} else {
					$cancelled = 0;
				}
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`cancelled` = " . $this->db->escape($cancelled);
			}

			if ($search_fields['project_type_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`project_typeID` = " . $this->db->escape($search_fields['project_type_id']);
				$current_page = 'projects_type_' . $search_fields['project_type_id'];
			}

			if ($search_fields['brand_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`brandID` = " . $this->db->escape($search_fields['brand_id']);
			}

			if ($search_fields['register_type'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`register_type` = " . $this->db->escape($search_fields['register_type']);
			}

			if ($search_fields['booking_type'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`type` = " . $this->db->escape($search_fields['booking_type']);
			}

			if ($search_fields['project_code'] != '') {
				if($search_fields['project_code'] == 'none') {
					$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`project_codeID` IS NULL";
				} else {
					$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`project_codeID` = " . $this->db->escape_like_str($search_fields['project_code']);
				}

			}

			if ($search_fields['bookings_site'] != '') {
				if ($search_fields['bookings_site'] == 'yes') {
					$confirmed = 1;
				} else {
					$confirmed = 0;
				}
				$search_where[] = '`' . $this->db->dbprefix("bookings") . "`.`public` = " . $this->db->escape($confirmed);
			}

			switch ($search_fields['completed']) {
				case 'yes':
					$search_where['completed'] = '`' . $this->db->dbprefix("bookings") . "`.`endDate` < CURDATE()";
					$order_by = 'bookings.endDate desc, bookings.startDate desc, org asc';
					break;
				default:
					$search_where['completed'] = '`' . $this->db->dbprefix("bookings") . "`.`endDate` >= CURDATE()";
					$order_by = 'bookings.startDate asc, bookings.endDate asc, org asc';
					break;
			}

		}

		if (array_key_exists('completed', $search_where)) {
			$search_where[] = $search_where['completed'];
			unset($search_where['completed']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('bookings.bookingID')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('bookings_lessons', 'bookings.bookingID = bookings_lessons.bookingID', 'left');

		if ($search_child === TRUE) {
			$res = $res->join('bookings_cart_sessions', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner');
		}

		$res = $res->where($where)->where($search_where, NULL, FALSE)->group_by('bookings.bookingID')->get();
		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select(array($this->db->dbprefix('bookings') . '.bookingID,
			' . $this->db->dbprefix('bookings') . '.type,
			' . $this->db->dbprefix('bookings') . '.project,
			' . $this->db->dbprefix('bookings') . '.confirmed,
			' . $this->db->dbprefix('bookings') . '.invoiced,
			' . $this->db->dbprefix('bookings') . '.riskassessed,
			' . $this->db->dbprefix('bookings') . '.startDate,
			' . $this->db->dbprefix('bookings') . '.endDate,
			' . $this->db->dbprefix('bookings') . '.name as event,
			' . $this->db->dbprefix('bookings') . '.orgID,
			' . $this->db->dbprefix('orgs') . '.name as org,
			' . $this->db->dbprefix('brands') . '.colour as brand_colour,
			GROUP_CONCAT(DISTINCT CONCAT(' . $this->db->dbprefix('staff') . '.first, \' \', ' . $this->db->dbprefix('staff') . '.surname) ORDER BY ' . $this->db->dbprefix('staff') . '.first ASC,' . $this->db->dbprefix('staff') . '.surname ASC SEPARATOR \', \') AS staff'), FALSE)->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->join('brands', 'bookings.brandID = brands.brandID', 'left')->join('bookings_lessons_staff', 'bookings.bookingID = bookings_lessons_staff.bookingID', 'left')->join('staff', 'bookings_lessons_staff.staffID = staff.staffID', 'left');

		if ($search_child === TRUE) {
			$res = $res->join('bookings_cart_sessions', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner');
		}
		$res = $res->where($where)->where($search_where, NULL, FALSE)->group_by('bookings.bookingID')->order_by($order_by)->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// get booking ids
		$bookingIDs = array();
		$orgIDs = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$bookingIDs[] = $row->bookingID;
				$orgIDs[] = $row->orgID;
			}
		}

		// cache lookups
		$invoiced_blocks = array();
		$invoiced_bookings = array();
		$booking_lessons = array();
		$booking_cancellations = array();
		$booking_addresses = array();
		$booking_blocks = array();
		$booking_activities = array();
		$booking_types = array();
		$customer_safety = array();

		// if some bookings returned
		if (count($bookingIDs) > 0) {
			// get types and activties
			$res_types = $this->db->select('bookings_lessons.bookingID, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('activities') . '.name SEPARATOR \'!SEPARATOR!\') AS activities,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('bookings_lessons') . '.activity_other SEPARATOR \'!SEPARATOR!\') AS activities_other,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('lesson_types') . ' .name SEPARATOR \'!SEPARATOR!\') AS types,
			GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('bookings_lessons') . '.type_other SEPARATOR \'!SEPARATOR!\') AS types_other', FALSE)->from('bookings_lessons')->join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'left')->where_in('bookingID', $bookingIDs)->group_by('bookings_lessons.bookingID')->get();
			if ($res_types->num_rows() > 0) {
				foreach($res_types->result() as $row) {
					$activities = explode("!SEPARATOR!", $row->activities);
					if (is_array($activities) && count($activities) > 0) {
						foreach ($activities as $item) {
							if ($item != "other") {
								$booking_activities[$row->bookingID][$item] = $item;
							}
						}
					}
					$activities_other = explode("!SEPARATOR!", $row->activities_other);
					if (is_array($activities_other) && count($activities_other) > 0) {
						foreach ($activities_other as $item) {
							$booking_activities[$row->bookingID][$item] = $item;
						}
					}
					$types = explode("!SEPARATOR!", $row->types);
					if (is_array($types) && count($types) > 0) {
						foreach ($types as $item) {
							if ($item != "other") {
								$booking_types[$row->bookingID][$item] = $item;
							}
						}
					}
					$types_other = explode("!SEPARATOR!", $row->types_other);
					if (is_array($types_other) && count($types_other) > 0) {
						foreach ($types_other as $item) {
							$booking_types[$row->bookingID][$item] = $item;
						}
					}
				}
			}


			// get blocks
			$where = array(
				'accountID' => $this->auth->user->accountID
			);
			$res_blocks = $this->db->from('bookings_blocks')->where($where)->where_in('bookingID', $bookingIDs)->order_by('startDate asc, endDate asc')->get();
			if ($res_blocks->num_rows() > 0) {
				foreach($res_blocks->result() as $row) {
					$booking_blocks[$row->bookingID][] = array(
						'blockID' => $row->blockID,
						'startDate' => $row->startDate,
						'endDate' => $row->endDate,
						'name' => $row->name,
						'targets_missed' => $row->targets_missed
					);
				}
			}

			if ($type == 'booking') {
				// get lessons, cancellations and addresses
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$res_lessons = $this->db->from('bookings_lessons')->where($where)->where_in('bookingID', $bookingIDs)->get();
				if ($res_lessons->num_rows() > 0) {
					foreach($res_lessons->result() as $row) {
						$booking_lessons[$row->bookingID][$row->blockID][$row->lessonID] = array(
							'day' => $row->day,
							'startDate' => $row->startDate,
							'endDate' => $row->endDate
						);
						$booking_addresses[$row->bookingID][$row->addressID] = $row->addressID;
					}
				}
				$where = array(
					'type' => 'cancellation',
					'accountID' => $this->auth->user->accountID
				);
				$res_cancellations = $this->db->from('bookings_lessons_exceptions')->where_in('bookingID', $bookingIDs)->where($where)->get();
				if ($res_cancellations->num_rows() > 0) {
					foreach($res_cancellations->result() as $row) {
						$booking_cancellations[$row->bookingID][] = $row->exceptionID;
					}
				}

				// get safety docs - check delivery address has both school and camp inductions and not expired
				$where = array(
					'expiry >=' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$where_in = array(
					'risk assessment',
					'school induction',
					'accountID' => $this->auth->user->accountID
				);

				// risk
				$res_risk = $this->db->from('orgs_safety')->where($where)->where_in('type', $where_in)->where_in('orgID', $orgIDs)->get();
				if ($res_risk->num_rows() > 0) {
					foreach($res_risk->result() as $row) {
						$customer_safety[$row->orgID][$row->type][$row->addressID] = $row->addressID;
					}
				}
			}

			if ($type == 'booking' || $projects === TRUE) {
				// get invoiced block
				$where = array(
					'is_invoiced' => 1,
					'bookings_invoices.accountID' => $this->auth->user->accountID
				);
				$res_invoices = $this->db->from('bookings_invoices')->join('bookings_invoices_blocks', 'bookings_invoices.invoiceID = bookings_invoices_blocks.invoiceID', 'inner')->group_by('bookings_invoices_blocks.blockID')->where_in('bookingID', $bookingIDs)->where($where)->get();
				if ($res_invoices->num_rows() > 0) {
					foreach($res_invoices->result() as $row) {
						$invoiced_blocks[$row->blockID] = TRUE;
					}
				}
				// get invoiced bookings
				$where = array(
					'is_invoiced' => 1,
					'bookings_invoices.type' => 'booking',
					'bookings_invoices.accountID' => $this->auth->user->accountID
				);
				$res_invoices = $this->db->select('bookingID')->from('bookings_invoices')->where_in('bookingID', $bookingIDs)->where($where)->get();
				if ($res_invoices->num_rows() > 0) {
					foreach($res_invoices->result() as $row) {
						$invoiced_bookings[$row->bookingID] = TRUE;
					}
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}
		$children = $this->db->from('family_children')->where(['accountID' => $this->auth->user->accountID])->order_by('first_name asc, last_name asc')->get();

		$orgs = $this->Orgs->getList($this->auth->user->accountID, 'name asc');

		$projectTypes = $this->ProjectTypes->getList($this->auth->user->accountID, 'name asc');

		$brands = $this->Brands->getList($this->auth->user->accountID, true, 'name asc');

		$projectCodes = $this->ProjectCodesModel->getList($this->auth->user->accountID, 1);


		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'type' => $type,
			'page_base' => $page_base,
			'bookings' => $res,
			'projects' => $projects,
			'booking_blocks' => $booking_blocks,
			'booking_lessons' => $booking_lessons,
			'booking_cancellations' => $booking_cancellations,
			'booking_addresses' => $booking_addresses,
			'booking_types' => $booking_types,
			'booking_activities' => $booking_activities,
			'invoiced_bookings' => $invoiced_bookings,
			'invoiced_blocks' => $invoiced_blocks,
			'customer_safety' => $customer_safety,
			'orgs' => $orgs,
			'children' => $children,
			'project_types' => $projectTypes,
			'project_codes' => $projectCodes,
			'brands' => $brands,
			'add_url' => $add_url,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('bookings/main', $data);
	}

	/**
	 * show bookings dashboard
	 * @return void
	 */
	public function dashboard(){

		// check permission
		if (!$this->auth->has_features('bookings_bookings')) {
			show_403();
		}

		// if posted
		if ($this->input->post()) {
			$this->session->unset_userdata('search-projects');
			$search_store = array();
			if($this->input->post('course')){
				$search_store = array(
					'booking_type' => 'booking'
				);
			}else if($this->input->post('event')) {
				$search_store = array(
					'booking_type' => 'event'
				);
			} else {
				$search_store = array(
					'bookings_site' => 'yes',
				);
			}
			$this->session->set_userdata('search-projects', $search_store);
			redirect('bookings/projects/recall');
		}

		$booking_link = $this->auth->get_bookings_site();

		$data = array(
			'title' => 'Bookings',
			'icon' => 'tachometer-alt',
			'page_base' => '',
			'section' => 'bookings',
			'current_page' => 'dashboard',
			'booking_link' => $booking_link
		);

		// load view
		$this->crm_view('bookings/dashboard', $data);
	}

	/**
	 * edit a booking
	 * @param  int $bookingID
	 * @param string $type
	 * @return void
	 */
	public function edit($bookingID = NULL, $type = 'booking', $is_project = FALSE)
	{

		// check permission
		if (($type == 'booking' && $is_project != TRUE && !$this->auth->has_features('bookings_bookings'))
		|| ($is_project == TRUE && !$this->auth->has_features('bookings_projects'))) {
			show_403();
		}

		$booking_info = new stdClass;
		$breadcrumb_levels = array();
		$submit_to = NULL;

		// check if editing
		if ($bookingID != NULL) {

			// check if numeric
			if (!ctype_digit($bookingID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'bookings.bookingID' => $bookingID,
				'bookings.accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->select('bookings.*, orgs.name as org, orgs.type as org_type')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'left')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$booking_info = $row;
				$type = $booking_info->type;

				if ($booking_info->project == 1) {
					$is_project = 'true';
					$breadcrumb_levels['bookings/projects'] = 'Projects';
				} else {
					$breadcrumb_levels['bookings'] = 'Contracts';
				}

				// work out submit to
				$submit_to = 'bookings/contract/' . $bookingID;
				if ($is_project) {
					if ($booking_info->type === 'event') {
						$submit_to = 'bookings/event/' . $bookingID;
					} else {
						$submit_to = 'bookings/course/' . $bookingID;
					}
					$submit_to .= $this->crm_library->last_segment()=="booking-site" ? '/booking-site' : '';
				}

				// redirect so unique edit page per booking type
				if (stripos(uri_string(), 'bookings/edit') === 0 || stripos(uri_string(), $submit_to) === FALSE) {
					redirect($submit_to);
					exit();
				}

				// get booking tags
				$booking_info->tags = array();
				$where = array(
					'bookings_tags.accountID' => $this->auth->user->accountID,
					'bookings_tags.bookingID' => $bookingID
				);
				$res = $this->db->select('settings_tags.*')->from('bookings_tags')->join('settings_tags', 'bookings_tags.tagID = settings_tags.tagID', 'inner')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$booking_info->tags[] = $row->name;
					}
				}

				// get booking images
				$booking_info->images = array();
				$where = array(
					'bookings_images.accountID' => $this->auth->user->accountID,
					'bookings_images.bookingID' => $bookingID
				);
				$res = $this->db->select('bookings_images.*')->from('bookings_images')->where($where)->get();
				if ($res->num_rows() > 0) {
					$i = 1;
					foreach ($res->result() as $row) {
						$booking_info->images[$i] = $row;
						$i++;
					}
				}
			}
		} else {
			// new booking
			// work out submit to
			$submit_to = 'bookings/contract/new';
			if ($is_project) {
				if ($type === 'event') {
					$submit_to = 'bookings/event/new';
				} else {
					$submit_to = 'bookings/course/new';
				}
			}

			// redirect so unique edit page per booking type
			if (stripos(uri_string(), 'bookings/new') === 0) {
				redirect($submit_to);
				exit();
			}
		}

		if (!in_array($type, array('booking', 'event'))) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Contract';
		$return_to = 'bookings';
		if ($type != 'booking') {
			$return_to .= '/' . $type . 's';
		}
		if ($bookingID != NULL) {
			$title = 'Edit Contract';
		} else {
			if ($is_project == TRUE) {
				$breadcrumb_levels['bookings/projects'] = 'Projects';
			} else {
				$breadcrumb_levels['bookings'] = 'Contracts';
			}
		}
		$icon = 'calendar-alt';
		$tab = 'details';
		$current_page = $type . 's';
		$section = 'bookings';

		// check if is project
		if ($is_project === 'true') {
			$is_project = TRUE;

			// update vars
			$title = 'New Course';
			if ($type == 'event') {
				$title = 'New Event';
			}
			if ($bookingID != NULL) {
				$title = $booking_info->name;
			}
			$return_to = 'bookings/projects';
			$current_page = 'projects';
		}

		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$add_contact = 0;
		$errors = array();
		$success = NULL;
		$info = NULL;

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

		// get list of session types
		$lesson_types = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_types[$row->typeID] = $row->name;
			}
		}

		// get list of org attachments, if editing - not before as don't know org id
		$org_attachments = array();
		if ($bookingID != NULL) {
			$where = array(
				'orgID' => $booking_info->orgID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('orgs_attachments')->where($where)->order_by('name asc')->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$org_attachments[$row->attachmentID] = $row->name;
				}
			}
		}

		// if posted
		if ($this->input->post()) {

			if ($this->input->post('add_contact') == 1) {
				$add_contact = 1;
			}

			// set validation rules
			if ($type == 'event' || $is_project === TRUE) {
				$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			}
			if ($is_project === TRUE) {
				$this->form_validation->set_rules('project_typeID', 'Project Type', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('project_codeID', 'Project Code', 'trim|xss_clean');

			if ($bookingID == NULL || in_array($this->auth->user->department, array('directors', 'management'))) {
				switch ($type) {
					case 'booking':
						$this->form_validation->set_rules('orgID', $this->settings_library->get_label('customer'), 'trim|xss_clean|required');
						break;
					case 'event':
						$this->form_validation->set_rules('orgID', 'Venue', 'trim|xss_clean|required');
						break;
				}
			}

			if ($type == 'event') {
				$this->form_validation->set_rules('addressID', 'Address', 'trim|xss_clean|required');
			}

			if ($type == 'booking') {
				if ($add_contact != 1) {
					$this->form_validation->set_rules('contactID', 'Contact', 'trim|xss_clean|required');
				} else {
					$this->form_validation->set_rules('contact_name', 'Name', 'trim|xss_clean|required');
					$this->form_validation->set_rules('contact_position', 'Position', 'trim|xss_clean|required');
					$this->form_validation->set_rules('contact_tel', 'Phone', 'trim|xss_clean|callback_phone_or_mobile[' . $this->input->post('contact_mobile') . ']');
					$this->form_validation->set_rules('contact_mobile', 'Mobile', 'trim|xss_clean');
					$this->form_validation->set_rules('contact_email', 'Email', 'trim|xss_clean|required|valid_email');
				}
			}
			$label = NULL;
			if ($type == 'booking') {
				$label = 'Contract ';
			}
			$this->form_validation->set_rules('startDate', $label . 'Start', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('endDate', $label . 'End', 'trim|xss_clean|required|callback_check_date|callback_after_start[' . $this->input->post('startDate') . ']');
			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand'), 'trim|xss_clean|required');

			if ($is_project === TRUE) {
				$this->form_validation->set_rules('register_type', 'Register Type', 'trim|xss_clean|required');
				$this->form_validation->set_rules('booking_postcodes', 'Booking Postcodes', 'trim|strtoupper|xss_clean');
				$this->form_validation->set_rules('min_age', 'Minimum Age', 'trim|integer|greater_than[0]|xss_clean');
				$this->form_validation->set_rules('max_age', 'Maximum Age', 'trim|integer|greater_than[0]|xss_clean');
				if ($type == 'booking' && !in_array($this->input->post('register_type'), array('numbers', 'names', 'bikeability', 'shapeup'))) {
					$this->form_validation->set_rules('booking_requirement', 'Booking Requirement', 'trim|xss_clean|required');
				}
			}

			switch ($type) {
				case 'booking':
					$this->form_validation->set_rules('contract_renewal', 'Contract Renewal', 'trim|xss_clean');
					$this->form_validation->set_rules('renewalDate', 'Contract Renewal Date', 'trim|xss_clean|callback_check_date');
					$this->form_validation->set_rules('renewalMeetingDate', 'Contract Renewal Meeting Date', 'trim|xss_clean|callback_check_date');
					$this->form_validation->set_rules('contract_renewed', 'Contract Renewed Status', 'trim|xss_clean');
					break;
			}
			if ($type == 'event' || $is_project === TRUE) {
				$this->form_validation->set_rules('public', 'Show on Bookings Site', 'trim|xss_clean');
				$this->form_validation->set_rules('limit_participants', 'Limit Online Booking to Target Participant Count', 'trim|xss_clean');
				$this->form_validation->set_rules('online_booking_password', 'Online Booking Password', 'trim|xss_clean');
				$this->form_validation->set_rules('disable_online_booking', 'Disable Online Booking', 'trim|xss_clean');
				$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
				$this->form_validation->set_rules('website_description', 'Web site description', 'trim|xss_clean');
				$this->form_validation->set_rules('autodiscount', 'Automatic discount', 'trim|xss_clean|required');
				$this->form_validation->set_rules('autodiscount_amount', 'Automatic discount amount', 'trim|xss_clean|callback_required_if_not_off[' . $this->input->post('autodiscount') . ']|numeric|callback_more_than_zero[' . $this->input->post('autodiscount') . ']|callback_percentage_check[' . $this->input->post('autodiscount') . ']');
				$this->form_validation->set_rules('siblingdiscount', 'Sibling discount', 'trim|xss_clean|required');
				$this->form_validation->set_rules('siblingdiscount_amount', 'Sibling discount amount', 'trim|xss_clean|callback_required_if_not_off[' . $this->input->post('siblingdiscount') . ']|numeric|callback_more_than_zero[' . $this->input->post('siblingdiscount') . ']|callback_percentage_check[' . $this->input->post('siblingdiscount') . ']');
				$this->form_validation->set_rules('subscriptions_only', 'Subscriptions Only', 'trim|xss_clean');
				$this->form_validation->set_rules('thanksemail', 'Send Thanks Email', 'trim|xss_clean');
				$this->form_validation->set_rules('thanksemail_text', 'Thanks Email', 'trim|callback_required_if_checked[' . $this->input->post('thanksemail') . ']');
				$this->form_validation->set_rules('cancelled', 'Cancel Event', 'trim|xss_clean');
				$this->form_validation->set_rules('booking_instructions', 'Participant Booking Instructions', 'trim');
				for ($i=1; $i <= 20; $i++) {
					$this->form_validation->set_rules('monitoring'.$i, 'Monitoring ' . $i, 'trim|xss_clean');
					$this->form_validation->set_rules('monitoring'.$i."_entry_type", 'Monitoring ' . $i, 'trim|xss_clean');
					$this->form_validation->set_rules('monitoring'.$i."_mandatory", 'Monitoring ' . $i, 'trim|xss_clean');
				}
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok

				// sort dates
				$renewalDate = NULL;
				if (set_value('renewalDate') != '') {
					$renewalDate = uk_to_mysql_date(set_value('renewalDate'));
				}

				$renewalMeetingDate = NULL;
				if (set_value('renewalMeetingDate') != '') {
					$renewalMeetingDate = uk_to_mysql_date(set_value('renewalMeetingDate'));
				}

				// prepare data
				$data = array(
					'type' => $type,
					'startDate' => uk_to_mysql_date(set_value('startDate')),
					'endDate' => uk_to_mysql_date(set_value('endDate')),
					'brandID' => set_value('brandID'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				$data['project_codeID'] = NULL;
				if (set_value('project_codeID') != '') {
					$data['project_codeID'] = set_value('project_codeID');
				}
				if ($is_project === TRUE) {
					$data['project_typeID'] = set_value('project_typeID');
					$data['register_type'] = set_value('register_type');
					$data['booking_postcodes'] = set_value('booking_postcodes');
					$data['min_age'] = NULL;
					if (set_value('min_age') !== "") {
						$data['min_age'] = set_value('min_age');
					}
					$data['max_age'] = NULL;
					if (set_value('max_age') !== "") {
						$data['max_age'] = set_value('max_age');
					}
					if ($type == 'booking') {
						$data['booking_requirement'] = set_value('booking_requirement');
					}
				}

				switch ($type) {
					case 'booking':
						$data['contactID'] = set_value('contactID');
						$data['renewalDate'] = $renewalDate;
						$data['renewalMeetingDate'] = $renewalMeetingDate;
						$data['contract_renewal'] = 0;
						$data['contract_renewed'] = NULL;

						if (set_value('contract_renewal') == 1) {
							$data['contract_renewal'] = 1;
						}
						if (!empty(set_value('contract_renewed'))) {
							$data['contract_renewed'] = set_value('contract_renewed');
						}
						break;
					case 'event':
						$data['addressID'] = set_value('addressID');
						break;
				}
				if ($type == 'event' || $is_project === TRUE) {
					$data['location'] = set_value('location');
					$data['public'] = 0;
					$data['limit_participants'] = 0;
					$data['disable_online_booking'] = 0;
					$data['website_description'] = set_value('website_description');
					$data['online_booking_password'] = set_value('online_booking_password');
					$data['autodiscount'] = 'off';
					$data['autodiscount_amount'] = 0;
					$data['siblingdiscount'] = 'off';
					$data['siblingdiscount_amount'] = 0;
					$data['subscriptions_only'] = 0;
					$data['thanksemail'] = 0;
					$data['thanksemail_text'] = $this->input->post('thanksemail_text', FALSE);
					$data['cancelled'] = 0;
					$data['booking_instructions'] = $this->input->post('booking_instructions', FALSE);

					if (set_value('public') == 1) {
						$data['public'] = 1;
					}

					if (set_value('limit_participants') == 1) {
						$data['limit_participants'] = 1;
					}

					if (set_value('disable_online_booking') == 1) {
						$data['disable_online_booking'] = 1;
					}

					if (in_array(set_value('autodiscount'), array('percentage', 'amount', 'fixed'))) {
						$data['autodiscount'] = set_value('autodiscount');
					}

					if (set_value('autodiscount_amount') > 0) {
						$data['autodiscount_amount'] = set_value('autodiscount_amount');
					}

					if (in_array(set_value('siblingdiscount'), array('percentage', 'amount', 'fixed'))) {
						$data['siblingdiscount'] = set_value('siblingdiscount');
					}

					if (set_value('siblingdiscount_amount') > 0) {
						$data['siblingdiscount_amount'] = set_value('siblingdiscount_amount');
					}

					if(set_value('subscriptions_only') == 1) {
						$data['subscriptions_only'] = 1;
					}

					if (set_value('thanksemail') == 1) {
						$data['thanksemail'] = 1;
					}

					if (set_value('cancelled') == 1) {
						$data['cancelled'] = 1;
					}

					for ($i=1; $i <= 20; $i++) {
						$data['monitoring' . $i] = set_value('monitoring' . $i);
						$data['monitoring'.$i."_entry_type"] = set_value('monitoring'.$i."_entry_type");
						$data['monitoring'.$i."_mandatory"] = set_value('monitoring'.$i."_mandatory");
					}
				}

				if ($type == 'event' || $is_project === TRUE) {
					$data['name'] = set_value('name');
				}

				if ($bookingID == NULL || in_array($this->auth->user->department, array('directors', 'management'))) {
					$data['orgID'] = set_value('orgID');
				}

				if ($bookingID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// if is project, store so
				if ($is_project === TRUE) {
					$data['project'] = 1;
				}

				// final check for errors
				if (count($errors) == 0) {

					// insert contact
					if ($add_contact == 1) {
						$contact_data = array(
							'orgID' => set_value('orgID'),
							'byID' => $this->auth->user->staffID,
							'name' => set_value('contact_name'),
							'position' => set_value('contact_position'),
							'tel' => set_value('contact_tel'),
							'mobile' => set_value('contact_mobile'),
							'email' => set_value('contact_email'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						if ($bookingID != NULL) {
							$contact_data['orgID'] = $booking_info->orgID;
						}

						$this->db->insert('orgs_contacts', $contact_data);

						$data['contactID'] = $this->db->insert_id();

					}

					if ($bookingID == NULL) {
						// insert id
						$query = $this->db->insert('bookings', $data);

						$affected_rows = $this->db->affected_rows();

						$bookingID = $this->db->insert_id();

						$just_added = TRUE;

						// switch prospect to customer if setting allows
						if ($this->settings_library->get('disable_prospects_automation') != 1) {
							$data = array(
								'prospect' => 0
							);
							$where = array(
								'orgID' => set_value('orgID'),
								'prospect' => 1,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->update('orgs', $data, $where, 1);
						}

						// redirect to blocks
						$return_to = 'bookings/blocks/' . $bookingID;
					} else {
						$where = array(
							'bookingID' => $bookingID,
							'accountID' => $this->auth->user->accountID
						);

						// update
						$query = $this->db->update('bookings', $data, $where);

						$affected_rows = $this->db->affected_rows();

						$return_to = $submit_to;

						// switch prospect to customer if changed and setting allows
						if ($this->settings_library->get('disable_prospects_automation') != 1 && in_array($this->auth->user->department, array('directors', 'management')) && $booking_info->orgID != set_value('orgID')) {
							$data = array(
								'prospect' => 0
							);
							$where = array(
								'orgID' => set_value('orgID'),
								'prospect' => 1,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->update('orgs', $data, $where, 1);
						}

						// add/update org attachments
						$org_attachments_posted = $this->input->post('org_attachments');
						if (!is_array($org_attachments_posted)) {
							$org_attachments_posted = array();
						}
						foreach ($org_attachments as $attachmentID => $attachment) {
							$where = array(
								'bookingID' => $bookingID,
								'attachmentID' => $attachmentID,
								'accountID' => $this->auth->user->accountID
							);
							if (!in_array($attachmentID, $org_attachments_posted)) {
								// not set, remove
								$this->db->delete('bookings_orgs_attachments', $where);
							} else {
								// look up, see if site record already exists
								$res = $this->db->from('bookings_orgs_attachments')->where($where)->get();

								$data = array(
									'bookingID' => $bookingID,
									'attachmentID' => $attachmentID,
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);

								if ($res->num_rows() > 0) {
									$this->db->update('bookings_orgs_attachments', $data, $where);
								} else {
									$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
									$this->db->insert('bookings_orgs_attachments', $data);
								}
							}
						}
					}

					// add/update tags
					$tags = $this->input->post('tags');
					if (!is_array($tags)) {
						$tags = array();
					}
					// remove existing
					$where = array(
						'bookingID' => $bookingID,
						'accountID' => $this->auth->user->accountID
					);
					$this->db->delete('bookings_tags', $where);
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
								'bookingID' => $bookingID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('bookings_tags', $data);
						}
					}

					// add/update pricing
					$prices_posted = $this->input->post('prices');
					if (!is_array($prices_posted)) {
						$prices_posted = array();
					}
					$prices_contract_posted = $this->input->post('prices_contract');
					if (!is_array($prices_contract_posted)) {
						$prices_contract_posted = array();
					}
					foreach ($lesson_types as $typeID => $type_label) {
						$where = array(
							'bookingID' => $bookingID,
							'typeID' => $typeID,
							'accountID' => $this->auth->user->accountID
						);
						if (!(array_key_exists($typeID, $prices_posted) && $prices_posted[$typeID] > 0) && !in_array($typeID, $prices_contract_posted)) {
							// delete existing
							$this->db->delete('bookings_pricing', $where);
						} else {
							// look up, see if already exists
							$res = $this->db->from('bookings_pricing')->where($where)->get();

							$data = array(
								'bookingID' => $bookingID,
								'typeID' => $typeID,
								'amount' => 0,
								'contract' => 0,
								'accountID' => $this->auth->user->accountID,
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);

							if (array_key_exists($typeID, $prices_posted) && $prices_posted[$typeID] > 0) {
								$data['amount'] = floatval($prices_posted[$typeID]);
							}

							if (in_array($typeID, $prices_contract_posted)) {
								$data['contract'] = 1;
							}

							if ($res->num_rows() > 0) {
								$this->db->update('bookings_pricing', $data, $where);
							} else {
								$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$this->db->insert('bookings_pricing', $data);
							}
						}
					}

					// if event cancelled
					if (set_value('cancelled') == 1) {
						// remove staff from event
						$where = array(
							'bookingID' => $bookingID,
							'accountID' => $this->auth->user->accountID
						);
						$res = $this->db->delete('bookings_lessons_staff', $where);

						// remove staff exceptions
						$where['type'] = 'staffchange';
						$res = $this->db->delete('bookings_lessons_exceptions', $where);
					}

					// booking images
					for ($i = 1; $i <= 4; $i++) {

						$delete_images = $this->input->post('delete_images');

						// delete if requested
						if (is_array($delete_images) && array_key_exists($i, $delete_images) && array_key_exists($i, $booking_info->images) && file_exists(UPLOADPATH . $booking_info->images[$i]->path)) {
								// delete image
								unlink(UPLOADPATH . $booking_info->images[$i]->path);
								// delete from db
								$where = array(
									'imageID' => $delete_images[$i],
									'bookingID' => $bookingID,
									'accountID' => $this->auth->user->accountID
								);
								$res = $this->db->delete('bookings_images', $where);
								// remove from array
								unset($booking_info->images[$i]);
						}

						// upload image
						$upload_res = $this->crm_library->handle_image_upload('image_' . $i, FALSE, $this->auth->user->accountID, 1024, 1024, 512, 512, TRUE);

						if ($upload_res !== NULL) {

							// update db with new file
							$data = array(
								'byID' => $this->auth->user->staffID,
								'name' => $upload_res['client_name'],
								'order' => $i,
								'path' => $upload_res['raw_name'],
								'type' => $upload_res['file_type'],
								'size' => $upload_res['file_size']*1024,
								'ext' => substr($upload_res['file_ext'], 1),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							if (isset($booking_info->images[$i])) {
								// update
								$where = array(
									'imageID' => $booking_info->images[$i]->imageID,
									'bookingID' => $bookingID,
									'accountID' => $this->auth->user->accountID
								);
								$query = $this->db->update('bookings_images', $data, $where);
							} else {
								// insert
								$data['accountID'] = $this->auth->user->accountID;
								$data['bookingID'] = $bookingID;
								$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$query = $this->db->insert('bookings_images', $data);
							}

							// delete previous file, if exists
							if (isset($booking_info->images[$i]) && is_array($booking_info->images) && array_key_exists($i, $booking_info->images) && file_exists(UPLOADPATH . $booking_info->images[$i]->path)) {
								unlink(UPLOADPATH . $booking_info->images[$i]->path);
							}

							// save in array
							$booking_info->images[$i] = (object)$data;
						}

					}

					// if inserted/updated
					if ($affected_rows == 1) {

						// switch title for success message
						if ($is_project == TRUE) {
							$type = 'Project';
						}

						if (isset($just_added)) {
							$this->session->set_flashdata('success', ucwords($type) . ' has been created successfully, continue to add a block below.');
						} else {
							$this->session->set_flashdata('success', ucwords($type) . ' has been updated successfully.');
						}

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
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

		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		// if editing, only show those belonging to org
		if ($bookingID != NULL && !in_array($this->auth->user->department, array('directors', 'management'))) {
			$where = array(
				'orgID' => $booking_info->orgID
			);
		}

		// organisations
		$organisations = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// contacts
		$contacts = $this->db->from('orgs_contacts')->where($where)->order_by('name asc')->get();

		// addresses
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();

		// orgs attachments
		$org_attachments_array = array();
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('bookings_orgs_attachments')->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$org_attachments_array[] = $row->attachmentID;
			}
		}

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($bookingID != NULL) {
			$or_where[] = '`brandID` = ' . $this->db->escape($booking_info->brandID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($where, NULL, FALSE)->order_by('name asc')->get();

		// project types
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$project_types = $this->db->from('project_types')->where($where)->order_by('name asc')->get();

		// project codes
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$project_codes = $this->db->from('project_codes')->where($where)->order_by('code asc')->get();

		// pricing
		$prices_array = array();
		$prices_contract_array = array();
		if ($bookingID != NULL) {
			$where = array(
				'bookingID' => $bookingID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('bookings_pricing')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$prices_array[$row->typeID] = $row->amount;
					if ($row->contract == 1) {
						$prices_contract_array[] = $row->typeID;
					}
				}
			}
		}

		// has org bookable blocks
		$has_org_bookable_blocks = FALSE;
		if ($bookingID != NULL) {
			$where = array(
				'bookingID' => $bookingID,
				'org_bookable' => 1,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('bookings_blocks')->where($where)->limit(1)->get();
			if ($res->num_rows() > 0) {
				$has_org_bookable_blocks = TRUE;
			}
		}

		$gc_error = NULL;
		$stripe_error = NULL;

		if ($this->gocardless_library->valid_config() !== TRUE) {
			$gc_error = 'Please complete the information for GoCardless in ' . anchor('settings/integrations', 'Settings > Integrations') . '.';
		}

		$stripe_pk = $this->settings_library->get('stripe_pk', $this->cart_library->accountID);
		$stripe_sk = $this->settings_library->get('stripe_sk', $this->cart_library->accountID);
		if (empty($stripe_pk) || empty($stripe_sk)) {
			$stripe_error = 'Please complete the information for Stripe in ' . anchor('settings/integrations', 'Settings > Integrations') . '.';
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'booking_info' => $booking_info,
			'booking_type' => $type,
			'bookingID' => $bookingID,
			'organisations' => $organisations,
			'contacts' => $contacts,
			'addresses' => $addresses,
			'brands' => $brands,
			'project_types' => $project_types,
			'project_codes' => $project_codes,
			'type' => $type,
			'add_contact' => $add_contact,
			'org_attachments' => $org_attachments,
			'org_attachments_array' => $org_attachments_array,
			'is_project' => $is_project,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'lesson_types' => $lesson_types,
			'prices_array' => $prices_array,
			'prices_contract_array' => $prices_contract_array,
			'tag_list' => $tag_list,
			'has_org_bookable_blocks' => $has_org_bookable_blocks,
			'breadcrumb_levels' => $breadcrumb_levels,
			'gc_error' => $gc_error,
			'stripe_error' => $stripe_error
		);

		// load view
		$this->crm_view('bookings/booking', $data);
	}

	/**
	 * delete a booking
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function remove($bookingID = NULL) {

		// check params
		if (empty($bookingID)) {
			show_404();
		}

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;

			// all ok, delete
			$query = $this->db->delete('bookings', $where);

			// work out name
			$name = 'Contract';
			if ($booking_info->project == 1) {
				$name = $booking_info->name;
			}

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $name . ' has been removed successfully.');
			} else {
				// try to force delete if required
				if ($this->crm_library->last_segment() === 'force') {
					force_delete_db_dependants('bookings', $bookingID);
					// redirect to normal delete to delete parent
					redirect(str_replace('/force', '', current_url()));
					exit();
				}
				// get dependant table conflicts
				if ($db_error = get_friendly_db_error('bookings', $bookingID, $booking_info, $name)) {
					$this->session->set_flashdata('error', $db_error);
				}
			}

			// determine which page to send the user back to
			$redirect_to = 'bookings';

			if ($booking_info->project == 1) {
				$redirect_to .= '/projects';
			} else if ($booking_info->type != 'booking') {
				$redirect_to .= '/' . $booking_info->type . 's';
			}

			redirect($redirect_to);
		}
	}

	/**
	 * confirm booking
	 * @param  int $bookingID
	 * @param string $value
	 * @return mixed
	 */
	public function confirm($bookingID = NULL, $value = NULL) {

		// check params
		if (empty($bookingID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$booking_info = $row;

			$data = array(
				'confirmed' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['confirmed'] = 1;
			}

			// run query
			$query = $this->db->update('bookings', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				if ($value == 'yes') {
					if ($booking_info->type == 'event') {
						echo 'OK';
					} else {
						$success = 'Booking confirmed successfully';
						$redirect_to = 'bookings/recall';
						if ($this->settings_library->get('send_new_booking') == 1) {
							$success .= ', check and send confirmation below.';
							$redirect_to = 'bookings/confirmation/' . $bookingID;
						}
						$this->session->set_flashdata('success', $success);
						echo site_url($redirect_to);
					}
				} else {
					echo 'OK';
				}
				exit();
			}
		}

	}

	/**
	 * jump to booking within list
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function jumpto($bookingID = NULL) {

		// check params
		if (empty($bookingID)) {
			show_404();
		}

		// look up
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('bookings')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $booking_info) {}

		// build search
		$search_fields = array(
			'start_from' => mysql_to_uk_date($booking_info->startDate),
			'start_to' => mysql_to_uk_date($booking_info->startDate),
			'end_from' => mysql_to_uk_date($booking_info->endDate),
			'end_to' => mysql_to_uk_date($booking_info->endDate),
			'org_id' => $booking_info->orgID,
			'search' => 'true'
		);

		// set search store
		$search_store = 'search-' . $booking_info->type;

		// determine page
		if ($booking_info->project == 1) {
			$redirect_to = 'bookings/projects';
			$search_store = 'search-projects';
		} else if ($booking_info->type == 'event') {
			$redirect_to = 'bookings/events';

		} else {
			$redirect_to = 'bookings';
			$search_store = 'search-' . $booking_info->type;
		}

		// store search fields
		$this->session->set_userdata($search_store, $search_fields);

		// go
		redirect($redirect_to . '/recall');
	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

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
	 * check a date is after start date
	 * @param  string $endDate
	 * @param  string $startDate
	 * @return boolean
	 */
	public function after_start($endDate, $startDate) {

		$startDate = strtotime(uk_to_mysql_date($startDate));
		$endDate = strtotime(uk_to_mysql_date($endDate));

		if ($endDate >= $startDate) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check if either this field filled in if value of another field is checked
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_checked($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 == 1 && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if either this field filled in if value of another field is not off
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function required_if_not_off($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// check
		if ($value2 != 'off' && empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if amount is not over 100 if using percentage
	 * @param  mixed $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function percentage_check($value, $value2) {

		// check type
		if ($value2 != 'percentage') {
			return TRUE;
		}

		// if over 100, error
		if ($value > 100) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * check if amount is more than 0
	 * @param  mixed $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function more_than_zero($value, $value2) {

		// check type
		if ($value2 == 'off') {
			return TRUE;
		}

		// if less than 0, error
		if ($value <= 0) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * jump to project type
	 * @param  int $typeID
	 * @return mixed
	 */
	public function projecttype($typeID = NULL) {

		// check params
		if (empty($typeID)) {
			show_404();
		}

		// build search
		$search_fields['project_type_id'] = intval($typeID);
		$search_fields['search'] = 'true';

		// store search fields
		$this->session->set_userdata('search-projects', $search_fields);

		// go to
		$redirect_to = 'bookings/projects/recall';

		redirect($redirect_to);
	}

	/**
	 * get default pricing
	 * @return mixed
	 */
	public function default_pricing() {

		$json = array(
			'result' => 'OK',
			'pricing' => array()
		);

		$orgID = $this->input->post('orgID');
		$brandID = $this->input->post('brandID');

		// check params
		if (empty($orgID) || empty($brandID)) {
			header("Content-type:application/json");
			$json['result'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}

		// get prices
		$where = array(
			'orgID' => $orgID,
			'brandID' => $brandID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_pricing')->where($where)->get();

		// no match
		if ($query->num_rows() == 0) {
			header("Content-type:application/json");
			$json['result'] = 'ERROR';
			echo json_encode($json);
			return TRUE;
		}

		foreach ($query->result() as $row) {
			$json['pricing'][$row->typeID] = array(
				'amount' => $row->amount,
				'contract' => $row->contract
			);
		}

		header("Content-type:application/json");
		echo json_encode($json);
		return TRUE;

	}

	/**
	 * duplicate a booking
	 * @param  int $bookingID
	 * @return mixed
	 */
	public function duplicate($bookingID = NULL) {

		// check params
		if (empty($bookingID)) {
			show_404();
		}

		// run query
		$where = array(
			'bookingID' => $bookingID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('bookings')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result_array() as $row) {
			$booking_info = $row;

			// copy block info
			$data = $booking_info;

			// update vars
			unset($data['bookingID']);
			// only append (copy) name if not empty
			if (!empty($data['name'])) {
				$data['name'] .= ' (Copy)';
			}
			$data['byID'] = $this->auth->user->staffID;
			$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
			$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

			$res = $this->db->insert('bookings', $data);

			if ($this->db->affected_rows() == 1) {

				$new_bookingID = $this->db->insert_id();

				// get blocks
				$where = array(
					'bookingID' => $bookingID,
					'accountID' => $this->auth->user->accountID
				);

				// run query
				$blocks = $this->db->from('bookings_blocks')->where($where)->get();

				if ($blocks->num_rows() > 0) {
					// match
					foreach ($blocks->result_array() as $block) {

						// copy block info
						$data = $block;

						// update vars
						unset($data['blockID']);
						$data['bookingID'] = $new_bookingID;
						$data['name'] .= ' (Copy)';
						$data['byID'] = $this->auth->user->staffID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						$res = $this->db->insert('bookings_blocks', $data);

						if ($this->db->affected_rows() == 1) {

							$new_blockID = $this->db->insert_id();

							// get lessons
							$lessons = array();

							$where = array(
								'blockID' => $block['blockID'],
								'accountID' => $this->auth->user->accountID
							);

							$res = $this->db->from('bookings_lessons')->where($where)->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $lesson_info) {
									$lessons[$lesson_info->lessonID] = $lesson_info;
								}
							}

							// duplicate sessions to new block
							$this->crm_library->duplicate_lessons($lessons, $new_blockID, $new_bookingID);
						}

						// calc targets
						$this->crm_library->calc_targets($new_blockID);
					}
				}

				// images
				$where = array(
					'bookingID' => $bookingID,
					'accountID' => $this->auth->user->accountID
				);
				// run query
				$images = $this->db->from('bookings_images')->where($where)->get();
				if ($images->num_rows() > 0) {
					// match
					foreach ($images->result_array() as $image) {

						// copy image info
						$data = $image;

						// update vars
						unset($data['imageID']);
						$data['bookingID'] = $new_bookingID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// copy file
						$data['path'] = $this->crm_library->duplicate_upload($data['path']);

						// insert
						$res = $this->db->insert('bookings_images', $data);
					}
				}

				// tags
				$where = array(
					'bookingID' => $bookingID,
					'accountID' => $this->auth->user->accountID
				);
				// run query
				$tags = $this->db->from('bookings_tags')->where($where)->get();
				if ($tags->num_rows() > 0) {
					// match
					foreach ($tags->result_array() as $tag) {

						// copy tag info
						$data = $tag;

						// update vars
						unset($data['linkID']);
						$data['bookingID'] = $new_bookingID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// insert
						$res = $this->db->insert('bookings_tags', $data);
					}
				}

				// pricing
				$where = array(
					'bookingID' => $bookingID,
					'accountID' => $this->auth->user->accountID
				);
				// run query
				$pricing = $this->db->from('bookings_pricing')->where($where)->get();
				if ($pricing->num_rows() > 0) {
					// match
					foreach ($pricing->result_array() as $price) {

						// copy price info
						$data = $price;

						// update vars
						unset($data['linkID']);
						$data['bookingID'] = $new_bookingID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// insert
						$res = $this->db->insert('bookings_pricing', $data);
					}
				}

				// org attachments
				$where = array(
					'bookingID' => $bookingID,
					'accountID' => $this->auth->user->accountID
				);
				// run query
				$org_attachments = $this->db->from('bookings_orgs_attachments')->where($where)->get();
				if ($org_attachments->num_rows() > 0) {
					// match
					foreach ($org_attachments->result_array() as $attachment) {

						// copy price info
						$data = $attachment;

						// update vars
						unset($data['actualID']);
						$data['bookingID'] = $new_bookingID;
						$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$data['modified'] = mdate('%Y-%m-%d %H:%i:%s');

						// insert
						$res = $this->db->insert('bookings_orgs_attachments', $data);
					}
				}

				if (empty($booking_info['name'])) {
					$booking_info['name'] = 'Booking';
				}
				$this->session->set_flashdata('success', $booking_info['name'] . ' has been duplicated successfully.');
				$redirect_to = 'bookings/edit/' . $new_bookingID;
			} else {
				if (empty($booking_info['name'])) {
					$booking_info['name'] = 'Booking';
				}
				$this->session->set_flashdata('error', $booking_info['name'] . ' could not be duplicated.');
				$redirect_to = 'bookings/';
			}

			redirect($redirect_to);
		}
	}

}

/* End of file main.php */
/* Location: ./application/controllers/bookings/main.php */
