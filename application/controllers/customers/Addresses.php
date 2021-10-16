<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Addresses extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of addresses
	 * @return void
	 */
	public function index($org_id = NULL) {

		if ($org_id == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'address-card';
		$tab = 'addresses';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $org_id] = $org_info->name;
		$page_base = 'customers/addresses/' . $org_id;
		$section = 'customers';
		$title = 'Addresses';
		$buttons = '<a class="btn btn-success" href="' . site_url('customers/addresses/' . $org_id . '/new') . '"><i class="far fa-plus"></i> Create New</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_addresses.orgID' => $org_id,
			'orgs_addresses.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'type' => NULL,
			'address' => NULL,
			'town' => NULL,
			'county' => NULL,
			'postcode' => NULL,
			'phone' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_address', 'Address', 'trim|xss_clean');
			$this->form_validation->set_rules('search_town', 'Town', 'trim|xss_clean');
			$this->form_validation->set_rules('search_county', localise('county'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_postcode', 'Postcode', 'trim|xss_clean');
			$this->form_validation->set_rules('search_phone', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['type'] = set_value('search_type');
			$search_fields['address'] = set_value('search_address');
			$search_fields['town'] = set_value('search_town');
			$search_fields['county'] = set_value('search_county');
			$search_fields['postcode'] = set_value('search_postcode');
			$search_fields['phone'] = set_value('search_phone');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-addresses'))) {

			foreach ($this->session->userdata('search-customer-addresses') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-addresses', $search_fields);

			if ($search_fields['type'] != '') {
				$search_where[] = "`type` = " . $this->db->escape($search_fields['type']);
			}

			if ($search_fields['address'] != '') {
				$search_where[] = "`address1` LIKE '%" . $this->db->escape_like_str($search_fields['address']) . "%'";
			}

			if ($search_fields['town'] != '') {
				$search_where[] = "`town` LIKE '%" . $this->db->escape_like_str($search_fields['town']) . "%'";
			}

			if ($search_fields['county'] != '') {
				$search_where[] = "`county` LIKE '%" . $this->db->escape_like_str($search_fields['county']) . "%'";
			}

			if ($search_fields['postcode'] != '') {
				$search_where[] = "`postcode` LIKE '%" . $this->db->escape_like_str($search_fields['postcode']) . "%'";
			}

			if ($search_fields['phone'] != '') {
				$search_where[] = "`phone` LIKE '%" . $this->db->escape_like_str($search_fields['phone']) . "%'";
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->from('orgs_addresses')->where($where)->where($search_where, NULL, FALSE)->order_by('type asc, address1 asc, address2 asc, address3 asc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->from('orgs_addresses')->where($where)->where($search_where, NULL, FALSE)->order_by('type asc, address1 asc, address2 asc, address3 asc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

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
			'org_id' => $org_id,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/addresses', $data);
	}

	/**
	 * edit an address
	 * @param  int $addressID
	 * @param int $orgID
	 * @return void
	 */
	public function edit($addressID = NULL, $orgID = NULL)
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
			$query = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$address_info = $row;
				$orgID = $address_info->orgID;
			}

		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$org_info = $row;
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Address';
		if ($addressID != NULL) {
			$submit_to = 'customers/addresses/edit/' . $addressID;
			$title = $address_info->address1;
		} else {
			$submit_to = 'customers/addresses/' . $orgID . '/new/';
		}
		$return_to = 'customers/addresses/' . $orgID;
		$icon = 'address-card';
		$tab = 'addresses';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/addresses/' . $orgID] = 'Addresses';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('type', 'Type', 'trim|xss_clean|required');
			$this->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean|required');
			$this->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean');
			$this->form_validation->set_rules('address3', 'Address 3', 'trim|xss_clean');
			$this->form_validation->set_rules('town', 'Town', 'trim|xss_clean|required');
			$this->form_validation->set_rules('county', localise('county'), 'trim|xss_clean|required');
			$this->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean|required|callback_check_postcode');
			$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'address3' => set_value('address3'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'phone' => set_value('phone'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('type') != 'main') {
					$data['type'] = set_value('type');
				}

				if ($addressID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($addressID == NULL) {
						// insert
						$query = $this->db->insert('orgs_addresses', $data);
						$addressID = $this->db->insert_id();
						$just_added = TRUE;

					} else {
						$where = array(
							'addressID' => $addressID
						);

						// update
						$query = $this->db->update('orgs_addresses', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// geocode address
						if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
							$where = array(
								'addressID' => $addressID,
								'accountID' => $this->auth->user->accountID
							);
							$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('orgs_addresses');
						}

						if (isset($just_added)) {
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
			'org_id' => $orgID,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/address', $data);
	}

	/**
	 * delete an address
	 * @param  int $orgID
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
		$query = $this->db->from('orgs_addresses')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$address_info = $row;

			// all ok, delete
			$query = $this->db->delete('orgs_addresses', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $address_info->address1 . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $address_info->address1 . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/addresses/' . $address_info->orgID;

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

}

/* End of file addresses.php */
/* Location: ./application/controllers/customers/addresses.php */
