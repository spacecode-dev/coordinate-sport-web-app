<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Organisationtypes extends MY_Controller {

	public function __construct() {
		// directors and management only - feature access checked later on as can access default settings via accounts
		parent::__construct(FALSE, array(), array('directors', 'management'));
	}

	/**
	 * edit
	 * @param  int $org_typeID
	 * @return void
	 */
	public function edit($org_typeID = NULL)
	{

		$org_info = new stdClass;

		// check if editing
		if ($org_typeID != NULL) {

			// check if numeric
			if (!ctype_digit($org_typeID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'org_typeID' => $org_typeID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('settings_customer_types')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$org_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Customer Type';
		$submit_to = 'settings/customers/type/new/';
		$return_to = 'settings/listing/general/customers_general';
		if ($org_typeID != NULL) {
			$title = $org_info->name;
			$submit_to = 'settings/customers/type//edit/' . $org_typeID;
		}
		$icon = 'book';
		$tab = 'details';
		$current_page = 'general';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/listing/general' => 'Settings',
			'settings/listing/general/customers_general' => 'Customers'
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
					'accountID' => $this->auth->user->accountID,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);

				// if new
				if ($org_typeID === NULL) {
					$data['created'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($org_typeID == NULL) {
						// insert id
						$query = $this->db->insert('settings_customer_types', $data);
					} else {
						$where = array(
							'org_typeID' => $org_typeID
						);

						// update
						$query = $this->db->update('settings_customer_types', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						$success_message = ' has been created successfully.';
						if ($org_typeID == NULL) {
							$success_message = ' has been updated successfully.';
						}
						$this->session->set_flashdata('success', set_value('name') . $success_message);

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
			'org_info' => $org_info,
			'org_typeID' => $org_typeID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/customer_org_type', $data);
	}

	/**
	 * Active/Inactive Org Customer Type
	 * @param  int $regionID
	 * @return mixed
	 */
	public function active($org_typeID, $active) {
		$active = $active == '1' ? 1 : 0;
		$data = array(
			'active' => $active
		);
		$where = array(
			'org_typeID' => $org_typeID,
			'accountID' => $this->auth->user->accountID
		);

		$query = $this->db->update('settings_customer_types', $data, $where);

		echo 'OK';
	}

	/**
	 * delete Or Customer Type
	 * @param  int $regionID
	 * @return mixed
	 */
	public function remove($org_typeID = NULL) {

		// check params
		if (empty($org_typeID)) {
			show_404();
		}

		$where = array(
			'org_typeID' => $org_typeID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_customer_types')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_customer_types', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $org_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $org_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/listing/general/customers_general';

			redirect($redirect_to);
		}
	}

}

/* End of file Main.php */
/* Location: ./application/controllers/settings/Main.php */
