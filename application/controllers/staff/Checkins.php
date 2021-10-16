<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checkins extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('staff_management'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * edit quals
	 * @param  int $staffID
	 * @return void
	 */
	public function index($staffID = NULL)
	{
		if (!$this->auth->has_features('lesson_checkins')) {
			show_404();
		}

		$this->load->library('crm_library');

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check exists
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		$staff_info = [];
		foreach ($query->result() as $item) {
			$staff_info = $item;
		}

		if ($staff_info->staffID != $staffID) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Checkins';
		$submit_to = 'staff/checkins/' . $staffID;
		$return_to = $submit_to;
		$buttons = NULL;
		$icon = 'map-marker-alt';
		$tab = 'checkins';
		$current_page = 'staff';
		$section = 'staff';
		$error = array();
		$success = NULL;
		$info = NULL;
		$page_base = '/staff/checkins/' . $staffID;
		$breadcrumb_levels = [
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
		];

		$where = array(
			'bookings_lessons_checkins.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => $staffID,
			'is_active' => 'yes',
			'search' => NULL
		);
		$is_search = TRUE;

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
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
		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-checkins-staff'))) {

			foreach ($this->session->userdata('search-checkins-staff') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}


		// if dates empty, add default
		if (empty($search_fields['date_from'])) {
			$search_fields['date_from'] = date('d/m/Y');
		}
		if (empty($search_fields['date_to'])) {
			$search_fields['date_to'] = date('d/m/Y');
		}

		// if from after to, reset
		if (strtotime(uk_to_mysql_date($search_fields['date_from'])) > strtotime(uk_to_mysql_date($search_fields['date_to']))) {
			$search_fields['date_from'] = $search_fields['date_to'];
		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-checkins-staff', $search_fields);

			if ($search_fields['date_from'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` >= " . $this->db->escape(uk_to_mysql_date($search_fields['date_from']));
			}

			if ($search_fields['date_to'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("bookings_lessons_checkins") . "`.`date` <= " . $this->db->escape(uk_to_mysql_date($search_fields['date_to']));
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

		}


		if (array_key_exists('is_active', $search_where)) {
			$search_where[] = $search_where['is_active'];
			unset($search_where['is_active']);
		}

		if (count($search_where) > 0) {
			$search_where = '(' . implode(' AND ', $search_where) . ')';
		}

		$markers = $this->crm_library->get_checkins(
			$where,
			$search_where,
			$search_fields,
			true);
		$markers = array_values($markers);

		$markers = $this->crm_library->prepare_markers($markers, [
			'date_from' => uk_to_mysql_date($search_fields['date_from']),
			'date_to' => uk_to_mysql_date($search_fields['date_to'])
		]);

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

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
			'markers' => $markers,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'staffID' => $staffID,
			'tab' => $tab
		);

		// load view
		$this->crm_view('staff/checkins', $data);
	}

}

/* End of file quals.php */
/* Location: ./application/controllers/staff/quals.php */
