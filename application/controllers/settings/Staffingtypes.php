<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Staffingtypes extends MY_Controller {
	private $original_types = array();
	private $required_staff_for_sessions;

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
		$this->original_types = $this->settings_library->staffing_types_defaults;
		$this->required_staff_for_sessions = $this->settings_library->staffing_types_required_for_sessions;
	}

	/**
	 * show list of staffingtypes
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'cog';
		$current_page = 'staffingtypes';
		$page_base = 'settings/staffingtypes';
		$section = 'settings';
		$title = 'Staffing Types';
		$buttons = NULL;
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
		);

		// set where
		$where = array(
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$res = $this->db->from('staffing_types')->where($where)->get();

		$type_labels = array();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$type_labels[$row->type] = $row->name;
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
			'type_labels' => $type_labels,
			'original_types' => $this->original_types,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/staffingtypes', $data);
	}

	/**
	 * edit a type
	 * @param  string $type
	 * @return void
	 */
	public function edit($type = NULL)
	{

		if (empty($type) || !array_key_exists($type, $this->original_types)) {
			return show_404();
		}

		$type_info = FALSE;

		// check for existing overwrite
		$where = array(
			'type' => $type,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staffing_types')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $row) {
				$type_info = $row;
			}
		}


		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$submit_to = 'settings/staffingtypes/edit/' . $type;
		$title = $this->original_types[$type];
		if ($type_info != FALSE && !empty($type_info->name)) {
			$title = $type_info->name;
		}
		$return_to = 'settings/staffingtypes';
		$icon = 'cog';
		$current_page = 'staffingtypes';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/staffingtypes' => 'Staffing Types'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('staff_required', 'Include in Staffing Requirements', 'trim|xss_clean');
			$this->form_validation->set_rules('staff_display_on_payroll', 'Include on Timesheets', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'required_for_session' => set_value('staff_required'),
					'display_on_payroll' => set_value('staff_display_on_payroll'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($type_info == FALSE) {
					$data['type'] = $type;
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($type_info == FALSE) {
						// insert
						$query = $this->db->insert('staffing_types', $data);
					} else {
						$where = array(
							'type' => $type,
							'accountID' => $this->auth->user->accountID
						);

						// update
						$query = $this->db->update('staffing_types', $data, $where);
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
			'type_info' => $type_info,
			'original_types' => $this->original_types,
			'required_staff_for_sessions' => $this->required_staff_for_sessions,
			'type' => $type,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/staffingtype', $data);
	}

}

/* End of file staffingtypes.php */
/* Location: ./application/controllers/settings/staffingtypes.php */
