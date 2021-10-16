<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projecttypes extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of project_types
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'projecttypes';
		$page_base = 'settings/projecttypes';
		$section = 'settings';
		$title = 'Project Types';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/projecttypes/new') . '"><i class="far fa-plus"></i> Create New</a>';
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
		$search_where = array();
		$search_fields = array(
			'search' => NULL,
			'name' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-project_types'))) {

			foreach ($this->session->userdata('search-project_types') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-project_types', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('project_types')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('project_types')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'project_types' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/projecttypes', $data);
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
			$query = $this->db->from('project_types')->where($where)->limit(1)->get();

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
		$title = 'New Project Type';
		if ($typeID != NULL) {
			$submit_to = 'settings/projecttypes/edit/' . $typeID;
			$title = $type_info->name;
		} else {
			$submit_to = 'settings/projecttypes/new/';
		}
		$return_to = 'settings/projecttypes';
		$icon = 'cog';
		$current_page = 'projecttypes';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/projecttypes' => 'Project Types'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('exclude_from_participant_booking_lists', 'Do not show on participant booking course list', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'exclude_from_participant_booking_lists' => intval(set_value('exclude_from_participant_booking_lists')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($typeID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($typeID == NULL) {
						// insert
						$query = $this->db->insert('project_types', $data);

						$typeID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'typeID' => $typeID
						);

						// update
						$query = $this->db->update('project_types', $data, $where);
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
		$this->crm_view('settings/projecttype', $data);
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
		$query = $this->db->from('project_types')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$type_info = $row;

			// all ok, delete
			$query = $this->db->delete('project_types', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $type_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $type_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/projecttypes';

			redirect($redirect_to);
		}
	}
}

/* End of file project_types.php */
/* Location: ./application/controllers/settings/projecttypes.php */
