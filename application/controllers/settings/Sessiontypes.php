<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sessiontypes extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of session types
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'sessiontypes';
		$page_base = 'settings/sessiontypes';
		$section = 'settings';
		$title = 'Session Types';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/sessiontypes/new') . '"><i class="far fa-plus"></i> Create New</a>';
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-sessiontypes'))) {

			foreach ($this->session->userdata('search-sessiontypes') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-sessiontypes', $search_fields);

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
		$res = $this->db->from('lesson_types')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('lesson_types')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'sessiontypes' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/sessiontypes', $data);
	}

	/**
	 * edit a type
	 * @param  int $typeID
	 * @return void
	 */
	public function edit($typeID = NULL)
	{

		$type_info = new stdClass();

		// check if editing
		if ($typeID != NULL) {

			// check if numeric
			if (!ctype_digit($typeID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'typeID' => $typeID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('lesson_types')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$type_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Session Type';
		if ($typeID != NULL) {
			$submit_to = 'settings/sessiontypes/edit/' . $typeID;
			$title = $type_info->name;
		} else {
			$submit_to = 'settings/sessiontypes/new/';
		}
		$return_to = 'settings/sessiontypes';
		$icon = 'cog';
		$current_page = 'sessiontypes';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/sessiontypes' => 'Session Types'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('colour', 'Colour', 'trim|xss_clean');
			$this->form_validation->set_rules('show_dashboard', 'Show on Dashboard', 'trim|xss_clean');
			$this->form_validation->set_rules('exclude_autodiscount', 'Exclude from Automatic Discount', 'trim|xss_clean');
			$this->form_validation->set_rules('show_label_register', 'Show Label on Payment Register', 'trim|xss_clean');
			if ($this->auth->has_features('session_evaluations')) {
				$this->form_validation->set_rules('session_evaluations', 'Enable Session Evaluations', 'trim|xss_clean');
			}
			if ($this->auth->has_features('online_booking')) {
				$this->form_validation->set_rules('exclude_online_booking_search', 'Hide from Search Dropdown on Bookings Site', 'trim|xss_clean');
				$this->form_validation->set_rules('exclude_online_booking_price_summary', 'Exclude from price summary on event page on online booking', 'trim|xss_clean');
				$this->form_validation->set_rules('exclude_online_booking_availability_status', 'Exclude from availability status calculation on event page on online booking', 'trim|xss_clean');
			}
			$this->form_validation->set_rules('birthday_tab', 'Birthday Tab', 'trim|xss_clean');
			if ($this->auth->has_features('timesheets')) {
				$this->form_validation->set_rules('extra_time_head', 'Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('head') . ')', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('extra_time_lead', 'Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('lead') . ')', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('extra_time_assistant', 'Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('assistant') . ')', 'trim|xss_clean|greater_than[-1]');
				$this->form_validation->set_rules('hourly_rate', 'Hourly Rate Per Session', 'trim|xss_clean|greater_than[-1]');
			}
			if ($this->auth->has_features('mileage')) {
				$this->form_validation->set_rules('exclude_mileage_session', 'Exclude from Mileage', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'colour' => NULL,
					'show_dashboard' => 0,
					'exclude_autodiscount' => 0,
					'show_label_register' => 0,
					'birthday_tab' => 0,
					'session_evaluations' => 0,
					'exclude_online_booking_search' => 0,
					'exclude_online_booking_price_summary' => 0,
					'exclude_online_booking_availability_status' => 0,
					'exclude_mileage_session' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (!empty(set_value('colour'))) {
					$data['colour'] = set_value('colour');
				}
				if (set_value('show_dashboard') == 1) {
					$data['show_dashboard'] = 1;
				}
				if (set_value('exclude_autodiscount') == 1) {
					$data['exclude_autodiscount'] = 1;
				}
				if (set_value('show_label_register') == 1) {
					$data['show_label_register'] = 1;
				}
				if (set_value('birthday_tab') == 1) {
					$data['birthday_tab'] = 1;
				}
				if ($this->auth->has_features('session_evaluations') && set_value('session_evaluations') == 1) {
					$data['session_evaluations'] = 1;
				}
				if ($this->auth->has_features('online_booking')) {
					if (set_value('exclude_online_booking_search') == 1) {
						$data['exclude_online_booking_search'] = 1;
					}
					if (set_value('exclude_online_booking_price_summary') == 1) {
						$data['exclude_online_booking_price_summary'] = 1;
					}
					if (set_value('exclude_online_booking_availability_status') == 1) {
						$data['exclude_online_booking_availability_status'] = 1;
					}
				}

				if ($this->auth->has_features('timesheets')) {
					$data['extra_time_head'] = set_value('extra_time_head');
					$data['extra_time_lead'] = set_value('extra_time_lead');
					$data['extra_time_assistant'] = set_value('extra_time_assistant');
					$data['hourly_rate'] = set_value('hourly_rate');
				}
				
				if ($this->auth->has_features('mileage')) {
					if (set_value('exclude_mileage_session') == 1) {
						$data['exclude_mileage_session'] = 1;
					}
				}

				if ($typeID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($typeID == NULL) {
						// insert
						$query = $this->db->insert('lesson_types', $data);

						$typeID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'typeID' => $typeID
						);

						// update
						$query = $this->db->update('lesson_types', $data, $where);
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
			'type_info' => $type_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/sessiontype', $data);
	}

	/**
	 * delete a type
	 * @param  int $typeID
	 * @return mixed
	 */
	public function remove($typeID = NULL) {

		// check params
		if (empty($typeID)) {
			show_404();
		}

		$where = array(
			'typeID' => $typeID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('lesson_types')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$type_info = $row;

			// all ok, delete
			$query = $this->db->delete('lesson_types', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $type_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $type_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/sessiontypes';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle active status
	 * @param  int $typeID
	 * @param string $value
	 * @return mixed
	 */
	public function active($typeID = NULL, $value = NULL) {

		// check params
		if (empty($typeID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'typeID' => $typeID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('lesson_types')->where($where)->limit(1)->get();

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
			$query = $this->db->update('lesson_types', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}
}

/* End of file sessiontypes.php */
/* Location: ./application/controllers/settings/sessiontypes.php */
