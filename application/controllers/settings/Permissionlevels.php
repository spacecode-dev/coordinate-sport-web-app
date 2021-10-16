<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permissionlevels extends MY_Controller {
	private $original_levels = array(
		'directors' => NULL,
		'management' => NULL,
		'office' => NULL,
		'headcoach' => NULL,
		'fulltimecoach' => NULL,
		'coaching' => NULL
	);

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));

		// add labels
		foreach ($this->original_levels as $key => $label) {
			$this->original_levels[$key] = $this->settings_library->get_permission_level_label($key, TRUE);
		}
	}

	/**
	 * show list of permissionlevels
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'permissionlevels';
		$page_base = 'settings/permissionlevels';
		$section = 'settings';
		$title = 'Permission Levels';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'department !=' => 'directors' // dont allow custom names for super user
		);

		// run query
		$res = $this->db->from('permission_levels')->where($where)->get();

		$level_labels = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$level_labels[$row->department] = $row->name;
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

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'page_base' => $page_base,
			'level_labels' => $level_labels,
			'original_levels' => $this->original_levels,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/permissionlevels', $data);
	}

	/**
	 * edit a department
	 * @param  string $department
	 * @return void
	 */
	public function edit($department = NULL)
	{

		if (empty($department) || !array_key_exists($department, $this->original_levels)) {
			return show_404();
		}

		// dont allow editing of super user
		if ($department == 'directors') {
			show_404();
		}

		$level_info = FALSE;

		// check for existing overwrite
		$where = array(
			'department' => $department,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('permission_levels')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$level_info = $row;
			}
		}


		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$submit_to = 'settings/permissionlevels/edit/' . $department;
		$title = $this->original_levels[$department];
		if ($level_info != FALSE && !empty($level_info->name)) {
			$title = $level_info->name;
		}
		$return_to = 'settings/permissionlevels';
		$icon = 'cog';
		$current_page = 'permissionlevels';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/permissionlevels' => 'Permission Levels'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($level_info == FALSE) {
					$data['department'] = $department;
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($level_info == FALSE) {
						// insert
						$query = $this->db->insert('permission_levels', $data);
					} else {
						$where = array(
							'department' => $department,
							'accountID' => $this->auth->user->accountID
						);

						// update
						$query = $this->db->update('permission_levels', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
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
			'level_info' => $level_info,
			'original_levels' => $this->original_levels,
			'department' => $department,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/permissionlevel', $data);
	}

}

/* End of file permissionlevels.php */
/* Location: ./application/controllers/settings/permissionlevels.php */
