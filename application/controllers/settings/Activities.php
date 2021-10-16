<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Activities extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of activities
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'activities';
		$page_base = 'settings/activities';
		$section = 'settings';
		$title = 'Activities';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/activities/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings'
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where['is_active'] = "`active` = 1";
		$search_fields = array(
			'search' => NULL,
			'name' => NULL,
			'is_active' => 'yes'
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_is_active', 'Active', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['is_active'] = set_value('search_is_active');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-activities'))) {

			foreach ($this->session->userdata('search-activities') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-activities', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['is_active'] != '') {
				if ($search_fields['is_active'] == 'yes') {
					$search_where['is_active'] = '`active` = 1';
				} else {
					$search_where['is_active'] = '`active` != 1';
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

		// run query
		$res = $this->db->from('activities')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('activities')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'activities' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/activities', $data);
	}

	/**
	 * edit an activity
	 * @param  int $activityID
	 * @return void
	 */
	public function edit($activityID = NULL)
	{

		$activity_info = new stdClass();

		// check if editing
		if ($activityID != NULL) {

			// check if numeric
			if (!ctype_digit($activityID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'activityID' => $activityID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('activities')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$activity_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Activity';
		if ($activityID != NULL) {
			$submit_to = 'settings/activities/edit/' . $activityID;
			$title = $activity_info->name;
		} else {
			$submit_to = 'settings/activities/new/';
		}
		$return_to = 'settings/activities';
		$icon = 'cog';
		$current_page = 'activities';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/actvities' => 'Activties'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			if ($this->auth->has_features('online_booking')) {
				$this->form_validation->set_rules('exclude_online_booking_search', 'Hide from Search Dropdown on Bookings Site', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'exclude_online_booking_search' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($this->auth->has_features('online_booking') && set_value('exclude_online_booking_search') == 1) {
					$data['exclude_online_booking_search'] = 1;
				}

				if ($activityID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($activityID == NULL) {
						// insert
						$query = $this->db->insert('activities', $data);

						$activityID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'activityID' => $activityID
						);

						// update
						$query = $this->db->update('activities', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if (isset($just_added)) {
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
						}

						redirect($return_to);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
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
			'activity_info' => $activity_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/activity', $data);
	}

	/**
	 * delete an activity
	 * @param  int $activityID
	 * @return mixed
	 */
	public function remove($activityID = NULL) {

		// check params
		if (empty($activityID)) {
			show_404();
		}

		$where = array(
			'activityID' => $activityID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('activities')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$activity_info = $row;

			// all ok, delete
			$query = $this->db->delete('activities', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $activity_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $activity_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/activities';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle active status
	 * @param  int $activityID
	 * @param string $value
	 * @return mixed
	 */
	public function active($activityID = NULL, $value = NULL) {

		// check params
		if (empty($activityID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'activityID' => $activityID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('activities')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$type_info = $row;

			$data = array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['active'] = 1;
			}

			// run query
			$query = $this->db->update('activities', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}
}

/* End of file activities.php */
/* Location: ./application/controllers/settings/activities.php */
