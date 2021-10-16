<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Childcarevoucherproviders extends MY_Controller {

	public function __construct() {
		// directors and management only
		parent::__construct(FALSE, array(), array('directors', 'management'), array('settings'));
	}

	/**
	 * show list of childcare voucher providers
	 * @return void
	 */
	public function index() {

		// set defaults
		$icon = 'credit-card';
		$current_page = 'vouchers_childcare';
		$page_base = 'settings/childcarevoucherproviders';
		$section = 'settings';
		$title = 'Childcare Voucher Providers';
		$buttons = '<a class="btn btn-success" href="' . site_url('settings/childcarevoucherproviders/new') . '"><i class="far fa-plus"></i> Create New</a>';
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
			'name' => NULL,
			'reference' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_reference', 'Reference', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['reference'] = set_value('search_reference');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-childcarevoucherproviders'))) {

			foreach ($this->session->userdata('search-childcarevoucherproviders') as $key => $value) {
				$search_fields[$key] = $value;
			}


			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-childcarevoucherproviders', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['reference'] != '') {
				$search_where[] = "`reference` LIKE '%" . $this->db->escape_like_str($search_fields['reference']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('settings_childcarevoucherproviders')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('settings_childcarevoucherproviders')->where($where)->where($search_where, NULL, FALSE)->order_by('name asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'providers' => $res,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/childcarevoucherproviders', $data);
	}

	/**
	 * edit a provider
	 * @param  int $providerID
	 * @return void
	 */
	public function edit($providerID = NULL)
	{

		$provider_info = new stdClass();

		// check if editing
		if ($providerID != NULL) {

			// check if numeric
			if (!ctype_digit($providerID)) {
				show_404();
			}

			// if so, check exists
			$where = array(
				'providerID' => $providerID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$provider_info = $row;
			}

		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Provider';
		if ($providerID != NULL) {
			$submit_to = 'settings/childcarevoucherproviders/edit/' . $providerID;
			$title = $provider_info->name;
		} else {
			$submit_to = 'settings/childcarevoucherproviders/new/';
		}
		$return_to = 'settings/childcarevoucherproviders';
		$icon = 'creditcard';
		$current_page = 'vouchers_childcare';
		$section = 'settings';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'settings/general' => 'Settings',
			'settings/childcarevoucherproviders' => 'Childcare Voucher Providers'
		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('reference', 'Reference', 'trim|xss_clean|required');
			$this->form_validation->set_rules('comment', 'Comment', 'trim|xss_clean');
			$this->form_validation->set_rules('information', 'Voucher Information Notice', 'trim');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'reference' => set_value('reference'),
					'comment' => set_value('comment'),
					'information' => $this->input->post('information', FALSE),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($providerID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($providerID == NULL) {
						// insert
						$query = $this->db->insert('settings_childcarevoucherproviders', $data);

						$providerID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'providerID' => $providerID
						);

						// update
						$query = $this->db->update('settings_childcarevoucherproviders', $data, $where);
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
			'provider_info' => $provider_info,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('settings/childcarevoucherprovider', $data);
	}

	/**
	 * delete a provider
	 * @param  int $providerID
	 * @return mixed
	 */
	public function remove($providerID = NULL) {

		// check params
		if (empty($providerID)) {
			show_404();
		}

		$where = array(
			'providerID' => $providerID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$provider_info = $row;

			// all ok, delete
			$query = $this->db->delete('settings_childcarevoucherproviders', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $provider_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $provider_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/childcarevoucherproviders';

			redirect($redirect_to);
		}
	}

	/**
	 * activate a provider
	 * @param  int $providerID
	 * @return mixed
	 */
	public function activate($providerID = NULL) {

		// check params
		if (empty($providerID)) {
			show_404();
		}

		$where = array(
			'providerID' => $providerID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$provider_info = $row;

			// all ok, update
			$data= array(
				'active' => 1,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('settings_childcarevoucherproviders', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $provider_info->name . ' has been marked as active.');
			} else {
				$this->session->set_flashdata('error', $provider_info->name . ' could not be marked as active.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/childcarevoucherproviders';

			redirect($redirect_to);
		}
	}

	/**
	 * deactivate a provider
	 * @param  int $providerID
	 * @return mixed
	 */
	public function deactivate($providerID = NULL) {

		// check params
		if (empty($providerID)) {
			show_404();
		}

		$where = array(
			'providerID' => $providerID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$provider_info = $row;

			// all ok, update
			$data= array(
				'active' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$query = $this->db->update('settings_childcarevoucherproviders', $data, $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $provider_info->name . ' has been marked as inactive.');
			} else {
				$this->session->set_flashdata('error', $provider_info->name . ' could not be marked as inactive.');
			}

			// determine which page to send the user back to
			$redirect_to = 'settings/childcarevoucherproviders';

			redirect($redirect_to);
		}
	}

}

/* End of file childcarevoucherproviders.php */
/* Location: ./application/controllers/settings/childcarevoucherproviders.php */
