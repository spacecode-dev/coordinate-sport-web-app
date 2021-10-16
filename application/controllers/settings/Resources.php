<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Resources extends MY_Controller {
	private $permission_levels;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
		$this->_set_permission_levels();
	}

	private function _set_permission_levels(){
		$permission_levels=array(
			'directors' => NULL,
			'management' => NULL,
			'office' => NULL,
			'headcoach' => NULL,
			'fulltimecoach' => NULL,
			'coaching' => NULL
		);
		foreach ($permission_levels as $key => $label) {
			$this->permission_levels[$key] = $this->settings_library->get_permission_level_label($key, TRUE);
		}
	}

	/**
	 * show list of resources
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'resources';
		$page_base = 'settings/resources';
		$section = 'settings';
		$title = 'Resources';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/resources/new') . '"><i class="far fa-plus"></i> Create New</a>';
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

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-resources'))) {

			foreach ($this->session->userdata('search-resources') as $key => $value) {
				$search_fields[$key] = $value;
			}
			$is_search = TRUE;
		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-resources', $search_fields);

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
		$res = $this->db->from('settings_resources')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('permissionlevel asc')
		->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('settings_resources')
		->where($where)
		->where($search_where, NULL, FALSE)
		->order_by('permissionlevel asc')
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->get();

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
			'resources' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/resources', $data);
	}

	/**
	 * edit a resource
	 * @param  int $resourceID
	 * @return void
	 */
	public function edit($resourceID = NULL)
	{
		$category_info = new stdClass();

		// check if editing
		if ($resourceID != NULL) {

			// check if numeric
			if (!ctype_digit($resourceID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'resourceID' => $resourceID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->select('settings_resources.*')->from('settings_resources')->where($where)->limit(1)->get();
			$str = $this->db->last_query();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$category_info= $row;
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Resource';
		if ($resourceID != NULL) {
			$submit_to = 'settings/resources/edit/' . $resourceID;
			$title = $category_info->name;
		} else {
			$submit_to = 'settings/resources/new/';
		}
		$return_to = 'settings/resources';
		$icon = 'cog';
		$current_page = 'resources';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/resources' => 'Resources'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'A name for the resource', 'trim|xss_clean|required');
			$this->form_validation->set_rules('permission_level', 'A permission level for the resource', 'trim|xss_clean|required');
			$this->form_validation->set_rules('policies', 'Show on Dashboard Policies', 'trim|xss_clean|intval');
			$this->form_validation->set_rules('customer_attachments', 'Show in Customer Attachments Templates', 'trim|xss_clean|intval');
			$this->form_validation->set_rules('booking_attachments', 'Show in Booking Attachments Templates', 'trim|xss_clean|intval');
			$this->form_validation->set_rules('session_attachments', 'Show in Session Attachments Templates', 'trim|xss_clean|intval');
			$this->form_validation->set_rules('staff_attachments', 'Show in Staff Attachments Templates', 'trim|xss_clean|intval');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {
				// all ok, prepare data

				$data = array(
					'name' => set_value('name'),
					'permissionLevel'=> set_value('permission_level'),
					'policies' => intval(set_value('policies')),
					'customer_attachments' => intval(set_value('customer_attachments')),
					'booking_attachments' => intval(set_value('booking_attachments')),
					'session_attachments' => intval(set_value('session_attachments')),
					'staff_attachments' => intval(set_value('staff_attachments')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($resourceID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($resourceID == NULL) {
						// insert
						$query = $this->db->insert('settings_resources', $data);
						$resourceID = $this->db->insert_id();
						$just_added = TRUE;
					} else {
						$where = array(
							'resourceID' => $resourceID
						);
						// update
						$query = $this->db->update('settings_resources', $data, $where);
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
			'category_info'=>$category_info,
			'permission_levels' => $this->permission_levels,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/resource', $data);
	}

	/**
	 * delete a resource
	 * @param  int $resourceID
	 * @return mixed
	 */
	public function remove($resourceID = NULL) {

		// check params
		if (empty($resourceID)) {
			show_404();
		}

		$where = array(
			'resourceID' => $resourceID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_resources')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$resource_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_resources', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $resource_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $resource_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/resources';

			redirect($redirect_to);
		}
	}

	/**
	 * toggle active status
	 * @param  int $resourceID
	 * @param string $value
	 * @return mixed
	 */
	public function active($resourceID = NULL, $value = NULL) {
		// check params
		if (empty($resourceID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'resourceID' => $resourceID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_resources')->where($where)->limit(1)->get();

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
			$query = $this->db->update('settings_resources', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}
	}

}

/* End of file resources.php */
/* Location: ./application/controllers/settings/resources.php */
