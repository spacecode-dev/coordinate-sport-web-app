<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Addresses extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach + head coach + office
		parent::__construct(FALSE, array('coaching', 'fulltimecoach', 'headcoach'));

		// check access if admin account
		if ($this->auth->account->admin == 1 && !in_array($this->auth->user->department, array('management', 'directors'))) {
			show_403();
		}
	}

	/**
	 * show list of addresses
	 * @return void
	 */
	public function index($staffID = NULL) {

		if ($staffID == NULL) {
			show_404();
		}

		// look up
		$where = array(
			'staffID' => $staffID,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('staff')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$staff_info = $row;
		}

		// set defaults
		$icon = 'address-card';
		$tab = 'addresses';
		$current_page = 'staff';
		$page_base = 'staff/addresses/' . $staffID;
		$section = 'staff';
		$title = 'Addresses & Contacts';
		$buttons = '<a class="btn btn-success" href="' . site_url('staff/addresses/' . $staffID . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
 		);

		// set where
		$where = array(
			'staff_addresses.staffID' => $staffID,
			'staff_addresses.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'relationship' => NULL,
			'type' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_relationship', 'Relationship', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['relationship'] = set_value('search_relationship');
			$search_fields['type'] = set_value('search_type');
			$search_fields['name'] = set_value('search_name');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-staff-addresses'))) {

			foreach ($this->session->userdata('search-staff-addresses') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-staff-addresses', $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = "`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['relationship'] != '') {
				$search_where[] = "`relationship` LIKE '%" . $this->db->escape_like_str($search_fields['relationship']) . "%'";
			}

			if ($search_fields['type'] != '') {
				$search_where[] = "`type` = " . $this->db->escape($search_fields['type']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('staff_addresses')->where($where)->where($search_where, NULL, FALSE)->order_by('type asc, address1 asc, address2 asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('staff_addresses')->where($where)->where($search_where, NULL, FALSE)->order_by('type asc, address1 asc, address2 asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'search_fields' => $search_fields,
			'page_base' => $page_base,
			'addresses' => $res,
			'staff_info' => $staff_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/addresses', $data);
	}

	/**
	 * edit an address
	 * @param  int $addressID
	 * @param int $staffID
	 * @return void
	 */
	public function edit($addressID = NULL, $staffID = NULL)
	{

		$address_info = new stdClass();

		// check if editing
		if ($addressID != NULL) {

			// check if numeric
			if (!ctype_digit($addressID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'addressID' => $addressID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('staff_addresses')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$address_info = $row;
				$staffID = $address_info->staffID;
			}

		}

		// required
		if ($staffID == NULL) {
			show_404();
		}

		// look up org
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
		$title = 'New Address';
		if ($addressID != NULL) {
			$submit_to = 'staff/addresses/edit/' . $addressID;
			$title = $address_info->address1;
		} else {
			$submit_to = 'staff/addresses/' . $staffID . '/new/';
		}
		$return_to = 'staff/addresses/' . $staffID;
		$icon = 'address-card';
		$tab = 'addresses';
		$current_page = 'staff';
		$section = 'staff';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'staff' => 'Staff',
			'staff/edit/' . $staffID => $staff_info->first . ' ' . $staff_info->surname,
			'staff/addresses/' . $staffID => 'Addresses & Contacts'
 		);

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			if ($this->input->post('type') == 'emergency') {
				$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');
				$this->form_validation->set_rules('relationship', 'Relationship', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean|required');
			$this->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean');
			$this->form_validation->set_rules('town', 'Town', 'trim|xss_clean|required');
			$this->form_validation->set_rules('county', localise('county'), 'trim|xss_clean|required');
			$this->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean|required|callback_check_postcode');
			if ($this->input->post('type') != 'emergency') {
				$this->form_validation->set_rules('fromM', 'From Month', 'trim|xss_clean|required');
				$this->form_validation->set_rules('fromY', 'From Year', 'trim|xss_clean|required');
				$this->form_validation->set_rules('toM', 'To Month', 'trim|xss_clean');
				$this->form_validation->set_rules('toY', 'To Year', 'trim|xss_clean');
			}
			if ($this->input->post('type') == 'emergency') {
				$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean|callback_phone_or_mobile[' . $this->input->post('mobile') . ']');
				$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('type') != 'main') {
					$data['type'] = set_value('type');
				}

				if (set_value('type') == 'emergency') {
					$data['name'] = set_value('name');
					$data['relationship'] = set_value('relationship');
					$data['phone'] = set_value('phone');
					$data['mobile'] = set_value('mobile');
				} else {
					// not emergency
					// work out from date for address
					$fromM = set_value('fromM');
					$fromY = set_value('fromY');
					if (!empty($fromM) && !empty($fromY)) {
						$from = $fromY . "-" . $fromM . "-1";
					} else {
						$from = NULL;
					}

					// work out to date for address
					$toM = set_value('toM');
					$toY = set_value('toY');
					if (!empty($toM) && !empty($toY)) {
						$to = $toY . "-" . $toM . "-1";
					} else {
						$to = NULL;
					}

					$data['from'] = $from;
					$data['to'] = $to;
				}

				if ($addressID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['staffID'] = $staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($addressID == NULL) {
						// insert
						$query = $this->db->insert('staff_addresses', $data);

					} else {
						$where = array(
							'addressID' => $addressID
						);

						// update
						$query = $this->db->update('staff_addresses', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						if ($staffID == NULL) {
							$this->session->set_flashdata('success', set_value('address1') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('address1') . ' has been updated successfully.');
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
			'tab' => $tab,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'submit_to' => $submit_to,
			'return_to' => $return_to,
			'address_info' => $address_info,
			'staffID' => $staffID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('staff/address', $data);
	}

	/**
	 * delete an address
	 * @param  int $staffID
	 * @return mixed
	 */
	public function remove($addressID = NULL) {

		// check params
		if (empty($addressID)) {
			show_404();
		}

		$where = array(
			'addressID' => $addressID,
			'type !=' => 'main',
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('staff_addresses')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$address_info = $row;

			// all ok, delete
			$query = $this->db->delete('staff_addresses', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $address_info->address1 . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $address_info->address1 . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'staff/addresses/' . $address_info->staffID;

			redirect($redirect_to);
		}
	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		return $this->crm_library->check_postcode($postcode);

	}

	/**
	 * check if either this or another field is filled in
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function phone_or_mobile($value, $value2) {

		// trim
		$value = trim($value);
		$value2 = trim($value2);

		// if both empty, not valid
		if (empty($value) && empty($value2)) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file addresses.php */
/* Location: ./application/controllers/staff/addresses.php */
