<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Performance extends MY_Controller {

	private $categories;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('reports', 'staff_performance'));

		// load library
		$this->load->library('reports_library');
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'book';
		$current_page = 'performance';
		$section = 'reports';
		$page_base = 'reports/performance';
		$title = 'Staff Performance';
		$buttons = ' <a class="btn btn-primary" href="' . site_url('reports/performance/export') . '" target="_blank"><i class="far fa-save"></i> Export</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$export = FALSE;
		$period = 'quarter';

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

		// columns
		$columns = array(
			'timetable' => 'Confirming Timetable',
			'sessionevaluations' => 'Session Evaluations',
			'pupilassessment' => 'Pupil Assessments',
			'observation' => 'Observation Scores',
			'sickness' => 'Sickness',
			'late' => 'Lateness',
			'feedback' => 'Feedback',
			'total' => 'Total'
		);

		// set up search
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'brand_id' => NULL,
			'teamleader_id' => NULL,
			'is_active' => 'yes',
			'exclude_non_delivery' => 'yes',
			'order_by' => 'performance',
			'search' => NULL
		);
		$is_search = FALSE;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_brand_id', $this->settings_library->get_label('brand'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_teamleader_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search_exclude_non_delivery', 'Exclude non-delivery staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search_order_by', 'Order By', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['brand_id'] = set_value('search_brand_id');
			$search_fields['teamleader_id'] = set_value('search_teamleader_id');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['exclude_non_delivery'] = set_value('search_exclude_non_delivery');
			$search_fields['order_by'] = set_value('search_order_by');
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

		// look up brand settings, if set
		if (isset($search_fields['brand_id']) && $search_fields['brand_id'] != '') {
			$where = array(
				'accountID' => $this->auth->user->accountID,
				'brandID' => $search_fields['brand_id']
			);
			$brand_res = $this->db->from('brands')->where($where)->limit(1)->get();
			if ($brand_res->num_rows() > 0) {
				foreach ($brand_res->result() as $row) {
					if ($row->staff_performance_exclude_session_evaluations == 1 && array_key_exists('sessionevaluations', $columns)) {
						unset($columns['sessionevaluations']);
					}
					if ($row->staff_performance_exclude_pupil_assessments == 1 && array_key_exists('pupilassessment', $columns)) {
						unset($columns['pupilassessment']);
					}
				}
			}
		}

		// calc offset
		switch ($period) {
			case 'week':
				$offset = '-6 days';
				break;
			case 'month':
				$offset = '-1 month';
				break;
			case 'quarter':
			default:
				$offset = '-12 weeks';
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

			// store search fields
			$this->session->set_userdata('search-reports', $search_fields);

		}

		if ($search_fields['staff_id'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("staff") . "`.`staffID` = " . $this->db->escape($search_fields['staff_id']);
		}

		if ($search_fields['brand_id'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("staff") . "`.`brandID` = " . $this->db->escape($search_fields['brand_id']);
		}

		if ($search_fields['teamleader_id'] != '') {
			$search_where[] = '`' . $this->db->dbprefix("staff_recruitment_approvers") . "`.`approverID` = " . $this->db->escape($search_fields['teamleader_id']);
		}

		if (isset($search_fields['exclude_non_delivery']) && $search_fields['exclude_non_delivery'] == 'yes') {
			$search_where[] = '`' . $this->db->dbprefix("staff") . "`.`non_delivery` != 1";
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

		$performance_data = $this->reports_library->calc_performance('all', $search_fields);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get staff
		$where = array(
			'staff.accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->select('staff.staffID, staff.first, staff.surname, team_leaders.first as leader_first, team_leaders.surname as leader_last, brands.name as brand')->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.approverID = staff.staffID', 'left')
		->join('staff as team_leaders', 'staff_recruitment_approvers.approverID = team_leaders.staffID', 'left')
		->join('brands', 'staff.brandID = brands.brandID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('staff.first asc, staff.surname asc')->get();

		// process data
		$staff_data = array();
		foreach ($staff->result() as $row) {
			if (!isset($performance_data[$row->staffID])) {
				continue;
			}
			$staff_row = array(
				'name' => $row->first . ' ' . $row->surname,
				'brand' => $row->brand,
				'team_leader' => trim($row->leader_first . ' ' . $row->leader_last),
			);
			foreach ($columns as $key => $label) {
				$val = 0;
				if (isset($performance_data[$row->staffID][$key])) {
					$val = $performance_data[$row->staffID][$key];
				}
				$staff_row[$key] = intval($val);
			}
			$staff_data[$row->staffID] = $staff_row;
		}
		if ($search_fields['order_by'] == 'performance') {
			uasort($staff_data, function($a, $b) {
				return $a['total'] < $b['total'];
			});
		}

		// staff list
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff_list = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// team leaders
		$where = array(
			'active' => 1,
			'department' => 'headcoach',
			'accountID' => $this->auth->user->accountID
		);
		$team_leaders = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// work out how many days shown
		$date_from = new DateTime(uk_to_mysql_date($search_fields['date_from']));
		$date_to = new DateTime(uk_to_mysql_date($search_fields['date_to']));
		$days = intval($date_to->diff($date_from)->format("%a") + 1);

		// hide export if none
		if ($staff->num_rows() == 0 || count($performance_data) == 0) {
			$buttons = NULL;
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'staff' => $staff,
			'performance_data' => $performance_data,
			'columns' => $columns,
			'staff_list' => $staff_list,
			'team_leaders' => $team_leaders,
			'brands' => $brands,
			'page_base' => $page_base,
			'days' => $days,
			'staff_data' => $staff_data,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		if ($export === TRUE) {
			//load csv helper
			$this->load->helper('csv_helper');

			$this->load->view('reports/performance-export', $data);
		} else {
			$this->crm_view('reports/performance', $data);
		}
	}

}

/* End of file performance.php */
/* Location: ./application/controllers/reports/performance.php */
