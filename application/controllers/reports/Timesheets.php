<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timesheets extends MY_Controller {

	private $categories;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		// load library
		$this->load->library('reports_library');
		$this->load->model('Staff/StaffModel');
		$this->load->model('Settings/Brands');
		$this->load->model('Settings/ActivitiesModel');
	}

	public function index($action = FALSE) {

		$filterBy = [
			'brand' => 'Departments',
			'activity' => 'Activities',
			'role' => 'Role'
		];
		// set defaults
		$icon = 'book';
		$current_page = 'timesheets';
		$section = 'reports';
		$page_base = 'reports/timesheets';
		$title = 'Timesheets Report';
		$buttons = ' <a class="btn btn-primary export-search-submit" href="' . site_url('reports/timesheets/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;
		$period = 'week';

		// check if exporting
		if ($action == 'export') {
			$export = TRUE;
		} else {
			switch ($action) {
				case 'week':
				case 'month':
				case 'quarter':
					$period = $action;
					break;
			}
		}

		// set up search
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'department' => NULL,
			'search' => NULL,
		);

		foreach ($filterBy as $key => $filter) {
			$search_fields['filter_by_' . $key] = 1;
		}
		$is_search = TRUE;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_department', 'Permission Level', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['department'] = set_value('search_department');
			$search_fields['search'] = set_value('search');

			foreach ($filterBy as $key => $filter) {
				if (!set_value('filter_by_' . $key)) {
					$search_fields['filter_by_' . $key] = 0;
				}
			}

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-timesheets-reports'))) {

			foreach ($this->session->userdata('search-timesheets-reports') as $key => $value) {
				$search_fields[$key] = $value;
			}

		}

		// calc offset
		switch ($period) {
			case 'week':
			default:
				$offset = '-1 week';
				break;
			case 'month':
				$offset = '-1 month';
				break;
			case 'quarter':
				$offset = '-3 months';
				break;
		}

		// if dates empty, add default
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
		$searchStaffFields = [];

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-timesheets-reports', $search_fields);

		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		if ($search_fields['staff_id'] != '') {
			$searchStaffFields['staffID'] = (int)($search_fields['staff_id']);
		}

		if ($search_fields['department'] != '') {
			$searchStaffFields['department'] = $search_fields['department'];
		}

		$timesheet_data = $this->reports_library->calc_timesheets('all', $search_fields);

		// get expenses
		$where = array(
			$this->db->dbprefix('timesheets_expenses') . '.status' => 'approved',
			$this->db->dbprefix('timesheets_expenses') . '.date <=' => $date_to,
			$this->db->dbprefix('timesheets_expenses') . '.date >=' => $date_from,
			$this->db->dbprefix('timesheets_expenses') . '.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('timesheets_expenses.*, timesheets.staffID')
			->join('timesheets', 'timesheets_expenses.timesheetID = timesheets.timesheetID', 'inner')
			->from('timesheets_expenses')->where($where)->get();

		$expense_data = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if (!isset($expense_data[$row->staffID])) {
					$expense_data[$row->staffID] = 0;
				}
				$expense_data[$row->staffID] += $row->amount;
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

		$query = $this->db->from("mileage")->where("accountID", $this->auth->user->accountID)->get();
		$mode_price = array();
		foreach($query->result() as $result){
			$mode_price[$result->mileageID] = $result->rate;
		}

		// get mileage cost
		$where = array(
			$this->db->dbprefix('timesheets_mileage') . '.status' => 'approved',
			$this->db->dbprefix('timesheets_mileage') . '.date <=' => $date_to,
			$this->db->dbprefix('timesheets_mileage') . '.date >=' => $date_from,
			$this->db->dbprefix('timesheets_mileage') . '.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('timesheets_mileage.*, timesheets.staffID, staff.mileage_activate_fuel_cards')
			->join('timesheets', 'timesheets_mileage.timesheetID = timesheets.timesheetID', 'inner')
			->join('staff', 'staff.staffID = timesheets.staffID', 'left')
			->from('timesheets_mileage')->where($where)->get();

		$mileage_data = array();
		$dataArray = array();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if(!isset($dataArray[$row->staffID])){
					$dataArray[$row->staffID] = array();
				}
				if (!isset($mileage_data[$row->staffID])) {
					$mileage_data[$row->staffID] = 0;
				}
				$price = 0;
				if(!in_array($row->date, $dataArray[$row->staffID]) && $row->total_mileage != 0){
					$exclude_mileages = 0;
					if($row->mileage_activate_fuel_cards == 1 && $exclude_mileage != NULL)
						$exclude_mileages = $exclude_mileage;
					else if($row->mileage_activate_fuel_cards != 1 && $excluded_mileage_without_fuel_card != NULL)
						$exclude_mileages = $excluded_mileage_without_fuel_card;
					$dataArray[$row->staffID][] = $row->date;
					$price = $exclude_mileages * $mode_price[$row->mode];
				}
				$mileage_data[$row->staffID] += $row->total_cost - ($price/100);

			}
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

		// get staff for report rows, filtered above
		$searchStaffFields['accountID'] = $this->auth->user->accountID;
		$staff = $this->StaffModel->search($searchStaffFields);

		// get staff for search
		$where = [
			'accountID' => $this->auth->user->accountID
		];
		$staff_list = $this->StaffModel->search($where);

		$brands = $this->Brands->getList($this->auth->user->accountID, true, 'name asc');

		$activities = $this->ActivitiesModel->getList($this->auth->user->accountID, true, 'name asc');

		// work out how many days shown
		$days = (strtotime(uk_to_mysql_date($search_fields['date_to'])) - strtotime(uk_to_mysql_date($search_fields['date_from'])))/(24*60*60) + 1;

		// calculate salaried and non-salaried hours
		$where = array(
			$this->db->dbprefix('timesheets_items') . '.status' => 'approved',
			$this->db->dbprefix('timesheets_items') . '.date <=' => $date_to,
			$this->db->dbprefix('timesheets_items') . '.date >=' => $date_from,
			$this->db->dbprefix('timesheets_items') . '.accountID' => $this->auth->user->accountID
		);
		$res = $this->db->select('timesheets_items.*, timesheets.staffID, bookings_lessons_staff.salaried')
			->join('timesheets', 'timesheets_items.timesheetID = timesheets.timesheetID', 'inner')
			->join('bookings_lessons_staff', 'bookings_lessons_staff.lessonID = timesheets_items.lessonID', 'left')
			->from('timesheets_items')
			->where($where)
			->group_by('timesheets_items.itemID')
			->get();

		$salaried = array();
		$nonsalaried = array();
		foreach ($res->result() as $row) {
			$seconds = (strtotime('2000-01-01 ' . $row->total_time) - strtotime(date('Y-m-d H:i', strtotime('2000-01-01 00:00'))))/(60*60);
			if(!isset($salaried[$row->staffID])){
				$salaried[$row->staffID] = 0;
				$nonsalaried[$row->staffID] = 0;
			}
			if($row->salaried == 1){
				$salaried[$row->staffID] += $seconds;
			}else{
				$nonsalaried[$row->staffID] += $seconds;
			}
		}
		// list of roles
		$roles = array(
			'head' => $this->settings_library->get_staffing_type_label('head'),
			'lead' => $this->settings_library->get_staffing_type_label('lead'),
			'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
			'participant' => $this->settings_library->get_staffing_type_label('participant'),
			'observer' => $this->settings_library->get_staffing_type_label('observer'),
			'travel' => 'Travel',
			'training' => 'Training',
			'marketing' => 'Marketing',
			'admin' => 'Admin',
			'other' => 'Other'
		);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'staff' => $staff,
			'timesheet_data' => $timesheet_data,
			'expense_data' => $expense_data,
			'salaried' => $salaried,
			'nonsalaried' => $nonsalaried,
			'brands' => $brands,
			'staff_list' => $staff_list,
			'page_base' => $page_base,
			'days' => $days,
			'roles' => $roles,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'activities' => $activities,
			'mileage_data' => $mileage_data,
			'mileage_section' => $mileage_section,
			'filter_by' => $filterBy
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');

			$this->load->view('reports/timesheets-export', $data);
		} else {
			$this->crm_view('reports/timesheets', $data);
		}
	}

}

/* End of file timesheets.php */
/* Location: ./application/controllers/reports/timesheets.php */
