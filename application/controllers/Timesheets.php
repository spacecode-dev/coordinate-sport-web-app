<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timesheets extends MY_Controller {

	private $allowed_departments = array(
		'directors',
		'management'
	);

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array(), array('timesheets'));
	}

	/**
	 * show list of timesheet dates
	 * @return void
	 */
	public function index($action = 'dates') {

		// only allow directors and management
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			// else default page shows own
			return $this->own();
		}

		// if viewing only own, stop and call another method
		if ($action == 'own') {
			return $this->own();
		}

		// set defaults
		$icon = 'clock';
		$current_page = 'timesheets';
		$section = 'timesheets';
		$page_base = 'finance/timesheets';
		$generate_url = 'timesheets/generate';
		$title = 'Timesheets';
		$buttons = '<a class="btn btn-success" href="' . site_url($generate_url) . '">Generate Timesheets</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'timesheets.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets'))) {

			foreach ($this->session->userdata('search-timesheets') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`date` <= " . $this->db->escape($date_to);
				}
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('timesheets.*, GROUP_CONCAT(' . $this->db->dbprefix('timesheets') . '.status SEPARATOR \',\') AS timesheet_statuses, COUNT(' . $this->db->dbprefix('timesheets') . '.`timesheetID`) AS `timesheet_count`')->from('timesheets')->where($where)->where($search_where, NULL, FALSE)->order_by('timesheets.date desc')->group_by('timesheets.date')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('timesheets.*, GROUP_CONCAT(' . $this->db->dbprefix('timesheets') . '.status SEPARATOR \',\') AS timesheet_statuses, COUNT(' . $this->db->dbprefix('timesheets') . '.`timesheetID`) AS `timesheet_count`')->from('timesheets')->where($where)->where($search_where, NULL, FALSE)->order_by('timesheets.date desc')->group_by('timesheets.date')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'timesheets' => $res,
			'generate_url' => $generate_url,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('timesheets/dates', $data);
	}

	/**
	 * show timesheets within a date
	 * @return void
	 */
	public function date($date) {
		

		// only allow directors and management
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// check date
		if (empty($date) || !check_mysql_date($date) || date('l', strtotime($date)) != 'Monday') {
			show_404();
		}

		// set defaults
		$icon = 'clock';
		$current_page = 'timesheets';
		$section = 'timesheets';
		$page_base = 'timesheets/date/' . $date;
		$title = 'Week Commencing ' . mysql_to_uk_date($date);
		$generate_url = 'timesheets/generate/' . $date;
		$return_to = 'finance/timesheets';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a> <a class="btn btn-success" href="' . site_url($generate_url) . '">Generate Timesheets</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'timesheets' => 'Timesheets'
		);

		// set where
		$where = array(
			'timesheets.accountID' => $this->auth->user->accountID,
			'timesheets.date' => $date
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'status' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['status'] = set_value('search_status');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets-date'))) {

			foreach ($this->session->userdata('search-timesheets-date') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets-date', $search_fields);

			if ($search_fields['staff_id'] > 0) {
				$search_where[] = $this->db->dbprefix('timesheets').".`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if (!empty($search_fields['status'])) {
				$search_where[] = $this->db->dbprefix('timesheets').".`status` = " . $this->db->escape($search_fields['status']);
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('timesheets.*, staff.first, staff.surname')->from('timesheets')->join('staff', 'timesheets.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('timesheets.*, staff.first, staff.surname, staff.mileage_activate_fuel_cards')->from('timesheets')->join('staff', 'timesheets.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();
		
		$mileageArray = array();
		$dateCount = array();
		$timesheetCount = array();
		$query = $this->db->select("timesheets_mileage.*")->from("timesheets_mileage")->join('timesheets', 'timesheets.timesheetID = timesheets_mileage.timesheetID')->where($where)->where($search_where, NULL, FALSE)->get();
	
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				if(!isset($mileageArray[$result->timesheetID])){
					$mileageArray[$result->timesheetID] = 0;
				}
				$mileageArray[$result->timesheetID] += $result->total_mileage;
				if($result->total_mileage != 0)
					$dateCount[$result->timesheetID][] = $result->date;
			}
		}
		
		// Check Exclude Mileage Define
		$where_in = array(
			"excluded_mileage",
			"excluded_mileage_without_fuel_card"
		);
		$where = array("accountID" => $this->auth->user->accountID);
		$exclude_mileage = $excluded_mileage_without_fuel_card = 0;
		$exclude_m = $this->db->select("*")->from("accounts_settings")->where($where)->where_in("key", $where_in)->get();
		foreach($exclude_m->result() as $result){
			if($result->value != NULL && $result->value != "" && $result->key == "excluded_mileage")
				$exclude_mileage = $result->value;
			if($result->value != NULL && $result->value != "" && $result->key == "excluded_mileage_without_fuel_card")
				$excluded_mileage_without_fuel_card = $result->value;
		}
		
		//Check Mileage Section in accounts
		$mileage_section = $this->auth->has_features("mileage");
		

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'timesheets' => $res,
			'generate_url' => $generate_url,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'staff_list' => $staff_list,
			'mileage_section' => $mileage_section,
			'exclude_mileage' => $exclude_mileage,
			'excluded_mileage_without_fuel_card' => $excluded_mileage_without_fuel_card,
			'dateCount' => $dateCount,
			'mileageArray' => $mileageArray
		);

		// load view
		$this->crm_view('timesheets/timesheets', $data);
	}

	/**
	 * generate timesheets
	 * @return void
	 */
	public function generate($date = NULL) {

		// only allow directors and management
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// set default date if none
		if (!empty($date) && check_mysql_date($date) && date('l', strtotime($date)) == 'Monday') {
			$return_to = 'timesheets/date/' . $date;
			$date = mysql_to_uk_date($date);
		} else {
			$date = date('d/m/Y', strtotime(date('Y') . 'W' . str_pad(date('W'), 2, '0', STR_PAD_LEFT)));
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Generate Timesheets';
		$submit_to = 'timesheets/generate';
		if (!isset($return_to)) {
			$return_to = 'finance/timesheets';
		}
		$icon = 'clock';
		$current_page = 'timesheets';
		$section = 'timesheets';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$success = NULL;
		$info = NULL;
		$errors = array();
		$breadcrumb_levels = array(
			'timesheets' => 'Timesheets'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date|callback_check_monday');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok
				$date = set_value('date');

				// create timesheets
				$timesheets_created = $this->crm_library->generate_timesheets(uk_to_mysql_date($date));
				
				if ($timesheets_created == 0) {
					// none created
					$info = 'All possible timesheets for this date have already been generated.';
				} else {
					$message = $timesheets_created . ' timesheet';
					if ($timesheets_created > 1) {
						$message .= 's have';
					}  else {
						$message .= ' has';
					}
					$message .= ' been generated successfully';
					$this->session->set_flashdata('success', $message);
					redirect('timesheets/date/' . uk_to_mysql_date($date));
					return TRUE;
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'date' => $date,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('timesheets/generate', $data);
	}

	/**
	 * show timesheets for logged in user
	 * @return void
	 */
	public function own() {

		// set defaults
		$icon = 'clock';
		$current_page = 'timesheets_own';
		$section = 'timesheets';
		$page_base = 'finance/timesheets/own';
		$title = 'Your Timesheets';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'timesheets.accountID' => $this->auth->user->accountID,
			'timesheets.staffID' => $this->auth->user->staffID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'status' => NULL,
			'search' => NULL
		);

		// if invoicing
		if ($this->input->post('invoice') && $this->auth->has_features('staff_invoices')) {
			$timesheets_posted = $this->input->post('timesheets');
			if (is_array($timesheets_posted) && count($timesheets_posted) > 0) {

				// check if timesheets are uninvoiced and have items
				if ($this->check_timesheets_uninvoiced($timesheets_posted)) {

					// get next invoice number
					$next_invoice_no = 1;
					$where = array(
						'accountID' => $this->auth->user->accountID
					);
					$res = $this->db->select('number')->from('staff_invoices')->where($where)->order_by('number desc')->limit(1)->get();
					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							$next_invoice_no = $row->number+1;
						}
					}

					// create invoice
					$invoice_data = array(
						'accountID' => $this->auth->user->accountID,
						'staffID' => $this->auth->user->staffID,
						'sent' => 0,
						'number' => $next_invoice_no,
						'date' => mdate('%Y-%m-%d'),
						'subject' => str_replace('{staff_name}', $this->auth->user->first . ' ' . $this->auth->user->surname, $this->settings_library->get('staff_invoice_default_subject')),
						'buyer_id' => $this->settings_library->get('staff_invoice_default_buyer'),
						'utr' => '',
						'bank_name' => $this->auth->user->payments_bankName,
						'bank_account' => $this->auth->user->payments_accountNumber,
						'bank_sort_code' => $this->auth->user->payments_sortCode,
						'amount' => 0,
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$this->db->insert('staff_invoices', $invoice_data);

					$invoiceID = $this->db->insert_id();

					// mark timesheets as invoiced
					foreach ($timesheets_posted as $timesheetID) {
						$data = array(
							'invoiced' => 1
						);
						$where = array(
							'timesheetID' => $timesheetID,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->update('timesheets', $data, $where, 1);

						// insert
						$data = array(
							'accountID' => $this->auth->user->accountID,
							'timesheetID' => $timesheetID,
							'invoiceID' => $invoiceID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s')
						);
						$this->db->insert('staff_invoices_timesheets', $data);
					}

					// tell user
					$this->session->set_flashdata('success', 'Invoice created successfully');

					// redirect to invoice
					redirect('timesheets/invoice/' . $invoiceID);
				} else {
					$error = 'There is nothing within the selected timesheet(s) to invoice';
				}
			} else {
				$error = 'Please select at least one approved and uninvoiced timesheet';
			}
		}

		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_status', 'Status', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['status'] = set_value('search_status');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets-own'))) {

			foreach ($this->session->userdata('search-timesheets-own') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets-own', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`date` <= " . $this->db->escape($date_to);
				}
			}

			if (!empty($search_fields['status'])) {
				$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`status` = " . $this->db->escape($search_fields['status']);
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('timesheets.*')->from('timesheets')->where($where)->where($search_where, NULL, FALSE)->order_by('date desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('timesheets.*')->from('timesheets')->where($where)->where($search_where, NULL, FALSE)->order_by('date desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'timesheets' => $res,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('timesheets/own', $data);
	}

	/**
	 * view/edit a timesheet
	 * @param  int $timesheetID
	 * @return void
	 */
	public function view($timesheetID)
	{

		// assume readonly
		$mode = 'readonly';

		// check if numeric
		if (!ctype_digit($timesheetID)) {
			show_404();
		}
		// if so, check user exists
		$where = array(
			'timesheets.timesheetID' => $timesheetID,
			'timesheets.accountID' => $this->auth->user->accountID
		);

		// if not in allowed departments, limit to own timesheet only
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			$where['timesheets.staffID'] = $this->auth->user->staffID;
		}

		// run query
		$query = $this->db->select('timesheets.*, staff.first as staff_first, staff.surname as staff_last, staff_addresses.postcode as staff_postcode, staff.mileage_activate_fuel_cards, staff.activate_mileage')->from('timesheets')->join('staff', 'timesheets.staffID = staff.staffID', 'inner')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND type = "main"', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$timesheet_info = $row;
		}

		// if not submitted or in allowed departments, allow edit
		if ($timesheet_info->status == 'unsubmitted' || in_array($this->auth->user->department, $this->allowed_departments)) {
			$mode = 'edit';
		}
		
		// Mileage Section activate? 
		$mileage_section = $this->auth->has_features('mileage');

		// get timesheet items
		$mileage_activate_fuel_cards = $timesheet_info->mileage_activate_fuel_cards;
		$where = array(
			'timesheets_items.timesheetID' => $timesheetID,
			'timesheets_items.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('timesheets_items.*, staff.first as approver_first, staff.surname as approver_last, orgs.name as venue, brands.name as brand, brands.colour as brand_colour, booking_address.postcode as booking_postcode, lesson_address.postcode as lesson_postcode, main_address.postcode as main_postcode, bookings.type as booking_type, activities.name as activity, staff.mileage_activate_fuel_cards')
		->from('timesheets_items')
		->join('staff', 'timesheets_items.approverID = staff.staffID', 'left')
		->join('brands', 'timesheets_items.brandID = brands.brandID', 'left')
		->join('activities', 'timesheets_items.activityID = activities.activityID', 'left')
		->join('orgs', 'timesheets_items.orgID = orgs.orgID', 'left')
		->join('bookings_lessons', 'timesheets_items.lessonID = bookings_lessons.lessonID', 'left')
		->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'left')
		->join('orgs_addresses AS booking_address', 'bookings_lessons.addressID = booking_address.addressID', 'left')
		->join('orgs_addresses as lesson_address', 'bookings_lessons.addressID = lesson_address.addressID', 'left')
		->join('orgs_addresses as main_address', 'orgs.orgID = main_address.orgID AND main_address.type = "main"', 'left')
		->where($where)
		->order_by('timesheets_items.date asc, timesheets_items.start_time asc, timesheets_items.end_time asc')
		->get();
		$timesheet_items = array();
	
		if ($res->num_rows() > 0) {
			foreach ($res->result_array() as $row) {
				$timesheet_items[$row['itemID']] = $row;
			}
		}
		
		// get mileage data
		$timesheet_mileage = array();
		// get timesheet items
		$where = array(
			'timesheets_mileage.timesheetID' => $timesheetID,
			'timesheets_mileage.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select("timesheets_mileage.*, staff.first as approver_first, staff.surname as approver_last")->from("timesheets_mileage")
		->join('staff', 'timesheets_mileage.approverID = staff.staffID', 'left')
		->where($where)
		->order_by("timesheets_mileage.date")
		->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result_array() as $row) {
				$timesheet_mileage[$row['itemID']] = $row;
			}
		}
		
		//get Fuel Card Mileage
		$timesheets_fuel_card = array();
		$where = array(
			'timesheets_fuel_card.timesheetID' => $timesheetID,
			'timesheets_fuel_card.accountID' => $this->auth->user->accountID
		);
		
		$res = $this->db->select("timesheets_fuel_card.*, staff.first as approver_first, staff.surname as approver_last, staff.mileage_activate_fuel_cards")->from("timesheets_fuel_card")
		->join('staff', 'timesheets_fuel_card.approverID = staff.staffID', 'left')
		->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result_array() as $row) {
				$timesheets_fuel_card[$row['id']] = $row;
			}
		}

		// get timesheet expenses
		$where = array(
			'timesheets_expenses.timesheetID' => $timesheetID,
			'timesheets_expenses.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('timesheets_expenses.*, staff.first as approver_first, staff.surname as approver_last, orgs.name as venue, brands.name as brand, brands.colour as brand_colour')
		->from('timesheets_expenses')
		->join('staff', 'timesheets_expenses.approverID = staff.staffID', 'left')
		->join('brands', 'timesheets_expenses.brandID = brands.brandID', 'left')
		->join('orgs', 'timesheets_expenses.orgID = orgs.orgID', 'left')
		->where($where)
		->order_by('timesheets_expenses.date asc, timesheets_expenses.item asc')
		->get();
		$timesheet_expenses = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result_array() as $row) {
				$timesheet_expenses[$row['expenseID']] = $row;
			}
		}
		
		// Mileage Settings
		$where = array("accountID" => $this->auth->user->accountID);
		$mileage_setting = array();
		$res = $this->db->select("*")->from("accounts_settings")->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result_array() as $row) {
				$mileage_setting[$row['key']] = $row["value"];
			}
		}
		
		$postcode["work"] = isset($mileage_setting['mileage_default_postcode'])?$mileage_setting['mileage_default_postcode']:'';
		$postcode["staff"] = $timesheet_info->staff_postcode;
		$postcode["global"] = '';
		
		// Add address from orgs
		$where = array("accountID" => $this->auth->user->accountID);
		$org_address = array();
		$res = $this->db->select("*")->from("orgs_addresses")->where($where)->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$org_address[] = $row->postcode;
			}
		}
		$postcode["global"] = $org_address;
		
		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = $timesheet_info->staff_first . ' ' . $timesheet_info->staff_last;
		$submit_to = 'timesheets/view/' . $timesheetID;
		$return_to = 'timesheets/';
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			$return_to .= 'date/' . $timesheet_info->date;
		}
		$icon = 'clock';
		$breadcrumb_levels = array();
		if ($timesheet_info->staffID == $this->auth->user->staffID) {
			$current_page = 'own';
			$breadcrumb_levels['timesheets/own'] = 'Timesheets';
		} else {
			$current_page = 'timesheets';
			$breadcrumb_levels['timesheets'] = 'Timesheets';
			$breadcrumb_levels['timesheets/date/' . $timesheet_info->date] = 'Week Commencing ' . mysql_to_uk_date($timesheet_info->date);
		}
		$section = 'timesheets';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$new_items = array();
		$new_mileage = array();
		$new_expenses = array();
		
		$query = $this->db->from("mileage")->where("accountID", $this->auth->user->accountID)->get();
		$default_mode = array();
		$mode_price = array();
		foreach($query->result() as $result){
			$default_mode[$result->mileageID] = $result->name;
			$mode_price[$result->mileageID] = $result->rate;
		}

		// if posted and mode is edit
		if ($this->input->post() && $mode == 'edit') {

			// set validation rules
			$this->form_validation->set_rules('edited_items', 'Edited Items', 'callback_check_edited_items');
			$this->form_validation->set_rules('new_items', 'New Items', 'callback_check_new_items');
			$this->form_validation->set_rules('deleted_items', 'Deleted Items');
			
			if($mileage_section == 1 && $timesheet_info->activate_mileage == 1){
				$this->form_validation->set_rules('new_mileage', 'New Mileage', 'callback_check_new_mileage');
				$this->form_validation->set_rules('edited_mileage', 'Edited Mileage', 'callback_check_edited_mileage');
				$this->form_validation->set_rules('deleted_mileage', 'Deleted Mileage');
				if(array_key_exists("mileage_activate_fuel_cards", $mileage_setting) && $mileage_setting["mileage_activate_fuel_cards"] == 1){
					$this->form_validation->set_rules('edited_fuel_card', 'Edited Fuel Card', 'callback_check_edited_fuel_card');
				}
			}
			
			if ($this->auth->has_features('expenses')) {
				$this->form_validation->set_rules('edited_expenses', 'Edited Expensess', 'callback_check_edited_expenses');
				$this->form_validation->set_rules('new_expenses', 'New Expenses', 'callback_check_new_expenses');
				$this->form_validation->set_rules('deleted_expenses', 'Deleted Expenses');
			}
			
			

			// process deletions
			$deleted_items = explode(",", $this->input->post('deleted_items'));
			if (is_array($deleted_items)) {
				$deleted_items = array_filter($deleted_items);
				if (count($deleted_items) > 0) {
					foreach ($deleted_items as $deleted_item) {
						// check is a user item and not from timeable
						if (array_key_exists($deleted_item, $timesheet_items) && empty($timesheet_items[$deleted_item]['lessonID'])) {
							unset($timesheet_items[$deleted_item]);
						} else {
							// not valid, remove
							unset($deleted_items[$deleted_item]);
						}
					}
				}
			}
			
			//check mileage section active
			if($mileage_section == 1 && $timesheet_info->activate_mileage == 1){
				$deleted_mileage = explode(",", $this->input->post('deleted_mileage'));
				if (is_array($deleted_mileage)) {
					$deleted_mileage = array_filter($deleted_mileage);
					if (count($deleted_mileage) > 0) {
						foreach ($deleted_mileage as $deleted_mileages) {
							// check is a user item and not from timeable
							if (array_key_exists($deleted_mileages, $timesheet_mileage) && empty($timesheet_mileage[$deleted_mileages]['lessonID'])) {
								unset($timesheet_mileage[$deleted_mileages]);
							} else {
								// not valid, remove
								unset($deleted_mileage[$deleted_mileages]);
							}
						}
					}
				}
			}
			
			if ($this->auth->has_features('expenses')) {
				$deleted_expenses = explode(",", $this->input->post('deleted_expenses'));
				if (is_array($deleted_expenses)) {
					$deleted_expenses = array_filter($deleted_expenses);
					if (count($deleted_expenses) > 0) {
						foreach ($deleted_expenses as $deleted_expense) {
							// check exists
							if (array_key_exists($deleted_expense, $timesheet_expenses)) {
								unset($timesheet_expenses[$deleted_expense]);
							} else {
								// not valid, remove
								unset($deleted_expenses[$deleted_expense]);
							}
						}
					}
				}
			}

			// pass to view
			if (is_array($this->input->post('new_items'))) {
				$new_items = $this->input->post('new_items');
			}
			if (is_array($this->input->post('new_mileage'))) {
				$new_mileage = $this->input->post('new_mileage');
			}
			if ($this->auth->has_features('expenses') && is_array($this->input->post('new_expenses'))) {
				$new_expenses = $this->input->post('new_expenses');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// process deletions
				if (is_array($deleted_items) && count($deleted_items) > 0) {
					foreach ($deleted_items as $deleted_item) {
						$where = array(
							'itemID' => $deleted_item,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->delete('timesheets_items', $where, 1);
					}
				}
				if($mileage_section == 1 && $timesheet_info->activate_mileage == 1){
					if (is_array($deleted_mileage) && count($deleted_mileage) > 0) {
						foreach ($deleted_mileage as $deleted_mileages) {
							$where = array(
								'itemID' => $deleted_mileages,
								'accountID' => $this->auth->user->accountID
							);
							$this->db->delete('timesheets_mileage', $where, 1);
						}
					}
				}
				
				if ($this->auth->has_features('expenses') && is_array($deleted_expenses) && count($deleted_expenses) > 0) {
					foreach ($deleted_expenses as $deleted_expense) {
						$where = array(
							'expenseID' => $deleted_expense,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->delete('timesheets_expenses', $where, 1);
					}
				}

				$total_time = 0;
				$total_expenses = 0;
				$items_to_approve = 0;

				$submit_status = 'submitted';

				if ($this->input->post('save')) {
					$submit_status = 'unsubmitted';
				}
				// process edited items
				$edited_items = $this->input->post('edited_items');
				if (count($timesheet_items) > 0) {
					foreach ($timesheet_items as $item_id => $item) {
						$where = array(
							'itemID' => $item_id,
							'accountID' => $this->auth->user->accountID
						);
						if (array_key_exists($item_id, $edited_items) && isset($edited_items[$item_id]['edited']) && $edited_items[$item_id]['edited'] == 1) {
							// work out times
							$start_time = $edited_items[$item_id]['start_time']['h'] . ':' . $edited_items[$item_id]['start_time']['m'];
							$end_time = $edited_items[$item_id]['end_time']['h'] . ':' . $edited_items[$item_id]['end_time']['m'];
							// work out item time
							$item_time = strtotime($end_time) - strtotime($start_time) + time_to_seconds($item['extra_time']);
							// add to total
							$total_time += $item_time;
							// prepare data
							$data = array(
								'start_time' => $start_time,
								'end_time' => $end_time,
								'total_time' => seconds_to_time($item_time),
								'approverID' => $edited_items[$item_id]['approverID'],
								'reason' => $edited_items[$item_id]['reason'],
								'reason_desc' => $edited_items[$item_id]['reason_desc'],
								'status' => 'submitted',
								'edited' => 1,
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							// if not editing lesson, update other fields
							if (empty($item['lessonID'])) {
								if (isset($edited_items[$item_id]['date'])) {
									$data['date'] = $edited_items[$item_id]['date'];
								}
								if (isset($edited_items[$item_id]['orgID'])) {
									$data['orgID'] = $edited_items[$item_id]['orgID'];
								}
								if (isset($edited_items[$item_id]['brandID'])) {
									$data['brandID'] = $edited_items[$item_id]['brandID'];
								}
								if (isset($edited_items[$item_id]['activityID']) && !empty($edited_items[$item_id]['activityID'])) {
									$data['activityID'] = $edited_items[$item_id]['activityID'];
								} else {
									$data['activityID'] = NULL;
								}
							}
							// if original times empty, set if not an actual lesson
							if (empty($item['original_start_time']) && empty($item['original_end_time']) && !empty($item['lessonID']))  {
								$data['original_start_time'] = $item['start_time'];
								$data['original_end_time'] = $item['end_time'];
							}
							// log as item to approve
							$items_to_approve++;
						} else {
							if ($item['status'] != 'declined') {
								$total_time += (strtotime('2000-01-01 ' . $item['total_time']) - strtotime('2000-01-01 00:00'));
							}
							// auto approve as not edited
							$data = array(
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							// if not already edited and related to lesson, mark approved
							if ($item['edited'] != 1 && $submit_status == 'submitted' && ($item['approverID'] == NULL || ($item['approverID'] != NULL && $item['status'] == 'approved'))) {
								$data['status'] = 'approved';
							} else if ($item['status'] == 'submitted' || $item['status'] == 'unsubmitted') {
								$data['status'] = $submit_status;
								$items_to_approve++;
							}
						}
						// if only saving, don't update status
						if ($submit_status == 'unsubmitted' && array_key_exists('status', $data)) {
							unset($data['status']);
						}
						$this->db->update('timesheets_items', $data, $where, 1);
					}
				}
				// process new items
				if (count($new_items) > 0) {
					foreach ($new_items as $item) {
						// if all empty, skip
						if (empty($item['date']) && empty($item['orgID']) && empty($item['brandID']) && empty($item['activityID']) && empty($item['approverID']) && empty($item['reason']) && empty($item['reason_desc'])) {
							continue;
						}
						// work out times
						$start_time = $item['start_time']['h'] . ':' . $item['start_time']['m'];
						$end_time = $item['end_time']['h'] . ':' . $item['end_time']['m'];
						// work out item time
						$item_time = strtotime($end_time) - strtotime($start_time);
						// add to total
						$total_time += $item_time;
						// prepare data
						$data = array(
							'accountID' => $this->auth->user->accountID,
							'timesheetID' => $timesheetID,
							'orgID' => $item['orgID'],
							'brandID' => $item['brandID'],
							'activityID' => NULL,
							'lessonID' => NULL,
							'date' => $item['date'],
							'start_time' => $start_time,
							'end_time' => $end_time,
							'total_time' => seconds_to_time($item_time),
							'status' => $submit_status,
							'reason' => $item['reason'],
							'reason_desc' => $item['reason_desc'],
							'approverID' => $item['approverID'],
							'created' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s')
						);
						if (!empty($item['activityID'])) {
							$data['activityID'] = $item['activityID'];
						}
						// insert
						$this->db->insert('timesheets_items', $data);
						// log as item to approve
						$items_to_approve++;
					}
				}
				
				
				// process edited mileage
				if($mileage_section == 1 && $timesheet_info->activate_mileage == 1){
					$edited_mileage = $this->input->post('edited_mileage');
					if (count($timesheet_mileage) > 0) {
						foreach ($timesheet_mileage as $item_id => $item) {					
							$where = array(
								'itemID' => $item_id,
								'accountID' => $this->auth->user->accountID
							);
							if (array_key_exists($item_id, $edited_mileage) && isset($edited_mileage[$item_id]['edited']) && $edited_mileage[$item_id]['edited'] == 1) {
								
								
								// prepare data
								$data = array(
									'approverID' => $edited_mileage[$item_id]['approverID'],
									'reason' => $edited_mileage[$item_id]['reason'],
									'reason_desc' => $edited_mileage[$item_id]['reason_desc'],
									'status' => 'submitted',
									'edited' => 1,
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								
								if (isset($edited_mileage[$item_id]['date'])) {
									$data['date'] = uk_to_mysql_date($edited_mileage[$item_id]['date']);
								}
								if (isset($edited_mileage[$item_id]['start_location'])) {
									$data['start_location'] = $edited_mileage[$item_id]['start_location'];
								}
								if (isset($edited_mileage[$item_id]['session_location'])) {
									$data['session_location'] = $edited_mileage[$item_id]['session_location'];
								}
								if (isset($edited_mileage[$item_id]['via_location'])) {
									$data['via_location'] = $edited_mileage[$item_id]['via_location'];
								}
								if (isset($edited_mileage[$item_id]['mode'])) {
									$data['mode'] = $edited_mileage[$item_id]['mode'];
								}
								
								if (isset($edited_mileage[$item_id]['date']) || isset($edited_mileage[$item_id]['start_location']) || isset($edited_mileage[$item_id]['session_location']) || isset($edited_mileage[$item_id]['via_location']) || isset($edited_mileage[$item_id]['mode'])) {
									$total_cost = $total_mileage = 0;
									if(!empty($data['start_location']) && !empty($data['session_location'])){
										if($data['via_location'] != "" && $data['via_location'] != null){
											$origin[0] = $data['start_location'];
											$destination[0] = $data['via_location'];
											$origin[1] = $data['via_location'];
											$destination[1] = $data['session_location'];
										}else{
											$origin[0] = $data['start_location'];
											$destination[0] = $data['session_location'];
										}
										for($x=0; $x<count($origin); $x++){
											if($origin[$x] != $destination[$x]){
												$param = geocode_mileage($origin[$x], $destination[$x]);
												if($param->status == 'OK'){
													if(isset($param->rows[0]->elements[0]->distance->text)){
														$total_mileage += TRIM($param->rows[0]->elements[0]->distance->text," km")/1.6;
													}
												}  
											}  
										}
										$total_cost = ($total_mileage * $mode_price[$data['mode']])/100;
									}
									$data["total_cost"] = $total_cost;
									$data["total_mileage"] = $total_mileage;
								}
								
								$items_to_approve++;
							} else {
								$data = array(
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								// if not already edited and related to lesson, mark approved
								if ($item['edited'] != 1 && !empty($item['lessonID'])) {
									$data['status'] = 'approved';
								} else if ($item['status'] == 'submitted') {
									$items_to_approve++;
								}
							}
							// if only saving, don't update status
							if ($submit_status == 'unsubmitted' && array_key_exists('status', $data)) {
								unset($data['status']);
							}
							$this->db->update('timesheets_mileage', $data, $where, 1);
						}
					}
					// process new Mileage
					if (count($new_mileage) > 0) {
						$this->load->helper('crm_helper');
						foreach ($new_mileage as $mileage) {
							// if all empty, skip
							if (empty($mileage['date']) && empty($mileage['start_location']) && empty($mileage['session_location']) && empty($mileage['mode']) && empty($mileage['approverID']) && empty($mileage['reason']) && empty($mileage['reason_desc'])) {
								continue;
							}
							$total_cost = $total_mileage = 0;
							if(!empty($mileage['start_location']) && !empty($mileage['session_location'])){
								if($mileage['via_location'] != "" && $mileage['via_location'] != null){
									$origin[0] = $mileage['start_location'];
									$destination[0] = $mileage['via_location'];
									$origin[1] = $mileage['via_location'];
									$destination[1] = $mileage['session_location'];
								}else{
									$origin[0] = $mileage['start_location'];
									$destination[0] = $mileage['session_location'];
								}
							
								for($x=0; $x<count($origin); $x++){
									if($origin[$x] != $destination[$x]){
										$param = geocode_mileage($origin[$x], $destination[$x]);
										if($param->status == 'OK'){
											if(isset($param->rows[0]->elements[0]->distance->text)){
												$total_mileage += TRIM($param->rows[0]->elements[0]->distance->text," km")/1.6;
											}
										}  
									}  
								}
								$total_cost = ($total_mileage * $mode_price[$mileage['mode']])/100;
							}
							
							// prepare data
							$data = array(
								'accountID' => $this->auth->user->accountID,
								'timesheetID' => $timesheetID,
								'lessonID' => NULL,
								'date' => uk_to_mysql_date($mileage['date']),
								'start_location' => $mileage['start_location'],
								'session_location' => $mileage['session_location'],
								'via_location' => $mileage['via_location'],
								'mode' => $mileage['mode'],
								'total_mileage' => $total_mileage,
								'total_cost' => $total_cost,			
								'status' => $submit_status,
								'reason' => $mileage['reason'],
								'reason_desc' => $mileage['reason_desc'],
								'approverID' => $mileage['approverID'],
								'created' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							
							// insert
							$this->db->insert('timesheets_mileage', $data);
							// log as item to approve
							$items_to_approve++;
						}
					}
				
					// process edited fuel card
					if(array_key_exists("mileage_activate_fuel_cards", $mileage_setting) && $mileage_setting["mileage_activate_fuel_cards"] == 1 && $timesheet_info->mileage_activate_fuel_cards == 1){
						$edited_fuel_card = $this->input->post('edited_fuel_card');
						if (count($timesheets_fuel_card) > 0) {
							$i = 0;
							foreach ($timesheets_fuel_card as $item_id => $item) {
								$where = array(
									'id' => $item_id,
									'accountID' => $this->auth->user->accountID
								);
								if (array_key_exists($item_id, $edited_fuel_card) && isset($edited_fuel_card[$item_id]['edited']) && $edited_fuel_card[$item_id]['edited'] == 1) {
									
									// prepare data
									$data = array(
										'approverID' => $edited_fuel_card[$item_id]['approverID'],
										'reason' => $edited_fuel_card[$item_id]['reason'],
										'reason_desc' => $edited_fuel_card[$item_id]['reason_desc'],
										'end_mileage' => $edited_fuel_card[$item_id]['end_mileage'],
										'status' => 'submitted',
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									if(isset($edited_fuel_card[$item_id]['start_mileage'])){
										$data['start_mileage'] = $edited_fuel_card[$item_id]['start_mileage'];
									}
									
									// upload attachment
									if (isset($_FILES['edited_fuel_card']['name'][$i])) {
										$_FILES['receipt']['name']= $_FILES['edited_fuel_card']['name'][$i]['receipt'];
										$_FILES['receipt']['type']= $_FILES['edited_fuel_card']['type'][$i]['receipt'];
										$_FILES['receipt']['tmp_name']= $_FILES['edited_fuel_card']['tmp_name'][$i]['receipt'];
										$_FILES['receipt']['error']= $_FILES['edited_fuel_card']['error'][$i]['receipt'];
										$_FILES['receipt']['size']= $_FILES['edited_fuel_card']['size'][$i]['receipt'];

										$upload_res = $this->crm_library->handle_image_upload('receipt', FALSE, $this->auth->user->accountID, 1000, 1000);

										if ($upload_res !== NULL) {
											$data['receipt_name'] = $upload_res['client_name'];
											$data['receipt_path'] = $upload_res['raw_name'];
											$data['receipt_type'] = $upload_res['file_type'];
											$data['receipt_size'] = $upload_res['file_size']*1024;
											$data['receipt_ext'] = substr($upload_res['file_ext'], 1);
										}
									}
									
									//Auto approve Functionality
									if(isset($mileage_setting['automatically_approve_fuel_card']) && $mileage_setting['automatically_approve_fuel_card'] == 1){
										$data['status'] = 'approved';
									}else{
										$items_to_approve++;
									}
								} else {
									// auto approve as not edited
									if($edited_fuel_card[$item_id]['end_mileage'] == 0){
										$items_to_approve++;
										$submit_status = 'unsubmitted';
									}
									$data['status'] = 'approved';
									$data = array(
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);
									// if not already edited and related to lesson, mark approved
									if ($item['status'] == 'submitted') {
										$items_to_approve++;
									}
								}
								// if only saving, don't update status
								if ($submit_status == 'unsubmitted' && array_key_exists('status', $data)) {
									unset($data['status']);
								}
		
								$this->db->update('timesheets_fuel_card', $data, $where, 1);
								
							}
						}else{
							$data = array(
								'approverID' => $edited_fuel_card[0]['approverID'],
								'reason' => $edited_fuel_card[0]['reason'],
								'reason_desc' => $edited_fuel_card[0]['reason_desc'],
								'end_mileage' => $edited_fuel_card[0]['end_mileage'],
								'status' => 'submitted',
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);
							if(isset($edited_fuel_card[0]['start_mileage'])){
								$data['start_mileage'] = $edited_fuel_card[0]['start_mileage'];
							}
							$data["timesheetID"] = $timesheetID;
							$data["accountID"] = $this->auth->user->accountID;
							$data["created"] = mdate('%Y-%m-%d %H:%i:%s');
							//Auto approve Functionality
							if(isset($mileage_setting['automatically_approve_fuel_card']) && $mileage_setting['automatically_approve_fuel_card'] == 1){
								$data['status'] = 'approved';
							}else{
								$items_to_approve++;
							}
							$this->db->insert('timesheets_fuel_card', $data);
						}
					}
				}
			
				if ($this->auth->has_features('expenses')) {
					// process edited expenses
					$edited_expenses = $this->input->post('edited_expenses');
					if (count($timesheet_expenses) > 0) {
						foreach ($timesheet_expenses as $item_id => $item) {
							$where = array(
								'expenseID' => $item_id,
								'accountID' => $this->auth->user->accountID
							);
							if (array_key_exists($item_id, $edited_expenses) && isset($edited_expenses[$item_id]['edited']) && $edited_expenses[$item_id]['edited'] == 1) {
								// add to total
								$total_expenses += floatval($edited_expenses[$item_id]['amount']);
								// prepare data
								$data = array(
									'date' => $edited_expenses[$item_id]['date'],
									'orgID' => $edited_expenses[$item_id]['orgID'],
									'brandID' => $edited_expenses[$item_id]['brandID'],
									'item' => $edited_expenses[$item_id]['item'],
									'amount' => floatval($edited_expenses[$item_id]['amount']),
									'approverID' => $edited_expenses[$item_id]['approverID'],
									'reason' => $edited_expenses[$item_id]['reason'],
									'reason_desc' => $edited_expenses[$item_id]['reason_desc'],
									'status' => 'submitted',
									'edited' => 1,
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								// log as item to approve
								$items_to_approve++;
							} else {
								if ($item['status'] != 'declined') {
									$total_expenses += floatval($item['amount']);
								}
								// auto approve as not edited
								$data = array(
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);
								// if not already edited, mark approved
								if ($item['edited'] != 1 && $submit_status == 'submitted' && ($item['approverID'] == NULL || ($item['approverID'] != NULL && $item['status'] == 'approved'))) {
									$data['status'] = 'approved';
								} else if ($item['status'] == 'submitted' || $item['status'] == 'unsubmitted') {
									$data['status'] = $submit_status;
									$items_to_approve++;
								}
							}
							// if only saving, don't update status
							if ($submit_status == 'unsubmitted' && array_key_exists('status', $data)) {
								unset($data['status']);
							}
							$this->db->update('timesheets_expenses', $data, $where, 1);
						}
					}

					// process new expenses
					if (count($new_expenses) > 0) {
						$i = 0;
						foreach ($new_expenses as $item) {
							// if all empty, skip
							if (empty($item['date']) && empty($item['orgID']) && empty($item['brandID']) && empty($item['item']) && empty($item['amount']) && empty($item['approverID']) && empty($item['reason']) && empty($item['reason_desc'])) {
								continue;
							}
							// add to total
							$total_expenses += floatval($item['amount']);
							// prepare data
							$data = array(
								'accountID' => $this->auth->user->accountID,
								'timesheetID' => $timesheetID,
								'orgID' => $item['orgID'],
								'brandID' => $item['brandID'],
								'date' => $item['date'],
								'item' => $item['item'],
								'amount' => floatval($item['amount']),
								'status' => $submit_status,
								'reason' => $item['reason'],
								'reason_desc' => $item['reason_desc'],
								'approverID' => $item['approverID'],
								'created' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);

							if (isset($_FILES['new_expenses']['name'][$i])) {
								$_FILES['receipt']['name']= $_FILES['new_expenses']['name'][$i]['receipt'];
								$_FILES['receipt']['type']= $_FILES['new_expenses']['type'][$i]['receipt'];
								$_FILES['receipt']['tmp_name']= $_FILES['new_expenses']['tmp_name'][$i]['receipt'];
								$_FILES['receipt']['error']= $_FILES['new_expenses']['error'][$i]['receipt'];
								$_FILES['receipt']['size']= $_FILES['new_expenses']['size'][$i]['receipt'];

								$upload_res = $this->crm_library->handle_image_upload('receipt', FALSE, $this->auth->user->accountID, 1000, 1000);

								if ($upload_res !== NULL) {
									$data['receipt_name'] = $upload_res['client_name'];
									$data['receipt_path'] = $upload_res['raw_name'];
									$data['receipt_type'] = $upload_res['file_type'];
									$data['receipt_size'] = $upload_res['file_size']*1024;
									$data['receipt_ext'] = substr($upload_res['file_ext'], 1);
								}
							}

							// insert
							$this->db->insert('timesheets_expenses', $data);
							// log as item to approve
							$items_to_approve++;
							$i++;
						}
					}
				}

				// prepare data
				$timesheet_data = array(
					'status' => $submit_status,
					'total_time' => seconds_to_time($total_time),
					'total_expenses' => $total_expenses,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if ($items_to_approve == 0 && $submit_status != 'unsubmitted') {
					$timesheet_data['status'] = 'approved';
				}

				// update timesheet
				$where = array(
					'timesheetID' => $timesheetID,
					'accountID' => $this->auth->user->accountID
				);
				$query = $this->db->update('timesheets', $timesheet_data, $where, 1);

				// if inserted/updated
				if ($this->db->affected_rows() == 1) {

					// determine message
					if (in_array($this->auth->user->department, $this->allowed_departments)) {
						$message = $timesheet_info->staff_first . ' ' . $timesheet_info->staff_last . "'s ";
					} else {
						$message = mysql_to_uk_date($timesheet_info->date);
					}
					$message .= ' Timesheet has been';
					if ($submit_status == 'unsubmitted') {
						$message .= ' saved';
					} else {
						$message .= ' submitted';
						if ($timesheet_data['status'] == 'approved') {
							$message .= ' and approved';
						}
					}
					$message .= ' successfully.';
					$this->session->set_flashdata('success', $message);
					redirect($return_to);

					return TRUE;
				} else {
					$this->session->set_flashdata('info', 'Error saving data, please try again.');
				}
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// list of approvers
		$where = array(
			'staff.active' => 1,
			'staff_recruitment_approvers.accountID' => $this->auth->user->accountID,
			'staff_recruitment_approvers.staffID' => $timesheet_info->staffID
		);
	
		if ($timesheet_info->staffID == $this->auth->user->staffID) {
			$where['staff_recruitment_approvers.approverID !='] = $this->auth->user->staffID;
		}
	
		$approvers = $this->db->select("staff.*")->from("staff_recruitment_approvers")
		->join("staff", "staff.staffID = staff_recruitment_approvers.approverID", "left")
		->where($where)
		->order_by('staff.first asc, staff.surname asc')->get();
		
		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// activities
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$activities = $this->db->from('activities')->where($where)->order_by('name asc')->get();

		// venues
		$where = array(
			'prospect !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$venues = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// map days to dates
		$day_map = array();
		$date_map = array();
		$date = $timesheet_info->date;
		$date_to = date('Y-m-d', strtotime('+6 days', strtotime($date)));
		while (strtotime($date) <= strtotime($date_to)) {
			$day = strtolower(date('l', strtotime($date)));
			$day_map[$day] = $date;
			$date_map[$date] = ucwords($day);
			$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
		}

		// if no new items, add one
		if ($mode == 'edit' && count($new_items) == 0) {
			$new_items[] = array();
		}
		
		// if no new mileage, add one
		if ($mode == 'edit' && count($new_mileage) == 0) {
			$new_mileage[] = array();
		}

		// if no new expenses, add one
		if ($mode == 'edit' && count($new_expenses) == 0) {
			$new_expenses[] = array();
		}
		
		
		$main_roles = $this->settings_library->get_staff_for_payroll();

		// list of roles
		$additional_roles = $this->settings_library->additonal_roles;

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'timesheet_info' => $timesheet_info,
			'timesheet_items' => $timesheet_items,
			'timesheet_expenses' => $timesheet_expenses,
			'timesheetID' => $timesheetID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'approvers' => $approvers,
			'brands' => $brands,
			'activities' => $activities,
			'venues' => $venues,
			'mode' => $mode,
			'day_map' => $day_map,
			'date_map' => $date_map,
			'new_items' => $new_items,
			'new_mileage' => $new_mileage,
			'new_expenses' => $new_expenses,
			'main_roles' => $main_roles,
			'timesheet_mileage' => $timesheet_mileage,
			'timesheets_fuel_card' => $timesheets_fuel_card,
			'default_mode' => $default_mode,
			'mode_price' => $mode_price,
			'additional_roles' => $additional_roles,
			'mileage_setting' => $mileage_setting,
			'mileage_activate_fuel_cards' => $mileage_activate_fuel_cards,
			'mileage_section' => $mileage_section,
			'postcode' => $postcode
		);

		// load view
		$this->crm_view('timesheets/timesheet', $data);
	}

	/**
	 * show list of invoices
	 * @return void
	 */
	public function invoices($show_all = TRUE) {

		if (!$this->auth->has_features('staff_invoices')) {
			show_404();
		}

		// only allow directors and management to view all
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			$show_all = FALSE;
		}

		if ($show_all === 'false') {
			$show_all = FALSE;
		}

		// set defaults
		$icon = 'sack-dollar';
		$current_page = 'invoices';
		$section = 'timesheets';
		$page_base = 'finance/invoices';
		$title = 'Invoices';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'timesheets' => 'Timesheets'
		);

		// set where
		$where = array(
			'staff_invoices.accountID' => $this->auth->user->accountID
		);
		if ($show_all != TRUE) {
			$where['staff_invoices.staffID'] = $this->auth->user->staffID;
			$title = 'Your Invoices';
			$current_page = 'invoices_own';
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets-invoices'))) {

			foreach ($this->session->userdata('search-timesheets-invoices') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets-invoices', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("staff_invoices") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("staff_invoices") . "`.`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("staff_invoices") . "`.`date` <= " . $this->db->escape($date_to);
				}
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// run query
		$res = $this->db->select('staff_invoices.*, staff.first, staff.surname')->from('staff_invoices')->join('staff', 'staff_invoices.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('staff_invoices.date desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		$res = $this->db->select('staff_invoices.*, staff.first, staff.surname')->from('staff_invoices')->join('staff', 'staff_invoices.staffID = staff.staffID', 'inner')->where($where)->where($search_where, NULL, FALSE)->order_by('staff_invoices.date desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

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
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'invoices' => $res,
			'staff_list' => $staff_list,
			'show_all' => $show_all,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('timesheets/invoices', $data);
	}

	/**
	 * view/edit an invoice
	 * @param  int $invoiceID
	 * @return void
	 */
	public function invoice($invoiceID, $pdf = NULL)
	{

		if (!$this->auth->has_features('staff_invoices')) {
			show_404();
		}

		// assume readonly
		$mode = 'readonly';

		// check if numeric
		if (!ctype_digit($invoiceID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staff_invoices.invoiceID' => $invoiceID,
			'staff_invoices.accountID' => $this->auth->user->accountID
		);

		// if not in allowed departments, limit to own timesheet only
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			$where['staff_invoices.staffID'] = $this->auth->user->staffID;
		}

		// run query
		$query = $this->db->select('staff_invoices.*, staff.first as staff_first, staff.surname as staff_last, staff.department, staff_addresses.postcode as staff_postcode, staff.payments_scale_head, staff.payments_scale_assist, staff.payments_bankName, staff.payments_sortCode, staff.payments_accountNumber')->from('staff_invoices')->join('staff', 'staff_invoices.staffID = staff.staffID', 'inner')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND type = "main"', 'left')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$invoice_info = $row;
		}

		$invoiceID = $invoice_info->invoiceID;

		// work out invoice rows
		$total = $invoice_info->amount;
		$invoice_rows = array();

		// refresh items, if not sent
		if ($invoice_info->sent != 1) {
			$res = $this->refresh_invoice_rows($invoiceID);
			if ($res !== FALSE) {
				$total = $res['total'];
			}
		}

		// get invoice items
		$where = array(
			'staff_invoices_items.invoiceID' => $invoiceID,
			'staff_invoices_items.accountID' => $this->auth->user->accountID,
			'timesheets_items.salaried !=' => 1,
		);
		$query = $this->db->select('staff_invoices_items.*, timesheets_items.date, timesheets_items.start_time, timesheets_items.end_time, timesheets_expenses.date as expense_date, staff.department')
			->from('staff_invoices_items')
			->join('timesheets_items', 'staff_invoices_items.itemID = timesheets_items.itemID', 'left')
			->join('timesheets_expenses', 'staff_invoices_items.expenseID = timesheets_expenses.expenseID', 'left')
			->join('timesheets', 'timesheets.timesheetID = timesheets_expenses.timesheetID', 'left')
			->join('staff', 'staff.staffID = timesheets.staffID', 'left')
			->where($where)
			->get();

		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$key = $row['rowID'];
				if (!empty($row['itemID'])) {
					$key = '0timesheet-' . $row['date'] . '-' . $row['start_time'] . '-' . $row['end_time'] . '-' . $row['itemID'];
				} else if (!empty($row['expenseID'])) {
					$key = '1expense-' . $row['expense_date'] . '-' . $row['expenseID'];
				}
				$invoice_rows[$key] = $row;
			}
			ksort($invoice_rows);
		}
		

		if (!empty($pdf)) {
			$pdf_output = $this->generate_invoice_pdf($invoiceID);
			header('Content-type: application/pdf');
			header('Content-Disposition: attachment; filename="Invoice ' . $invoice_info->number . '.pdf"');
			header("Content-Length: " . strlen($pdf_output));
			echo $pdf_output;
			exit();
		}

		// if not sent, allow edit
		if ($invoice_info->sent != 1) {
			$mode = 'edit';
		}

		$invoice_prefix = $this->settings_library->get('staff_invoice_prefix');

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Invoice: ' . $invoice_prefix . $invoice_info->number;
		$submit_to = 'timesheets/invoice/' . $invoiceID;
		$return_to = 'finance/approvals';
		$icon = 'sack-dollar';
		$current_page = 'invoices';
		if ($invoice_info->staffID == $this->auth->user->staffID) {
			$return_to = 'finance/invoices/own';
			$current_page = 'invoices_own';
		}
		$breadcrumb_levels = array(
			'timesheets' => 'Timesheets',
			$return_to => 'Invoices'
		);
		$section = 'timesheets';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>  <a class="btn btn-primary" href="' . site_url('timesheets/invoice/' . $invoiceID . '/pdf') . '"><i class="far fa-save"></i> Download PDF</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if making draft again (if allowed)
		if ($mode != 'edit' && !empty($invoiceID) && in_array($this->auth->user->department, $this->allowed_departments) && $this->input->post('revert_draft')) {
			// prepare data
			$data = array(
				'sent' => 0
			);
			$where = array(
				'invoiceID' => $invoiceID,
				'accountID' => $this->auth->user->accountID
			);
			$query = $this->db->update('staff_invoices', $data, $where, 1);

			$this->session->set_flashdata('success', 'Invoice has been marked as draft again.');
			redirect('timesheets/invoice/' . $invoiceID);

		}

		// if posted and mode is edit
		if ($this->input->post() && $mode == 'edit') {

			// set validation rules
			$this->form_validation->set_rules('number', 'Invoice Number', 'trim|required|is_numeric');
			$this->form_validation->set_rules('date', 'Invoice Date', 'trim|required|check_uk_date');
			$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
			$this->form_validation->set_rules('buyer_id', 'Buyer ID', 'trim');
			$this->form_validation->set_rules('utr', 'UTR', 'trim');
			$this->form_validation->set_rules('bank_name', 'Bank Name', 'trim|required');
			$this->form_validation->set_rules('bank_account', 'Account Number', 'trim|required');
			$this->form_validation->set_rules('bank_sort_code', 'Sort Code', 'trim|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				// prepare data
				$invoice_data = array(
					'sent' => 0,
					'number' => set_value('number'),
					'date' => uk_to_mysql_date(set_value('date')),
					'subject' => set_value('subject'),
					'buyer_id' => set_value('buyer_id'),
					'utr' => set_value('utr'),
					'bank_name' => set_value('bank_name'),
					'bank_account' => set_value('bank_account'),
					'bank_sort_code' => set_value('bank_sort_code'),
					'amount' => $total,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				if (!$this->input->post('save') && !empty($this->settings_library->get('staff_invoice_to'))) {
					$invoice_data['sent'] = 1;
					$invoice_data['sent_date'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// save
				$where = array(
					'invoiceID' => $invoiceID,
					'accountID' => $this->auth->user->accountID
				);
				$query = $this->db->update('staff_invoices', $invoice_data, $where, 1);

				if ($invoice_data['sent'] == 1 && $this->settings_library->get('send_staff_invoices') == 1 && !empty($this->settings_library->get('staff_invoice_to'))) {
					// send invoice
					$pdf_output = $this->generate_invoice_pdf($invoiceID);
					$file_name = 'Invoice ' . $invoice_prefix . $invoice_data['number'] . '.pdf';

					$subject = $this->settings_library->get('staff_invoice_subject');
					$message = $this->settings_library->get('staff_invoice_email');
					$attachments = array(
						array(
							'file_name' => $file_name,
							'file_contents' => $pdf_output,
							'file_type' => 'application/pdf'
						)
					);

					$smart_tags = array(
						'invoice_no' => $invoice_prefix . $invoice_data['number'],
						'staff_name' => $invoice_info->staff_first . ' ' . $invoice_info->staff_last
					);

					foreach ($smart_tags as $key => $value) {
						$subject = str_replace('<p>{' . $key . '}</p>', $value, $subject);
						$subject = str_replace('{' . $key . '}', $value, $subject);
						$message = str_replace('<p>{' . $key . '}</p>', $value, $message);
						$message = str_replace('{' . $key . '}', $value, $message);
					}

					$this->crm_library->send_email($this->settings_library->get('staff_invoice_to'), $subject, $message, $attachments, FALSE, $this->auth->user->accountID);

					// tell user
					$message = 'Invoice has been sent';
				} else {
					$message = 'Invoice draft has been saved';
					$return_to = 'timesheets/invoice/' . $invoiceID;
				}
				$message .= ' successfully';
				$this->session->set_flashdata('success', $message);
				redirect($return_to);

				return TRUE;
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'invoice_info' => $invoice_info,
			'invoice_rows' => $invoice_rows,
			'total' => $total,
			'invoiceID' => $invoiceID,
			'allowed_departments' => $this->allowed_departments,
			'invoice_prefix' => $invoice_prefix,
			'mode' => $mode,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('timesheets/invoice', $data);
	}

	/**
	 * remove an invoice
	 * @param  int $invoiceID
	 * @return mixed
	 */
	public function removeinvoice($invoiceID = NULL) {

		// check params
		if (empty($invoiceID)) {
			show_404();
		}

		$where = array(
			'invoiceID' => $invoiceID,
			'sent !=' => 1,
			'accountID' => $this->auth->user->accountID,
			'staffID' => $this->auth->user->staffID
		);

		// only allow directors and management to delete any
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			unset($where['staffID']);
		}

		// run query
		$query = $this->db->from('staff_invoices')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$invoice_info = $row;
			$timesheetIDs = array();

			// look up timesheets invoiced
			$where_invoiced = array(
				'invoiceID' => $invoiceID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('staff_invoices_timesheets')->where($where_invoiced)->get();

			// match
			if ($query->num_rows() > 0) {
				foreach ($query->result() as $row) {
					$timesheetIDs[] = $row->timesheetID;
				}
			}

			// all ok, delete
			$query = $this->db->delete('staff_invoices', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', 'Invoice ' . $invoice_info->number . ' has been removed successfully.');

				// set timesheets to uninvoiced
				if (count($timesheetIDs) > 0) {
					$where = array(
						'accountID' => $this->auth->user->accountID
					);
					$data = array(
						'invoiced' => 0
					);

					// run query
					$query = $this->db->where($where)->where_in('timesheetID', $timesheetIDs)->update('timesheets', $data);
				}

			} else {
				$this->session->set_flashdata('error', 'Invoice ' . $invoice_info->number . ' could not be removed.');
			}

			$redirect_to = 'finance/invoices';
			redirect($redirect_to);
		}
	}

	/**
	 * generate invoice pdf
	 * @param  int $invoiceID
	 * @return mixed
	 */
	private function generate_invoice_pdf($invoiceID) {
		// run query
		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID,
		);
		$query = $this->db->from('staff_invoices')->where($where)->limit(1)->get();

		if ($query->num_rows() == 0) {
			return FALSE;
		}

		foreach ($query->result() as $invoice_info) {}

		// get staff info
		$where = array(
			'staff.staffID' => $invoice_info->staffID,
			'staff.accountID' => $this->auth->user->accountID,
		);
		$query = $this->db->select('staff.*, staff_addresses.*')->from('staff')->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND ' . $this->db->dbprefix('staff_addresses') . '.type = "main"', 'left')->where($where)->group_by('staff.staffID')->limit(1)->get();

		if ($query->num_rows() == 0) {
			return FALSE;
		}

		foreach ($query->result() as $staff_info) {}

		$invoice_rows = array();

		// run query
		$where = array(
			'staff_invoices_items.invoiceID' => $invoiceID,
			'staff_invoices_items.accountID' => $this->auth->user->accountID,
		);
		$query = $this->db->select('staff_invoices_items.*, timesheets_items.date, timesheets_items.start_time, timesheets_items.end_time, timesheets_expenses.date as expense_date')
			->from('staff_invoices_items')
			->join('timesheets_items', 'staff_invoices_items.itemID = timesheets_items.itemID', 'left')
			->join('timesheets_expenses', 'staff_invoices_items.expenseID = timesheets_expenses.expenseID', 'left')
			->where($where)
			->get();
		if ($query->num_rows() > 0) {
			foreach ($query->result_array() as $row) {
				$key = $row['rowID'];
				if (!empty($row['itemID'])) {
					$key = '0timesheet-' . $row['date'] . '-' . $row['start_time'] . '-' . $row['end_time'] . '-' . $row['itemID'];
				} else if (!empty($row['expenseID'])) {
					$key = '1expense-' . $row['expense_date'] . '-' . $row['expenseID'];
				}
				$invoice_rows[$key] = $row;
			}
			ksort($invoice_rows);
		}

		$pdf_data = array(
			'invoice' => $invoice_info,
			'staff' => $staff_info,
			'invoice_rows' => $invoice_rows,
			'invoice_prefix' => $this->settings_library->get('staff_invoice_prefix')
		);
		$invoice_html = $this->load->view('timesheets/invoice_pdf', $pdf_data, TRUE);

		// generate PDF
		$dompdf = new Dompdf\Dompdf();
		$dompdf->loadHtml($invoice_html);
		$dompdf->setPaper('A4', 'portraint');
		$dompdf->render();
		return $dompdf->output();
	}

	/**
	 * delete a timesheet
	 * @param  int $timesheetID
	 * @return mixed
	 */
	public function remove($timesheetID = NULL) {

		// only allow directors and management
		if (!in_array($this->auth->user->department, $this->allowed_departments)) {
			show_404();
		}

		// check params
		if (empty($timesheetID)) {
			show_404();
		}

		$where = array(
			'timesheets.timesheetID' => $timesheetID,
			'timesheets.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('timesheets.*, staff.first, staff.surname')->from('timesheets')->join('staff', 'timesheets.staffID = staff.staffID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$timesheet_info = $row;

			// all ok, delete
			$query = $this->db->delete('timesheets', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $timesheet_info->first . ' ' . $timesheet_info->surname . ' (' . mysql_to_uk_date($timesheet_info->date) . ') has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $timesheet_info->first . ' ' . $timesheet_info->surname . ' (' . mysql_to_uk_date($timesheet_info->date) . ') could not be removed.');
			}

			$redirect_to = 'timesheets/date/' . $timesheet_info->date;
			redirect($redirect_to);
		}
	}

	/**
	 * show approvals for logged in user
	 * @param $show_all boolean
	 * @return void
	 */
	public function approvals($show_all = TRUE) {

		// only allow directors and management or team leaders
		if (!in_array($this->auth->user->department, $this->allowed_departments) && $this->auth->user->department != 'headcoach') {
			show_404();
		}

		// if only viewing own
		if ($show_all === 'false') {
			$show_all = FALSE;
		}

		// check permission if viewing all
		if ($show_all === TRUE && !in_array($this->auth->user->department, $this->allowed_departments)) {
			$show_all = FALSE;
		}

		// set defaults
		$icon = 'ok';
		$current_page = 'approvals_own';
		$section = 'timesheets';
		$page_base = 'finance/approvals';
		$title = 'Your Approvals';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$selected_approvals = array();
		$action = NULL;
		$breadcrumb_levels = array(
			'timesheets' => 'Timesheets'
		);

		// set where
		$where = array(
			'timesheets_items.accountID' => $this->auth->user->accountID,
			'timesheets_items.approverID' => $this->auth->user->staffID,
			'timesheets_items.status' => 'submitted'
		);

		// if showing all
		if ($show_all === TRUE) {
			$title = 'Approvals';
			$current_page = 'approvals';
			unset($where['timesheets_items.approverID']);
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'staff_id' => NULL,
			'date_from' => NULL,
			'date_to' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post('search')) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets-approvals'))) {

			foreach ($this->session->userdata('search-timesheets-approvals') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets-approvals', $search_fields);

			if ($search_fields['staff_id'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("timesheets") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
			}

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets_items") . "`.`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = '`' . $this->db->dbprefix("timesheets_items") . "`.`date` <= " . $this->db->escape($date_to);
				}
			}

		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// bulk actions
		if ($this->input->post('bulk') == 1) {
			if (is_array($this->input->post('selected_approvals'))) {
				$selected_approvals = $this->input->post('selected_approvals');
			}
			$action = $this->input->post('action');
			$bulk_successful = 0;
			$bulk_failed = 0;
			if (count($selected_approvals) > 0) {
				foreach ($selected_approvals as $itemID => $type) {
					switch ($action) {
						case 'approve':
							$res = $this->approve($type, $itemID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
						case 'decline':
							$res = $this->decline($type, $itemID, TRUE);
							if ($res === TRUE) {
								$bulk_successful++;
							} else {
								$bulk_failed++;
							}
							break;
					}
				}
			}

			if ($bulk_successful > 0 && $bulk_failed == 0) {
				$this->session->set_flashdata('success', $bulk_successful . ' item(s) has been processed successfully.');
			} else if ($bulk_successful == 0 && $bulk_failed > 0) {
				$this->session->set_flashdata('error', $bulk_failed . ' item(s) could not be processed.');
			} else if ($bulk_successful > 0 && $bulk_failed > 0) {
				$this->session->set_flashdata('info', $bulk_successful . ' item(s) has been processed successfully, however ' .  $bulk_failed . ' item(s) could not be processed.');
			}
			$redirect_to = 'finance/approvals';
			if ($show_all !== TRUE) {
				$redirect_to .= '/own';
			}
			redirect($redirect_to);
			exit();
		}

		$approvals = array();

		// run query for timesheet items
		$res = $this->db->select('timesheets_items.*, bookings.type, staff.first as staff_first, staff.surname as staff_last, approver.first as approver_first, approver.surname as approver_last, orgs.name as venue, brands.name as brand, brands.colour as brand_colour, staff_addresses.postcode as staff_postcode, orgs_addresses.postcode, event_address.postcode as event_postcode, nonlesson_address.postcode as nonlesson_postcode, timesheets.date as timesheet_date, activities.name as activity')
		->from('timesheets_items')
		->join('timesheets', 'timesheets_items.timesheetID = timesheets.timesheetID', 'inner')
		->join('staff', 'timesheets.staffID = staff.staffID', 'inner')
		->join('bookings_lessons', 'timesheets_items.lessonID = bookings_lessons.lessonID', 'left')
		->join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'left')
		->join('staff_addresses', 'staff.staffID = staff_addresses.staffID AND staff_addresses.type = "main"', 'left')
		->join('staff as approver', 'timesheets_items.approverID = approver.staffID', 'left')
		->join('brands', 'timesheets_items.brandID = brands.brandID', 'left')
		->join('activities', 'timesheets_items.activityID = activities.activityID', 'left')
		->join('orgs_addresses', 'bookings_lessons.addressID = orgs_addresses.addressID', 'left')
		->join('orgs_addresses as event_address', 'bookings.addressID = event_address.addressID', 'left')
		->join('orgs_addresses as nonlesson_address', 'timesheets_items.orgID = nonlesson_address.orgID and nonlesson_address.type = \'main\'', 'left')
		->join('orgs', 'timesheets_items.orgID = orgs.orgID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('date desc')
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$row->type = 'item';
				// get prev timesheet item
				$prev_item = $this->crm_library->get_prev_timesheet_item($row->date, $row->start_time, $row->timesheetID);
				if ($prev_item !== FALSE) {
					$row->prev_postcode = $prev_item->postcode;
				}
				// get next timesheet item
				$next_item = $this->crm_library->get_next_timesheet_item($row->date, $row->end_time, $row->timesheetID);
				if ($next_item !== FALSE) {
					$row->next_postcode = $next_item->postcode;
				}
				// get item postcode
				if (empty($row->lessonID)) {
					$row->postcode = $row->nonlesson_postcode;
				} else if ($row->type == 'event') {
					$row->postcode = $row->event_postcode;
				}
				unset($row->event_postcode);
				unset($row->nonlesson_postcode);
				// add to approvals
				$key = $row->date . '-item-' . $row->itemID;
				$approvals[$key] = $row;
			}
		}
		

		// run query for timesheet expenses
		if ($this->auth->has_features('expenses')) {
			foreach ($where as $id => $clause) {
				unset($where[$id]);
				$id = str_replace('timesheets_items', 'timesheets_expenses', $id);
				$where[$id] = str_replace('timesheets_items', 'timesheets_expenses', $clause);
			}
			if (is_string($search_where)) {
				$search_where = str_replace('timesheets_items', 'timesheets_expenses', $search_where);
			}
			$res = $this->db->select('timesheets_expenses.*, staff.first as staff_first, staff.surname as staff_last, approver.first as approver_first, approver.surname as approver_last, orgs.name as venue, brands.name as brand, brands.colour as brand_colour, timesheets.date as timesheet_date')
				->from('timesheets_expenses')
				->join('timesheets', 'timesheets_expenses.timesheetID = timesheets.timesheetID', 'inner')
				->join('staff', 'timesheets.staffID = staff.staffID', 'inner')
				->join('staff as approver', 'timesheets_expenses.approverID = approver.staffID', 'left')
				->join('brands', 'timesheets_expenses.brandID = brands.brandID', 'left')
				->join('orgs', 'timesheets_expenses.orgID = orgs.orgID', 'left')
				->where($where)
				->where($search_where, NULL, FALSE)
				->order_by('date desc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$row->type = 'expense';
					$row->itemID = $row->expenseID;
					$key = $row->date . '-expense-' . $row->expenseID;
					$approvals[$key] = $row;
				}
			}
		}
		//Check Mileage Section in accounts
		$mileage_section = $this->auth->has_features("mileage");
		
		if($mileage_section == 1){
			// run query for timesheet mileage
			// set where
			$where = array(
				'timesheets_mileage.accountID' => $this->auth->user->accountID,
				'timesheets_mileage.approverID' => $this->auth->user->staffID,
				'timesheets_mileage.status' => 'submitted'
			);
			
			$res = $this->db->select('timesheets_mileage.*, staff.first as staff_first, staff.surname as staff_last, approver.first as approver_first, approver.surname as approver_last, timesheets.date as timesheet_date')
				->from('timesheets_mileage')
				->join('timesheets', 'timesheets_mileage.timesheetID = timesheets.timesheetID', 'inner')
				->join('staff', 'timesheets.staffID = staff.staffID', 'inner')
				->join('staff as approver', 'timesheets_mileage.approverID = approver.staffID', 'left')
				->where($where)
				->where($search_where, NULL, FALSE)
				->order_by('date desc')->get();
				
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$row->type = 'mileage';
					$row->itemID = $row->itemID;
					$key = $row->date . '-mileage-' . $row->itemID;
					$approvals[$key] = $row;
				}
			}
			
			// run query for timesheet_fuel_card
			$where = array(
				'timesheets_fuel_card.accountID' => $this->auth->user->accountID,
				'timesheets_fuel_card.approverID' => $this->auth->user->staffID,
				'timesheets_fuel_card.status' => 'submitted'
			);
			
			$res = $this->db->select('timesheets_fuel_card.*, staff.first as staff_first, staff.surname as staff_last, approver.first as approver_first, approver.surname as approver_last, timesheets.date as timesheet_date')
				->from('timesheets_fuel_card')
				->join('timesheets', 'timesheets_fuel_card.timesheetID = timesheets.timesheetID', 'inner')
				->join('staff', 'timesheets.staffID = staff.staffID', 'inner')
				->join('staff as approver', 'timesheets_fuel_card.approverID = approver.staffID', 'left')
				->where($where)
				->where($search_where, NULL, FALSE)
				->order_by('date desc')->get();
				
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$row->type = 'fuel_card';
					$row->itemID = $row->id;
					$key = 'fuel_card-' . $row->id;
					$approvals[$key] = $row;
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

		// staff
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'approvals' => $approvals,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'staff_list' => $staff_list,
			'selected_approvals' => $selected_approvals,
			'action' => $action,
			'show_all' => $show_all
		);

		// load view
		$this->crm_view('timesheets/approvals', $data);
	}

	/**
	 * approve an item
	 * @param string $type
	 * @param  int $itemID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function approve($type = NULL, $itemID = NULL, $bulk = FALSE) {

		// no need for permission check as query below ensure only approver can approve

		// check params
		if (empty($type) || empty($itemID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		switch ($type) {
			case 'item':
				$table = 'timesheets_items';
				$id_field = 'itemID';
				break;
			case 'expense':
				$table = 'timesheets_expenses';
				$id_field = 'expenseID';
				break;
			case 'mileage':
				$table = 'timesheets_mileage';
				$id_field = 'itemID';
				break;
			case 'fuel_card':
				$table = 'timesheets_fuel_card';
				$id_field = 'id';
				break;
			default:
				if ($bulk == TRUE) {
					return FALSE;
				}
				show_404();
				break;
		}

		$where = array(
			$id_field => $itemID,
			'accountID' => $this->auth->user->accountID,
			'approverID' => $this->auth->user->staffID
		);

		// if in allowed department, can approve any
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			unset($where['approverID']);
		}

		// run query
		$query = $this->db->from($table)->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$item_info = $row;

			// all ok, approve
			$data = array(
				'approverID' => $this->auth->user->staffID,
				'status' => 'approved',
				'approved' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$query = $this->db->update($table, $data, $where, 1);

			if ($bulk != TRUE) {
				if ($this->db->affected_rows() == 1) {
					$this->session->set_flashdata('success', 'Item has been approved successfully.');
				} else {
					$this->session->set_flashdata('error', 'Item could not be approved.');
				}
			}

			// check for items not yet processed
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'timesheetID' => $item_info->timesheetID
			);
			$where_in = array(
				'unsubmitted',
				'submitted'
			);
			$res_1 = $this->db->from('timesheets_items')->where($where)->where_in('status', $where_in)->get();
			$res_2 = $this->db->from('timesheets_expenses')->where($where)->where_in('status', $where_in)->get();
			$res_3 = $this->db->from('timesheets_mileage')->where($where)->where_in('status', $where_in)->get();
			$res_4 = $this->db->from('timesheets_fuel_card')->where($where)->where_in('status', $where_in)->get();

			// check
			if ($res_1->num_rows() == 0 && $res_2->num_rows() == 0 && $res_3->num_rows() == 0) {
				// mark timesheet as approved
				$data = array(
					'status' => 'approved',
					'approverID' => $this->auth->user->staffID,
					'approved' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$where = array(
					'accountID' => $this->auth->user->accountID,
					'timesheetID' => $item_info->timesheetID
				);
				$this->db->update('timesheets', $data, $where, 1);
			}

			if ($bulk == TRUE) {
				return TRUE;
			}

			$redirect_to = 'finance/approvals';
			if ($item_info->approverID == $this->auth->user->staffID) {
				$redirect_to .= '/own';
			}
			redirect($redirect_to);
		}
	}

	/**
	 * decline an item
	 * @param string $type
	 * @param  int $itemID
	 * @param boolean $bulk
	 * @return mixed
	 */
	public function decline($type = NULL, $itemID = NULL, $bulk = FALSE) {

		// no need for permission check as query below ensure only approver can approve

		// check params
		if (empty($type) || empty($itemID)) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		switch ($type) {
			case 'item':
				$table = 'timesheets_items';
				$id_field = 'itemID';
				break;
			case 'expense':
				$table = 'timesheets_expenses';
				$id_field = 'expenseID';
				break;
			case 'mileage':
				$table = 'timesheets_mileage';
				$id_field = 'itemID';
				break;
			case 'fuel_card':
				$table = 'timesheets_fuel_card';
				$id_field = 'id';
				break;
			default:
				if ($bulk == TRUE) {
					return FALSE;
				}
				show_404();
				break;
		}

		$where = array(
			$id_field => $itemID,
			'accountID' => $this->auth->user->accountID,
			'approverID' => $this->auth->user->staffID
		);

		// if in allowed department, can decline any
		if (in_array($this->auth->user->department, $this->allowed_departments)) {
			unset($where['approverID']);
		}

		// run query
		$query = $this->db->from($table)->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			if ($bulk == TRUE) {
				return FALSE;
			}
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$item_info = $row;

			// all ok, decline
			$data = array(
				'approverID' => $this->auth->user->staffID,
				'declined' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			switch ($type) {
				case 'expense':
					$data['status'] = 'declined';
					$query = $this->db->update($table, $data, $where, 1);
					$updated_rows = $this->db->affected_rows();
					// recalc total expenses
					$calc_where = array(
						'timesheetID' => $item_info->timesheetID,
						'accountID' => $this->auth->user->accountID,
						'status !=' => 'declined'
					);
					$calc_res = $this->db->select('SUM(`amount`) as `total_expenses`')->from('timesheets_expenses')->where($calc_where)->group_by('timesheetID')->get();
					$total_expenses = 0;
					if ($calc_res->num_rows() > 0) {
						foreach ($calc_res->result() as $calc_row) {
							$total_expenses = $calc_row->total_expenses;
						}
					}
					$calc_where = array(
						'timesheetID' => $item_info->timesheetID,
						'accountID' => $this->auth->user->accountID
					);
					$calc_data = array(
						'total_expenses' => $total_expenses
					);
					break;
				case 'item':
					// if not about lesson, set status to declined
					if (empty($item_info->lessonID)) {
						$data['status'] = 'declined';
					} else {
						// set times back to original
						$data['start_time'] = $item_info->original_start_time;
						$data['end_time'] = $item_info->original_end_time;
						// mark approved
						$data['status'] ='approved';
						// add declined to reason desc
						$data['reason_desc'] = $item_info->reason_desc . ' (Time Changed Declined)';
					}
					$query = $this->db->update($table, $data, $where, 1);
					$updated_rows = $this->db->affected_rows();
					// recalc total time
					$calc_where = array(
						'timesheetID' => $item_info->timesheetID,
						'accountID' => $this->auth->user->accountID,
						'status !=' => 'declined'
					);
					$calc_res = $this->db->select('SEC_TO_TIME(SUM(TIME_TO_SEC(`total_time`))) as `total_time`')->from('timesheets_items')->where($calc_where)->group_by('timesheetID')->get();
					$total_time = '00:00';
					if ($calc_res->num_rows() > 0) {
						foreach ($calc_res->result() as $calc_row) {
							$total_time = $calc_row->total_time;
						}
					}
					$calc_where = array(
						'timesheetID' => $item_info->timesheetID,
						'accountID' => $this->auth->user->accountID
					);
					$calc_data = array(
						'total_time' => $total_time
					);
					$calc_update = $this->db->update('timesheets', $calc_data, $calc_where, 1);
					break;
				case 'mileage':
					$data['status'] = 'declined';
					$query = $this->db->update($table, $data, $where, 1);
					$updated_rows = $this->db->affected_rows();
					break;
				case 'fuel_card':
					$data['status'] = 'declined';
					$query = $this->db->update($table, $data, $where, 1);
					$updated_rows = $this->db->affected_rows();
					break;
				
			}

			if ($bulk != TRUE) {
				if ($updated_rows == 1) {
					$this->session->set_flashdata('success', 'Item has been declined successfully.');
				} else {
					$this->session->set_flashdata('error', 'Item could not be declined.');
				}
			}

			// check for items not yet processed
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'timesheetID' => $item_info->timesheetID
			);
			$where_in = array(
				'unsubmitted',
				'submitted'
			);
			$res_1 = $this->db->from('timesheets_items')->where($where)->where_in('status', $where_in)->get();
			$res_2 = $this->db->from('timesheets_expenses')->where($where)->where_in('status', $where_in)->get();
			$res_3 = $this->db->from('timesheets_mileage')->where($where)->where_in('status', $where_in)->get();

			// check
			if ($res_1->num_rows() == 0 && $res_2->num_rows() == 0 && $res_3->num_rows() == 0) {
				// mark timesheet as approved
				$data = array(
					'status' => 'approved',
					'approverID' => $this->auth->user->staffID,
					'approved' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$where = array(
					'accountID' => $this->auth->user->accountID,
					'timesheetID' => $item_info->timesheetID
				);
				$this->db->update('timesheets', $data, $where, 1);
			}

			if ($bulk == TRUE) {
				return TRUE;
			}

			$redirect_to = 'finance/approvals';
			if ($item_info->approverID == $this->auth->user->staffID) {
				$redirect_to .= '/own';
			}
			redirect($redirect_to);
		}
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
	 * check date is a monday
	 * @param  string $date
	 * @return bool
	 */
	public function check_monday($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check is a monday
		if (date('l', strtotime(uk_to_mysql_date($date))) == 'Monday') {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * check edited items are valid
	 * @return bool
	 */
	public function check_edited_items() {
		$edited_items = $this->input->post('edited_items');

		// if none, all ok
		if (!is_array($edited_items) || count($edited_items) == 0) {
			return TRUE;
		}

		foreach ($edited_items as $item_id => $item) {
			// check of not editing
			if (!isset($item['edited']) || $item['edited'] != 1) {
				continue;
			}

			// check for fields
			if (!isset($item['start_time']['h'], $item['start_time']['m'], $item['end_time']['h'], $item['end_time']['m'], $item['approverID'], $item['reason'], $item['reason_desc'])) {
				return FALSE;
			}

			// check fields not empty
			if (empty($item['start_time']['h']) || $item['start_time']['m'] < 0 || empty($item['end_time']['h']) || $item['end_time']['m'] < 0 || empty($item['approverID']) || empty($item['reason']) || empty($item['reason_desc'])) {
				return FALSE;
			}

			// check end time is after start time
			if (strtotime($item['end_time']['h'] . ':' . $item['end_time']['m']) < strtotime($item['start_time']['h'] . ':' . $item['start_time']['m'])) {
				echo 3;
				return FALSE;
			}
		}

		return TRUE;
	}
	
	/**
	 * check edited mileage are valid
	 * @return bool
	 */
	public function check_edited_mileage() {
		$edited_mileage = $this->input->post('edited_mileage');

		// if none, all ok
		if (!is_array($edited_mileage) || count($edited_mileage) == 0) {
			return TRUE;
		}

		foreach ($edited_mileage as $mileage_id => $mileage) {
			// check of not editing
			if (!isset($mileage['edited']) || $mileage['edited'] != 1) {
				continue;
			}

			// check for fields
			if (!isset($mileage['date'], $mileage['start_location'], $mileage['session_location'], $mileage['mode'], $mileage['approverID'], $mileage['reason'], $mileage['reason_desc'])) {
				return FALSE;
			}
			
			// check fields not empty
			if (empty($mileage['date']) || empty($mileage['start_location']) || empty($mileage['session_location']) || empty($mileage['mode']) || empty($mileage['approverID']) || empty($mileage['reason']) || empty($mileage['reason_desc'])) {
				return FALSE;
			}
		}

		return TRUE;
	}
	
	/**
	 * check edited fuel card are valid
	 * @return bool
	 */
	public function check_edited_fuel_card() {
		$edited_fuel_card = $this->input->post('edited_fuel_card');

		// if none, all ok
		if (!is_array($edited_fuel_card) || count($edited_fuel_card) == 0) {
			return TRUE;
		}

		foreach ($edited_fuel_card as $item_id => $fuel_card) {
			// check of not editing
			if (!isset($fuel_card['edited']) || $fuel_card['edited'] != 1) {
				continue;
			}

			// check for fields
			if (!isset($fuel_card['end_mileage'], $fuel_card['approverID'], $fuel_card['reason'])) {
				return FALSE;
			}
			
			// check fields not empty
			if (empty($fuel_card['end_mileage']) || empty($fuel_card['approverID']) || empty($fuel_card['reason'])) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * check new items are valid
	 * @return bool
	 */
	public function check_new_items() {
		$new_items = $this->input->post('new_items');

		// if none, all ok
		if (!is_array($new_items) || count($new_items) == 0) {
			return TRUE;
		}

		foreach ($new_items as $item_id => $item) {
			// if all empty, skip
			if (empty($item['date']) && empty($item['orgID']) && empty($item['brandID']) && empty($item['approverID']) && empty($item['reason']) && empty($item['reason_desc'])) {
				continue;
			}

			// check for fields
			if (!isset($item['date'], $item['orgID'], $item['brandID'], $item['start_time']['h'], $item['start_time']['m'], $item['end_time']['h'], $item['end_time']['m'], $item['approverID'], $item['reason'], $item['reason_desc'])) {
				return FALSE;
			}

			// check fields not empty
			if (empty($item['date']) || empty($item['orgID']) || empty($item['brandID']) || empty($item['start_time']['h']) || $item['start_time']['m'] < 0 || empty($item['end_time']['h']) || $item['end_time']['m'] < 0 || empty($item['approverID']) || empty($item['reason']) || empty($item['reason_desc'])) {
				return FALSE;
			}

			// check date field
			if (!check_mysql_date($item['date'])) {
				return FALSE;
			}

			// check end time is after start time
			if (strtotime($item['end_time']['h'] . ':' . $item['end_time']['m']) < strtotime($item['start_time']['h'] . ':' . $item['start_time']['m'])) {
				return FALSE;
			}
		}

		return TRUE;
	}
	
	/**
	 * check new mileage are valid
	 * @return bool
	 */
	public function check_new_mileage() {
		$new_mileage = $this->input->post('new_mileage');

		// if none, all ok
		if (!is_array($new_mileage) || count($new_mileage) == 0) {
			return TRUE;
		}

		foreach ($new_mileage as $mileage_id => $mileage) {
			// if all empty, skip
			if (empty($mileage['date']) && empty($mileage['start_location']) && empty($mileage['session_location']) && empty($mileage['mode']) && empty($mileage['approverID']) && empty($mileage['reason']) && empty($mileage['reason_desc'])) {
				continue;
			}

			// check for fields
			if (!isset($mileage['date'], $mileage['start_location'], $mileage['session_location'], $mileage['mode'], $mileage['approverID'], $mileage['reason'], $mileage['reason_desc'])) {
				return FALSE;
			}

			// check fields not empty
			if (empty($mileage['date']) || empty($mileage['start_location']) || empty($mileage['session_location']) || empty($mileage['mode']) || empty($mileage['approverID']) || empty($mileage['reason']) || empty($mileage['reason_desc'])) {
				return FALSE;
			}

		}

		return TRUE;
	}

	/**
	 * check edited expenses are valid
	 * @return bool
	 */
	public function check_edited_expenses() {
		$edited_expenses = $this->input->post('edited_expenses');

		// if none, all ok
		if (!is_array($edited_expenses) || count($edited_expenses) == 0) {
			return TRUE;
		}

		foreach ($edited_expenses as $item_id => $item) {
			// check of not editing
			if (!isset($item['edited']) || $item['edited'] != 1) {
				continue;
			}

			// check for fields
			if (!isset($item['date'], $item['orgID'], $item['brandID'], $item['item'], $item['amount'], $item['approverID'], $item['reason'], $item['reason_desc'])) {
				return FALSE;
			}

			// check fields not empty
			if (empty($item['date']) || empty($item['orgID']) || empty($item['brandID']) || empty($item['item']) || !is_numeric($item['amount']) || $item['amount'] < 0 || empty($item['approverID']) || empty($item['reason']) || empty($item['reason_desc'])) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * check new expenses are valid
	 * @return bool
	 */
	public function check_new_expenses() {
		$new_expenses = $this->input->post('new_expenses');

		// if none, all ok
		if (!is_array($new_expenses) || count($new_expenses) == 0) {
			return TRUE;
		}

		foreach ($new_expenses as $item_id => $item) {
			// if all empty, skip
			if (empty($item['date']) && empty($item['orgID']) && empty($item['brandID']) && empty($item['item']) && empty($item['amount']) && empty($item['approverID']) && empty($item['reason']) && empty($item['reason_desc'])) {
				continue;
			}

			// check for fields
			if (!isset($item['date'], $item['orgID'], $item['brandID'], $item['item'], $item['amount'], $item['approverID'], $item['reason'], $item['reason_desc'])) {
				return FALSE;
			}

			// check fields not empty
			if (empty($item['date']) || empty($item['orgID']) || empty($item['brandID']) || empty($item['item']) || !is_numeric($item['amount']) || $item['amount'] < 0 || empty($item['approverID']) || empty($item['reason']) || empty($item['reason_desc'])) {
				return FALSE;
			}

			// check date field
			if (!check_mysql_date($item['date'])) {
				return FALSE;
			}

			// check for file
			if (!isset($_FILES['new_expenses']['name'][$item_id]['receipt']) || empty($_FILES['new_expenses']['name'][$item_id]['receipt'])) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * check if selected timesheets are uninvoied and have items
	 * @param array $timesheets
	 * @return boolean
	 */
	private function check_timesheets_uninvoiced($timesheets) {

		// check params
		if (!is_array($timesheets) || count($timesheets) <= 0) {
			return FALSE;
		}

		$timesheets_with_items = array();

		// check items
		$where = array(
			'timesheets.accountID' =>$this->auth->user->accountID,
			'timesheets.status' => 'approved',
			'timesheets.invoiced !=' => 1,
			'timesheets_items.salaried !=' => 1
		);
		$res = $this->db->select('timesheets.timesheetID')
			->from('timesheets')
			->join('timesheets_items', 'timesheets.timesheetID = timesheets_items.timesheetID', 'inner')
			->where($where)
			->where_in('timesheets.timesheetID', $timesheets)
			->group_by('timesheets.timesheetID')
			->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$timesheets_with_items[$row->timesheetID] = $row->timesheetID;
			}
		}

		// check expenses
		$where = array(
			'timesheets.accountID' =>$this->auth->user->accountID,
			'timesheets.status' => 'approved',
			'timesheets.invoiced !=' => 1,
		);
		$res = $this->db->select('timesheets.timesheetID')
			->from('timesheets')
			->join('timesheets_expenses', 'timesheets.timesheetID = timesheets_expenses.timesheetID', 'inner')
			->where($where)
			->where_in('timesheets.timesheetID', $timesheets)
			->group_by('timesheets.timesheetID')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$timesheets_with_items[$row->timesheetID] = $row->timesheetID;
			}
		}

		// check timesheets with found items matches number passed in
		if (count($timesheets_with_items) == count($timesheets)) {
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * refresh invoice rows
	 * @param  int $invoiceID
	 * @return mixed
	 */
	private function refresh_invoice_rows($invoiceID) {

		$this->load->library('reports_library');
		// look up invoice
		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' =>$this->auth->user->accountID,
			'sent !=' => 1
		);
		$res = $this->db->from('staff_invoices')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $invoice_info) {}

		// look up staff
		$where = array(
			'staffID' => $invoice_info->staffID,
			'accountID' =>$this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return FALSE;
		}
		foreach ($res->result() as $staff_info) {}

		$timesheet_items = array();
		$timesheet_expenses = array();

		// get timesheet items
		$where_item = array(
			'timesheets.staffID' => $invoice_info->staffID,
			'timesheets.status' => 'approved',
			'timesheets_items.status' => 'approved',
			'timesheets_items.accountID' => $this->auth->user->accountID,
			'timesheets_items.salaried !=' => 1,
			'staff_invoices_timesheets.invoiceID' => $invoiceID
		);

		$res = $this->db->select('timesheets_items.*, staff.first as approver_first, staff.surname as approver_last, activities.name as activity, bookings_lessons.activity_other, bookings_blocks.name as block_name, orgs.name as booking_org, blocks_orgs.name as block_org, lesson_types.hourly_rate, lesson_types.typeID')->
		from('timesheets_items')->
		join('staff_invoices_timesheets', 'timesheets_items.timesheetID = staff_invoices_timesheets.timesheetID', 'inner')->
		join('timesheets', 'timesheets_items.timesheetID = timesheets.timesheetID', 'inner')->
		join('staff', 'timesheets_items.approverID = staff.staffID', 'left')->
		join('bookings_lessons', 'timesheets_items.lessonID = bookings_lessons.lessonID', 'left')->
		join('lesson_types', 'lesson_types.typeID=bookings_lessons.typeID', 'left')->
		join('bookings', 'bookings_lessons.bookingID = bookings.bookingID', 'left')->
		join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'left')->
		join('orgs', 'bookings.orgID = orgs.orgID', 'left')->
		join('orgs as blocks_orgs', 'bookings_blocks.orgID = blocks_orgs.orgID', 'left')->
		join('activities', 'bookings_lessons.activityID = activities.activityID', 'left')->
		where($where_item)->group_by('timesheets_items.itemID')->
		order_by('timesheets_items.date asc, timesheets_items.start_time asc, timesheets_items.end_time asc')->
		get();

		if ($res->num_rows() > 0) {

			foreach ($res->result_array() as $row) {
				$timesheet_items[$row['itemID']] = $row;
			}
		}

		// get timesheet expenses
		if ($this->auth->has_features('expenses')) {
			$where_item = array(
				'timesheets.staffID' => $invoice_info->staffID,
				'timesheets.status' => 'approved',
				'timesheets_expenses.status' => 'approved',
				'timesheets_expenses.accountID' => $this->auth->user->accountID,
				'staff_invoices_timesheets.invoiceID' => $invoiceID
			);
			$res = $this->db->select('timesheets_expenses.*, staff.first as approver_first, staff.surname as approver_last, orgs.name as venue, brands.name as brand, brands.colour as brand_colour')->from('timesheets_expenses')->join('staff_invoices_timesheets', 'timesheets_expenses.timesheetID = staff_invoices_timesheets.timesheetID', 'inner')->join('timesheets', 'timesheets_expenses.timesheetID = timesheets.timesheetID', 'inner')->join('staff', 'timesheets_expenses.approverID = staff.staffID', 'left')->join('brands', 'timesheets_expenses.brandID = brands.brandID', 'left')->join('orgs', 'timesheets_expenses.orgID = orgs.orgID', 'left')->where($where_item)->order_by('timesheets_expenses.date asc, timesheets_expenses.item asc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					if($staff_info->department != "fulltimecoach"){
						$timesheet_expenses[$row['expenseID']] = $row;
					}
				}
			}
		}

		// work out invoice rows
		$total = 0;
		$invoice_rows = array();
		if (count($timesheet_items) > 0) {
			foreach ($timesheet_items as $row) {
				$selected_qual = $this->db->select('mandatory_quals.*')
					->from('mandatory_quals')
					->join('staff_quals_mandatory',
						'mandatory_quals.qualID=staff_quals_mandatory.qualID AND staff_quals_mandatory.preferred_for_pay_rate=1',
						'left')
					->where([
						'mandatory_quals.accountID' => $this->auth->user->accountID,
						'staff_quals_mandatory.staffID' => $staff_info->staffID
					])->limit(1)->get()->result();

				if (!empty($selected_qual)) {
					$selected_qual = $selected_qual[0];
				}
				$session_override_rate = 0;
				if ($staff_info->system_pay_rates && !empty($selected_qual)) {

					$session_rates = $this->db->from('session_qual_rates')
						->where([
							'accountID' => $this->auth->user->accountID,
							'lessionTypeID' => $row['typeID'],
							'qualTypeID' => $selected_qual->qualID,
						])->limit(1)->get()->result();

					if (!empty($session_rates)) {
						$session_override_rate = $this->reports_library->get_qualification_rate_by_session($staff_info, $selected_qual, $session_rates[0], $row['role']);
					}
				}

				$amount = 0;
				$hourly_rate = (float)$row['hourly_rate'];
				$per_hour = 0;

				if ($hourly_rate > 0)
				{
					$per_hour = $hourly_rate;
				}
				else
				{
					if ($session_override_rate > 0) {
						$per_hour += $session_override_rate;
					} else {
						if(!$staff_info->system_pay_rates &&  (float)$staff_info->hourly_rate > 0){
							// for this staff member only hourly_rate is set

							$per_hour = $staff_info->hourly_rate;
						} else {
							if ($staff_info->system_pay_rates && !empty($selected_qual)) {
								$per_hour = $this->reports_library->get_qualification_rate($staff_info, $selected_qual);
							} else {
								switch ($row['role'])
								{
									case 'lead':
									case 'head':
										$per_hour = $staff_info->payments_scale_head;
										break;
									default:
										$per_hour = $staff_info->payments_scale_assist;
										break;
								}
							}
						}
					}
				}
				$lesson_length = time_to_seconds($row['total_time'])/60/60;
				$amount = round($lesson_length * $per_hour, 2);
				$activity = $row['activity'];
				if (empty($activity)) {
					$activity = $row['activity_other'];
				}
				$org = $row['block_org'];
				if (empty($org)) {
					$org = $row['booking_org'];
				}
				$desc_bits = array(
					'date' => mysql_to_uk_date($row['date']),
					'time' => substr($row['start_time'], 0, 5) . '-' . substr($row['end_time'], 0, 5),
					'activity' => $activity,
					'block' => $row['block_name']
				);
				if (!empty($org)) {
					$desc_bits['block'] .= ' (' . $org . ')';
				}
				if (!empty($row['role'])) {
					$role = $this->settings_library->get_staffing_type_label($row['role']);
					$desc_bits['role'] = $role;
				} else if (!empty($row['reason'])) {
					$desc_bits['role'] = ucwords($row['reason']);
				}
				$desc_bits = array_filter($desc_bits);
				$invoice_row = array(
					'type' => 'item',
					'itemID' => $row['itemID'],
					'desc' => 'Timesheet: ' . implode(" - ", $desc_bits),
					'amount' => $amount,
					'timesheetID' => $row['timesheetID']
				);
				$invoice_rows[] = $invoice_row;
				$total += $invoice_row['amount'];
			}
		}
		if ($this->auth->has_features('expenses') && count($timesheet_expenses) > 0) {
			foreach ($timesheet_expenses as $row) {
				$invoice_row = array(
					'type' => 'expense',
					'expenseID' => $row['expenseID'],
					'desc' => 'Expense: ' . mysql_to_uk_date($row['date']) . ' - ' . $row['venue'] . ': ' . $row['item'],
					'amount' => $row['amount'],
					'timesheetID' => $row['timesheetID']
				);
				$invoice_rows[] = $invoice_row;
				$total += $invoice_row['amount'];
			}
		}

		// delete existing invoice rows
		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' =>$this->auth->user->accountID
		);
		$res = $this->db->delete('staff_invoices_items', $where);

		// insert invoice rows
		foreach ($invoice_rows as $row) {
			$data = array(
				'invoiceID' => $invoiceID,
				'timesheetID' => $row['timesheetID'],
				'accountID' => $this->auth->user->accountID,
				'type' => $row['type'],
				'itemID' => NULL,
				'expenseID' => NULL,
				'desc' => $row['desc'],
				'amount' => $row['amount'],
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if (isset($row['itemID']) && !empty($row['itemID'])) {
				$data['itemID'] = $row['itemID'];
			}
			if (isset($row['expenseID']) && !empty($row['expenseID'])) {
				$data['expenseID'] = $row['expenseID'];
			}

			$query = $this->db->insert('staff_invoices_items', $data);
		}

		// update total
		$where = array(
			'invoiceID' => $invoiceID,
			'accountID' => $this->auth->user->accountID
		);
		$data = array(
			'amount' => $total,
			'modified' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$res = $this->db->update('staff_invoices', $data, $where, 1);

		$return = array(
			'invoice_rows' => $invoice_rows,
			'total' => $total
		);

		return $return;
	}

}

/* End of file timesheets.php */
/* Location: ./application/controllers/Timesheets.php */
