<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Id extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach + office
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'), array(), array('staff_management', 'staff_id'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * edit id
	 * @param  int $staffID
	 * @return void
	 */
	public function index($staffID = NULL)
	{

		$staff_info = new stdClass;

		// check
		if ($staffID == NULL) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($staffID)) {
			show_404();
		}

		// if so, check user exists
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

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'Coach ID';
		$submit_to = 'staff/id/' . $staffID;
		$return_to = $submit_to;
		$buttons = '<a class="btn btn-primary" href="' . site_url('staff/id/' . $staffID . '/print') . '" target="_blank"><i class="far fa-print"></i> Print</a>';
		$icon = 'passport';
		$tab = 'id';
		$current_page = 'staff';
		$section = 'staff';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('id_personalStatement', 'Personal Statement', 'trim|xss_clean|required');
			$this->form_validation->set_rules('id_specialism', 'Specialism', 'trim|xss_clean|required');
			$this->form_validation->set_rules('id_favQuote', 'Favourite Quote', 'trim|xss_clean|required');
			$this->form_validation->set_rules('id_sportingHero', 'Sporting Hero', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'id_personalStatement' => $this->input->post('id_personalStatement'),
					'id_specialism' => set_value('id_specialism'),
					'id_favQuote' => set_value('id_favQuote'),
					'id_sportingHero' => set_value('id_sportingHero'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$upload_res = $this->crm_library->handle_upload();

				if ($upload_res !== NULL) {
					$data['id_photo_name'] = $upload_res['client_name'];
					$data['id_photo_path'] = $upload_res['raw_name'];
					$data['id_photo_type'] = $upload_res['file_type'];
					$data['id_photo_size'] = $upload_res['file_size']*1024;
					$data['id_photo_ext'] = substr($upload_res['file_ext'], 1);

					if (!empty($staff_info->id_photo_path)) {
						// delete previous file, if exists
						$path = UPLOADPATH;
						if (file_exists($path . $staff_info->id_photo_path)) {
							unlink($path . $staff_info->id_photo_path);
						}
					}
				}

				// final check for errors
				if (count($errors) == 0) {

					$where = array(
						'staffID' => $staffID,
						'accountID' => $this->auth->user->accountID
					);

					// update
					$query = $this->db->update('staff', $data, $where);

					// if updated
					if ($this->db->affected_rows() == 1) {

						$this->session->set_flashdata('success', 'Coach ID has been updated successfully.');

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/id', $data);
	}

	/**
	 * print id
	 * @param  int $staffID
	 * @return void
	 */
	public function print_id($staffID = NULL)
	{

		$staff_info = new stdClass;

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

		// match
		foreach ($query->result() as $row) {
			$staff_info = $row;
		}

		// quals
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$quals = $this->db->from('staff_quals')->where($where)->where("(`expiry_date` > '". mdate('%Y-%m-%d %H:%i:%s') . "' || `expiry_date` IS NULL)", NULL, FALSE)->order_by('name asc')->get();

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// prepare data for view
		$data = array(
			'staff_info' => $staff_info,
			'quals' => $quals,
			'brands' => $brands
		);

		// load view
		$this->load->view('staff/id-print', $data);
	}

}

/* End of file id.php */
/* Location: ./application/controllers/staff/id.php */
