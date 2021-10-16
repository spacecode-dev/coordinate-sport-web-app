<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Checkins extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management', 'headcoach'), array('lesson_checkins'));
	}

	public function index($action = FALSE) {

		// set defaults
		$icon = 'map-marker-alt';
		$current_page = 'checkins';
		$section = 'checkins';
		$page_base = 'checkins';
		$title = 'Session Check-ins';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;

		$where = array(
			'bookings_lessons_checkins.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'staff_id' => NULL,
			'is_active' => 'yes',
			'search' => NULL,
			'status' => NULL
		);
		$is_search = TRUE;

		$view = 'map';

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Start Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'End Date', 'trim|xss_clean');
			$this->form_validation->set_rules('search_staff_id', 'Staff', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');
			$this->form_validation->set_rules('status', 'Status', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['staff_id'] = set_value('search_staff_id');
			$search_fields['search'] = set_value('search');
			$search_fields['status'] = set_value('status');
			$view = set_value('view');

			// check dates
			if (!uk_to_mysql_date($search_fields['date_from'])) {
				$search_fields['date_from'] = NULL;
			}
			if (!uk_to_mysql_date($search_fields['date_to'])) {
				$search_fields['date_to'] = NULL;
			}

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-checkins'))) {

			foreach ($this->session->userdata('search-checkins') as $key => $value) {
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
			$this->session->set_userdata('search-checkins', $search_fields);

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

		if ($this->auth->user->department == 'headcoach') {
			$search_where[] = '`' . $this->db->dbprefix("staff_recruitment_approvers") . '`.`approverID` = ' . $this->auth->user->staffID;
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

		//preparing markers to display, getting checkouts
		$markers = $this->crm_library->prepare_markers($markers, [
			'date_from' => uk_to_mysql_date($search_fields['date_from']),
			'date_to' => uk_to_mysql_date($search_fields['date_to'])
		]);

		$details_data = [];
		foreach ($markers as $key => $marker) {

			//filtering by checkin status
			if (!empty($search_fields['status'])) {
				if ($marker['colour'] != $search_fields['status']) {
					continue;
				}
			}

			$details_data[] = [
				'staff' => $marker['staff'],
				'first_lesson_org' => $marker['orgs'][0],
				'first_lesson_time' => $marker['lesson_times'][0],
				'check_in_times' => $marker['checkin_times'],
				'check_in_status' => $marker['colour'],
				'last_lesson_ord' => array_pop($marker['orgs']),
				'last_lesson_time' => array_pop($marker['lesson_times']),
				'check_out_times' => $marker['checkout_times'],
				'not_checked_in' => $marker['not_checked_in']
			];
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// staff list
		$where = array(
			'staff.active' => 1,
			'staff.accountID' => $this->auth->user->accountID
		);

		if ($this->auth->user->department == 'headcoach') {
			$where['staff_recruitment_approvers.approverID'] = $this->auth->user->staffID;
		}

		$staff_list = $this->db->select("staff.*")
		->from('staff')
		->join('staff_recruitment_approvers', 'staff_recruitment_approvers.staffID = staff.staffID', 'left')
		->where($where)
		->order_by('staff.first asc, staff.surname asc')->get();

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
			'staff_list' => $staff_list,
			'page_base' => $page_base,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'view' => $view,
			'details_data' => $details_data
		);

		// load view
		$this->crm_view('checkins/main', $data);
	}

}

/* End of file Checkins.php */
/* Location: ./application/controllers/Checkins.php */
