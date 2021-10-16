<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends MY_Controller {

	private $categories;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports'));

		// load library
		$this->load->library('reports_library');
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'projects';
		$section = 'reports';
		$page_base = 'reports/projects';
		$title = 'Project Delivery Report';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/projects/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
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
		$search_where = array();
		$search_fields = array(
			'type' => 'project-type',
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'project_code_id' => NULL,
			'is_active' => 'yes',
			'search' => NULL
		);
		$is_search = FALSE;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_type', 'Report Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_project_code_id', 'Project Code', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['type'] = set_value('search_type');
			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['project_code_id'] = set_value('search_project_code_id');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['search'] = set_value('search');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if (($export == TRUE || $this->crm_library->last_segment() == 'recall') && is_array($this->session->userdata('search-reports'))) {

			foreach ($this->session->userdata('search-reports') as $key => $value) {
				$search_fields[$key] = $value;
			}

		}

		// calc offset
		switch ($period) {
			case 'week':
			default:
				$offset = '-6 days';
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

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-reports', $search_fields);

		}

		if ($search_fields['date_from'] != '') {
			$date_from = uk_to_mysql_date($search_fields['date_from']);
		}

		if ($search_fields['date_to'] != '') {
			$date_to = uk_to_mysql_date($search_fields['date_to']);
		}

		if ($search_fields['staff_id'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("staff") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
		}

		if ($search_fields['is_active'] != '') {
			if ($search_fields['is_active'] == 'yes') {
				$search_where['is_active'] = '`' . $this->db->dbprefix("staff") . '`.`active` = 1';
			} else {
				$search_where['is_active'] = '`' . $this->db->dbprefix("staff") . '`.`active` != 1';
			}
		}

		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// vars
		$bookings = array();
		$report_data = array();
		$row_data = array();

		switch ($search_fields['type']) {
			case 'full':
			case 'alt':
				// report data
				$report_data = $this->reports_library->calc_project_delivery($search_fields, $search_fields['type']);

				// get staff - filtered by search
				$where = array(
					'staff.accountID' => $this->auth->user->accountID
				);
				$row_data = $this->db->from('staff')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

				// get bookings (with data)
				$bookingIDs = array(
					-1 // needs at least 1 value to ensure query works
				);
				if ($search_fields['type'] == 'alt') {
					foreach ($report_data as $bookingID => $activities) {
						$bookingIDs[$bookingID] = $bookingID;
					}
				} else {
					foreach ($report_data as $staffID => $staff_bookings) {
						// if search for single staff member, only show projects for that staff member
						if (!empty($search_fields['staff_id']) && $staffID != $search_fields['staff_id']) {
							continue;
						}
						foreach ($staff_bookings as $bookingID => $length) {
							$bookingIDs[$bookingID] = $bookingID;
						}
					}
				}
				$where = array(
					'bookings.accountID' => $this->auth->user->accountID
				);
				$res = $this->db->select('bookings.bookingID, bookings.startDate, bookings.endDate, bookings.name, project_codes.code')->from('bookings')->join('project_codes', 'bookings.project_codeID = project_codes.codeID', 'left')->where($where)->where_in('bookings.bookingID', $bookingIDs)->order_by('bookings.startDate asc, bookings.endDate asc, bookings.name asc')->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$label = $row->name . ' (' . mysql_to_uk_date($row->startDate);
						if ($row->startDate != $row->endDate) {
							$label .= '-' . mysql_to_uk_date($row->endDate);
						}
						$label .= ')';

						$bookings[$row->bookingID] = array(
							'label' => $label,
							'code' => $row->code
						);
					}
				}
				break;
			case 'project-type':
				// report data
				$report_data = $this->reports_library->calc_project_delivery($search_fields, $search_fields['type']);

				// get project types
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$row_data = $this->db->select('typeID as id, name')->from('project_types')->where($where)->order_by('name asc')->get();
				break;
			case 'session-type':
				// report data
				$report_data = $this->reports_library->calc_project_delivery($search_fields, $search_fields['type']);

				// get session types
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$row_data = $this->db->select('typeID as id, name')->from('lesson_types')->where($where)->order_by('name asc')->get();
				break;
			case 'activity-type':
				// report data
				$report_data = $this->reports_library->calc_project_delivery($search_fields, $search_fields['type']);

				// get project types
				$where = array(
					'accountID' => $this->auth->user->accountID
				);
				$row_data = $this->db->select('activityID as id, name')->from('activities')->where($where)->order_by('name asc')->get();
				break;
		}

		// staff list
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// project codes
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$project_codes = $this->db->from('project_codes')->where($where)->order_by('code asc')->get();

		// work out how many days shown
		$date_from = new DateTime(uk_to_mysql_date($search_fields['date_from']));
		$date_to = new DateTime(uk_to_mysql_date($search_fields['date_to']));
		$days = intval($date_to->diff($date_from)->format("%a") + 1);

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'row_data' => $row_data,
			'report_data' => $report_data,
			'bookings' => $bookings,
			'staff_list' => $staff_list,
			'project_codes' => $project_codes,
			'page_base' => $page_base,
			'days' => $days,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');
			switch ($search_fields['type']) {
				case 'full':
					$view = 'reports/projects-export-full';
					break;
				case 'alt':
					$view = 'reports/projects-export-alt';
					break;
				default:
					$view = 'reports/projects-export';
					break;
			}
			$this->load->view($view, $data);
		} else {
			$this->crm_view('reports/projects', $data);
		}
	}

}

/* End of file projects.php */
/* Location: ./application/controllers/reports/projects.php */
