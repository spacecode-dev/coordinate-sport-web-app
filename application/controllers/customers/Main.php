<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}
	}

	/**
	 * show list of customers
	 * @return void
	 */
	public function index($org_type = 'school', $is_prospect = FALSE) {

		if (!in_array($org_type, array('school', 'organisation'))) {
			show_404();
		}

		// check permission
		if (($org_type == 'school' && $is_prospect === FALSE && !$this->auth->has_features('customers_schools'))
		|| ($org_type == 'school' && $is_prospect === TRUE && !$this->auth->has_features('customers_schools_prospects'))
		|| ($org_type == 'organisation' && $is_prospect === FALSE && !$this->auth->has_features('customers_orgs'))
		|| ($org_type == 'organisation' && $is_prospect === TRUE && !$this->auth->has_features('customers_orgs_prospects'))) {
			show_403();
		}

		// set defaults
		$icon = 'laptop';
		$type = $org_type;
		$current_page = $type . 's';
		$section = 'customers';
		$page_base = 'customers';
		$title = ucwords($type . 's');
		if ($is_prospect == TRUE) {
			$buttons = '<a class="btn btn-success" href="' . site_url('customers/new/prospect/' . $type) . '"><i class="far fa-plus"></i> Create New</a>';
		} else {
			$buttons = '<a class="btn btn-success" href="' . site_url('customers/new/' . $type) . '"><i class="far fa-plus"></i> Create New</a>';
		}
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// set where
		$where = array(
			'orgs.prospect' => 0,
			'orgs.type' => $type,
			'orgs.accountID' => $this->auth->user->accountID
		);

		if ($is_prospect == TRUE) {
			$page_base .= '/prospects/' . $type . 's';
			$type = 'prospective-' . $type;
			$current_page = $type . 's';
			$title = 'Prospective ' . $title;
			$where['orgs.prospect'] = 1;
		} else {
			$page_base .= '/' . $type . 's';
		}

		// set up search
		$search_where = array();
		$search_fields = array(
			'name' => NULL,
			'phone' => NULL,
			'postcode' => NULL,
			'county' => NULL,
			'type' => NULL,
			'school_type' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_name', 'Name', 'trim|xss_clean');
			$this->form_validation->set_rules('search_phone', 'Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('search_postcode', 'Postcode', 'trim|xss_clean');
			$this->form_validation->set_rules('search_county', localise('county'), 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_school_type', 'School Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['name'] = set_value('search_name');
			$search_fields['phone'] = set_value('search_phone');
			$search_fields['postcode'] = set_value('search_postcode');
			$search_fields['county'] = set_value('search_county');
			$search_fields['type'] = set_value('search_type');
			$search_fields['school_type'] = set_value('search_school_type');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-' . $type))) {

			foreach ($this->session->userdata('search-' . $type) as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-' . $type, $search_fields);

			if ($search_fields['name'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`name` LIKE '%" . $this->db->escape_like_str($search_fields['name']) . "%'";
			}

			if ($search_fields['phone'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs_addresses") . "`.`phone` LIKE '%" . $this->db->escape_like_str($search_fields['phone']) . "%'";
			}

			if ($search_fields['county'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs_addresses") . "`.`county` LIKE '%" . $this->db->escape_like_str($search_fields['county']) . "%'";
			}

			if ($search_fields['postcode'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs_addresses") . "`.`postcode` LIKE '%" . $this->db->escape_like_str($search_fields['postcode']) . "%'";
			}

			if ($search_fields['type'] != '') {
				if ($search_fields['type'] == 'local') {
					$is_private = 0;
				} else {
					$is_private = 1;
				}
				$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`isPrivate` = " . $this->db->escape($is_private);
			}

			if ($search_fields['school_type'] != '') {
				$search_where[] = '`' . $this->db->dbprefix("orgs") . "`.`schoolType` = " . $this->db->escape($search_fields['school_type']);
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('orgs.*, orgs_addresses.postcode, orgs_addresses.phone, COUNT(' . $this->db->dbprefix('bookings') . '.bookingID) as booking_count, COUNT(' . $this->db->dbprefix('bookings_blocks') . '.blockID) as block_count')
		->from('orgs')
		->join('orgs_addresses', 'orgs.orgID = orgs_addresses.orgID and orgs_addresses.type = \'main\'', 'left')
		->join('bookings', 'bookings.orgID = orgs.orgID', 'left')
		->join('bookings_blocks', 'bookings_blocks.orgID = orgs.orgID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->group_by('orgs.orgID')
		->order_by('orgs.name')
		->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('orgs.*, orgs_addresses.postcode, orgs_addresses.phone, COUNT(' . $this->db->dbprefix('bookings') . '.bookingID) as booking_count, COUNT(' . $this->db->dbprefix('bookings_blocks') . '.blockID) as block_count')
		->from('orgs')
		->join('orgs_addresses', 'orgs.orgID = orgs_addresses.orgID and orgs_addresses.type = \'main\'', 'left')
		->join('bookings', 'bookings.orgID = orgs.orgID', 'left')
		->join('bookings_blocks', 'bookings_blocks.orgID = orgs.orgID', 'left')
		->where($where)
		->where($search_where, NULL, FALSE)
		->group_by('orgs.orgID')
		->order_by('orgs.name')
		->limit($this->pagination_library->amount, $this->pagination_library->start)
		->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		}
		if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}
		if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// retrieve bulk data
		$bulk_data = array();
		if ($this->session->flashdata('bulk_data')) {
			$bulk_data = $this->session->flashdata('bulk_data');
			if (!is_array($bulk_data)) {
				$bulk_data = array();
			}
		}

		// if an error, keep tags in list that are not already stored
		if (!empty($error) && isset($bulk_data['tags'])) {
			if (is_array($bulk_data['tags'])) {
				$tag_list = array_merge($tag_list, $bulk_data['tags']);
			}
		}
		$this->load->library('user_agent');
		$is_mobile_device = FALSE;
		if ($this->agent->is_mobile()){
			$is_mobile_device = TRUE;
		}

		// prepare data for view
		$data = array(
			'title' => $title,
			'icon' => $icon,
			'current_page' => $current_page,
			'section' => $section,
			'buttons' => $buttons,
			'is_prospect' => $is_prospect,
			'search_fields' => $search_fields,
			'type' => $type,
			'org_type' => $org_type,
			'page_base' => $page_base,
			'customers' => $res,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'bulk_data' => $bulk_data,
			'tag_list' => $tag_list,
			'is_mobile_device' => $is_mobile_device
		);

		// load view
		$this->crm_view('customers/main', $data);
	}

	/**
	 * edit a customer
	 * @param  int $orgID
	 * @param string $type
	 * @return void
	 */
	public function edit($orgID = NULL, $type = 'school', $is_prospect = FALSE)
	{

		$org_info = new stdClass;

		if ($is_prospect == TRUE) {
			$org_info->prospect = 1;
		} else {
			$org_info->prospect = 0;
		}

		// check if editing
		if ($orgID != NULL) {

			// check if numeric
			if (!ctype_digit($orgID)) {
				show_404();
			}

			// if so, check exists
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
				$type = $org_info->type;
				$is_prospect = boolval($org_info->prospect);

				// get org tags
				$org_info->tags = array();
				$where = array(
					'orgs_tags.accountID' => $this->auth->user->accountID,
					'orgs_tags.orgID' => $orgID
				);
				$res = $this->db->select('settings_tags.*')->from('orgs_tags')->join('settings_tags', 'orgs_tags.tagID = settings_tags.tagID', 'inner')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$org_info->tags[] = $row->name;
					}
				}
			}

		}

		// check permission
		if (($type == 'school' && $is_prospect === FALSE && !$this->auth->has_features('customers_schools'))
		|| ($type == 'school' && $is_prospect === TRUE && !$this->auth->has_features('customers_schools_prospects'))
		|| ($type == 'organisation' && $is_prospect === FALSE && !$this->auth->has_features('customers_orgs'))
		|| ($type == 'organisation' && $is_prospect === TRUE && !$this->auth->has_features('customers_orgs_prospects'))) {
			show_403();
		}

		if (!in_array($type, array('school', 'organisation'))) {
			show_404();
		}

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New ' . ucwords($type);
		$submit_to = 'customers/new/';
		$return_to = 'customers/';
		if ($this->input->post('prospect') == 1) {
			$return_to .= 'prospects/';
		}
		$submit_to .= $type;
		$return_to .= $type . 's';
		if ($orgID != NULL) {
			$title = $org_info->name;
			$submit_to = 'customers/edit/' . $orgID;
		}
		$icon = 'laptop';
		$tab = 'details';
		$current_page = $type . 's';
		$breadcrumb_levels = array();
		if ((isset($org_info->prospect) && $org_info->prospect == 1) || $this->input->post('prospect') === 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $type . 's'] = 'Prospective ' . ucwords($type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $type . 's'] = ucwords($type) . 's';
		}
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		// get list of Org Type
		$org_types = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('settings_customer_types')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$org_types[$row->org_typeID] = $row->name;
			}
		}

		// get list of session types
		$lesson_types = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('lesson_types')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$lesson_types[$row->typeID] = $row->name;
			}
		}

		// get list of brands
		$brands = array();
		$brand_colours = array();
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->from('brands')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$brands[$row->brandID] = $row->name;
				$brand_colours[$row->brandID] = $row->colour;
			}
		}

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('name', 'Name', 'trim|xss_clean|required');

			switch ($type) {
				case 'school':
					$this->form_validation->set_rules('isPrivate', 'Private/Local Authority', 'trim|xss_clean|callback_check_boolean');
					$this->form_validation->set_rules('schoolType', 'Type', 'trim|xss_clean|required');
					break;
			}

			$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email');
			$this->form_validation->set_rules('website', 'Web Site', 'trim|xss_clean');
			$this->form_validation->set_rules('rate', 'Rate/Charge', 'trim|xss_clean');
			$this->form_validation->set_rules('invoiceFrequency', 'Invoice Frequency', 'trim|xss_clean');
			$this->form_validation->set_rules('org_typeID', 'Organisation Type', 'trim|xss_clean');
			$this->form_validation->set_rules('prospect', 'Status', 'trim|xss_clean|callback_check_boolean');
			$this->form_validation->set_rules('regionID', 'Region', 'trim|xss_clean');
			$this->form_validation->set_rules('areaID', 'Area', 'trim|xss_clean');
			$this->form_validation->set_rules('staffing_notes', 'Staffing Notes', 'trim|xss_clean');

			if ($orgID == NULL) {
				$this->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean|required');
				$this->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean');
				$this->form_validation->set_rules('address3', 'Address 3', 'trim|xss_clean');
				$this->form_validation->set_rules('town', 'Town', 'trim|xss_clean|required');
				$this->form_validation->set_rules('county', localise('county'), 'trim|xss_clean|required');
				$this->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean|required|callback_check_postcode');
				$this->form_validation->set_rules('phone', 'Phone', 'trim|xss_clean|required');
			}

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'name' => set_value('name'),
					'type' => $type,
					'email' => set_value('email'),
					'website' => set_value('website'),
					'rate' => set_value('rate'),
					'invoiceFrequency' => NULL,
					'org_typeID' => set_value('org_typeID'),
					'prospect' => set_value('prospect'),
					'staffing_notes' => set_value('staffing_notes'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (set_value('invoiceFrequency') != '') {
					$data['invoiceFrequency'] = set_value('invoiceFrequency');
				}

				if (set_value('regionID') != '') {
					$data['regionID'] = set_value('regionID');
				} else {
					$data['regionID'] = NULL;
				}

				if (set_value('areaID') != '') {
					$data['areaID'] = set_value('areaID');
				} else {
					$data['areaID'] = NULL;
				}

				switch ($type) {
					case 'school':
						$data['isPrivate'] = set_value('isPrivate');
						$data['schoolType'] = set_value('schoolType');
						break;
				}

				if ($orgID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($orgID == NULL) {
						// insert
						$query = $this->db->insert('orgs', $data);

						// get id
						$orgID = $this->db->insert_id();

						$just_added = TRUE;

						$data = array(
							'orgID' => $orgID,
							'byID' => $this->auth->user->staffID,
							'type' => 'main',
							'address1' => set_value('address1'),
							'address2' => set_value('address2'),
							'address3' => set_value('address3'),
							'town' => set_value('town'),
							'county' => set_value('county'),
							'postcode' => set_value('postcode'),
							'phone' => set_value('phone'),
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						// insert
						$query = $this->db->insert('orgs_addresses', $data);
						$addressID = $this->db->insert_id();

						// geocode address
						if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
							$where = array(
								'addressID' => $addressID,
								'accountID' => $this->auth->user->accountID
							);
							$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('orgs_addresses');
						}
					} else {
						$where = array(
							'orgID' => $orgID
						);

						// update
						$query = $this->db->update('orgs', $data, $where);
					}

					// if inserted/updated
					if ($this->db->affected_rows() == 1) {

						// add/update tags
						$tags = $this->input->post('tags');
						if (!is_array($tags)) {
							$tags = array();
						}
						// remove existing
						$where = array(
							'orgID' => $orgID,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->delete('orgs_tags', $where);
						if (count($tags) > 0) {
							foreach ($tags as $tag) {
								$tag = trim(strtolower($tag));
								// check if tag in system already
								if (in_array($tag, $tag_list)) {
									$tagID = array_search($tag, $tag_list);
								} else {
									$data = array(
										'name' => $tag,
										'byID' => $this->auth->user->staffID,
										'added' => mdate('%Y-%m-%d %H:%i:%s'),
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'accountID' => $this->auth->user->accountID
									);
									$this->db->insert('settings_tags', $data);
									$tagID = $this->db->insert_id();
									$tag_list[$tagID] = $tag;
								}
								// add link to tag
								$data = array(
									'tagID' => $tagID,
									'orgID' => $orgID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);
								$this->db->insert('orgs_tags', $data);
							}
						}

						// add/update pricing
						$prices_posted = $this->input->post('prices');
						if (!is_array($prices_posted)) {
							$prices_posted = array();
						}
						foreach ($lesson_types as $typeID => $type) {
							foreach ($brands as $brandID => $brand) {
								$where = array(
									'orgID' => $orgID,
									'typeID' => $typeID,
									'brandID' => $brandID,
									'accountID' => $this->auth->user->accountID
								);
								if ((!isset($prices_posted[$typeID][$brandID]['amount']) || $prices_posted[$typeID][$brandID]['amount'] <= 0) && (!isset($prices_posted[$typeID][$brandID]['contract']) || $prices_posted[$typeID][$brandID]['contract'] != 1)) {
									// delete existing
									$this->db->delete('orgs_pricing', $where);
								} else {
									// look up, see if already exists
									$res = $this->db->from('orgs_pricing')->where($where)->get();

									$data = array(
										'orgID' => $orgID,
										'typeID' => $typeID,
										'brandID' => $brandID,
										'amount' => 0,
										'contract' => 0,
										'accountID' => $this->auth->user->accountID,
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);

									if (isset($prices_posted[$typeID][$brandID]['amount']) && $prices_posted[$typeID][$brandID]['amount'] > 0) {
										$data['amount'] = floatval($prices_posted[$typeID][$brandID]['amount']);
									}

									if (isset($prices_posted[$typeID][$brandID]['contract']) && $prices_posted[$typeID][$brandID]['contract'] == 1) {
										$data['contract'] = 1;
									}

									if ($res->num_rows() > 0) {
										$this->db->update('orgs_pricing', $data, $where);
									} else {
										$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
										$this->db->insert('orgs_pricing', $data);
									}
								}
							}
						}

						if (isset($just_added) && $just_added === TRUE) {
							$this->session->set_flashdata('success', set_value('name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('name') . ' has been updated successfully.');
						}

						redirect('customers/edit/' . $orgID);

						return TRUE;
					} else {
						$this->session->set_flashdata('info', 'Error saving data, please try again.');
					}
				}
			}
		}

		// if an error, keep tags in list that are not already stored
		if (count($errors) > 0 && isset($_POST)) {
			$tags = $this->input->post('tags');
			if (is_array($tags)) {
				$tag_list = array_merge($tag_list, $tags);
			}
		}

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}

		// pricing
		$prices_array = array();
		$prices_contract_array = array();
		if ($orgID != NULL) {
			$where = array(
				'orgID' => $orgID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('orgs_pricing')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$prices_array[$row->typeID][$row->brandID]['amount'] = $row->amount;
					$prices_array[$row->typeID][$row->brandID]['contract'] = $row->contract;
				}
			}
		}

		// regions and areas
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$regions = $this->db->from('settings_regions')->where($where)->order_by('name asc')->get();
		$areas = $this->db->from('settings_areas')->where($where)->order_by('name asc')->get();

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
			'org_type' => $type,
			'org_id' => $orgID,
			'regions' => $regions,
			'areas' => $areas,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'lesson_types' => $lesson_types,
			'org_types' => $org_types,
			'brands' => $brands,
			'brand_colours' => $brand_colours,
			'prices_array' => $prices_array,
			'tag_list' => $tag_list,
			'breadcrumb_levels' => $breadcrumb_levels
		);

		// load view
		$this->crm_view('customers/customer', $data);
	}

	/**
	 * delete a customer
	 * @param  int $orgID
	 * @return mixed
	 */
	public function remove($orgID = NULL) {

		// check params
		if (empty($orgID)) {
			show_404();
		}

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

			// all ok, attempt delete
			$this->db->trans_start();
			// start with main address
			$address_where = $where;
			$address_where['main'] = 1;
			$query = $this->db->delete('orgs_addresses', $where);
			// then org
			$query = $this->db->delete('orgs', $where);
			$this->db->trans_complete();

			if ($this->db->trans_status() !== FALSE) {
				$this->session->set_flashdata('success', $org_info->name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $org_info->name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers';

			// if prospect, redirect back to prospects page
			if ($org_info->prospect == 1) {
				$redirect_to .= '/prospects';
			}

			switch ($org_info->type) {
				case 'school':
					$redirect_to .= '/schools';
					break;
				case 'organisation':
					$redirect_to .= '/organisations';
					break;
			}

			redirect($redirect_to);
		}
	}

	// bulk actions
	private $bulk_data;

	/**
	 * bulk actions for orgs
	 * @return mixed
	 */
	public function bulk() {

		$this->bulk_data['redirect_to'] = $this->input->post('redirect_to');
		if (empty($this->bulk_data['redirect_to'])) {
			$this->bulk_data['redirect_to'] = 'customers/schools';
		}

		// save bulk data
		$this->bulk_data['orgs'] = array();
		$this->bulk_data['action'] = $this->input->post('action');
		$this->bulk_data['tags'] = $this->input->post('tags');

		// check orgs
		$orgs = $this->input->post('orgs');

		// check if array
		if (!is_array($orgs)) {
			$this->session->set_flashdata('error', 'Please select at least one ' . strtolower($this->settings_library->get_label('customer')));
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			redirect($this->bulk_data['redirect_to']);
		}

		// loop through all
		$verified_orgs = array();

		foreach ($orgs as $orgID) {
			$where = array(
				'orgs.orgID' => $orgID,
				'orgs.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->from('orgs')->where($where)->limit(1)->get();

			if ($res->num_rows() == 1) {
				foreach ($res->result() as $org_info) {
					$verified_orgs[$orgID] = $org_info;
				}
			}
		}

		// save
		$this->bulk_data['orgs'] = $verified_orgs;

		if (count($this->bulk_data['orgs']) == 0) {
			$this->session->set_flashdata('error', 'Please select at least one ' . strtolower($this->settings_library->get_label('customer')));
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			redirect($this->bulk_data['redirect_to']);
		}

		// switch action
		switch ($this->bulk_data['action']) {
			case 'tag':
				$this->bulk_tag();
				break;
			case 'passwords':
				$this->bulk_passwords();
				break;
			default:
				$this->session->set_flashdata('error', 'Please select an action.');
				$this->session->set_flashdata('bulk_data', $this->bulk_data);
				redirect($this->bulk_data['redirect_to']);
				break;
		}

	}

	/**
	 * bulk add tags
	 * @return mixed
	 */
	private function bulk_tag() {

		// get params
		$orgs = $this->bulk_data['orgs'];
		$tags = $this->bulk_data['tags'];

		// get tags
		$tag_list = array();
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$res = $this->db->from('settings_tags')->where($where)->order_by('name asc')->get();
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$tag_list[$row->tagID] = $row->name;
			}
		}

		if (!is_array($tags) || count($tags) == 0) {
			$this->session->set_flashdata('bulk_data', $this->bulk_data);
			$this->session->set_flashdata('error', 'At least one tag required.');
		} else {

			// update
			$orgs_changed = 0;

			foreach ($orgs as $orgID => $org_info) {
				// remove existing
				$where = array(
					'orgID' => $orgID,
					'accountID' => $this->auth->user->accountID
				);
				$this->db->delete('orgs_tags', $where);
				if (count($tags) > 0) {
					foreach ($tags as $tag) {
						$tag = trim(strtolower($tag));
						// check if tag in system already
						if (in_array($tag, $tag_list)) {
							$tagID = array_search($tag, $tag_list);
						} else {
							$data = array(
								'name' => $tag,
								'byID' => $this->auth->user->staffID,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->auth->user->accountID
							);
							$this->db->insert('settings_tags', $data);
							$tagID = $this->db->insert_id();
							$tag_list[$tagID] = $tag;
						}
						// add link to tag
						$data = array(
							'tagID' => $tagID,
							'orgID' => $orgID,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);
						$this->db->insert('orgs_tags', $data);
					}
				}
				$orgs_changed++;
			}

			// tell user
			if ($orgs_changed == 0) {
				$this->session->set_flashdata('error', 'No ' . strtolower($this->settings_library->get_label('customers')) . ' have been updated.');
			} else {

				$success_text = $orgs_changed . ' ';

				if($orgs_changed == 1) {
					$success_text .= strtolower($this->settings_library->get_label('customer')) . ' has';
				} else {
					$success_text .= strtolower($this->settings_library->get_label('customers')) . ' have';
				}

				$this->session->set_flashdata('success', $success_text . ' been updated successfully.');
			}

		}

		redirect($this->bulk_data['redirect_to']);
	}

	/**
	 * bulk assign passwords
	 * @return mixed
	 */
	private function bulk_passwords() {

		// get params
		$orgs = $this->bulk_data['orgs'];

		// look up potential orgs
		$org_ids = array_keys($orgs);

		$where = array(
			'orgs_contacts.isMain' => 1,
			'orgs_contacts.accountID' => $this->auth->user->accountID,
			'orgs_contacts.email !=' => '',
			'orgs_contacts.email IS NOT NULL' => NULL,
			'orgs_contacts.password IS NULL' => NULL,
		);
		$contacts_res = $this->db->select('orgs_contacts.*, orgs.name as org_name')->from('orgs_contacts')->join('orgs', 'orgs_contacts.orgID = orgs.orgID', 'inner')->where($where)->where_in('orgs_contacts.orgID', $org_ids)->get();

		if ($contacts_res->num_rows() == 0) {
			$this->session->set_flashdata('error', 'No eligible ' . strtolower($this->settings_library->get_label('customers')) . ' - either they already have passwords or have no main contact set');
			redirect($this->bulk_data['redirect_to']);
		}

		// get emails already in use for account
		$emails_in_use = array();
		$where = array(
			'password IS NOT NULL' => NULL,
			'accountID' => $this->auth->user->accountID,
			'active' => 1
		);
		$res = $this->db->get_where('orgs_contacts', $where);
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$emails_in_use[] = $row->email;
			}
		}

		// loop through eligible contacts
		foreach ($contacts_res->result() as $contact_info) {
			// check if email in use
			if (in_array($contact_info->email, $emails_in_use)) {
				continue;
			}

			// reset vars
			$subject = $this->input->post('subject', FALSE);
			$content = $this->input->post('content', FALSE);

			// generate password
			$password = get_random_password();

			// generate hash
			$password_hash = password_hash($password, PASSWORD_BCRYPT);

			// check hash
			if (!password_verify($password, $password_hash)) {
				// skip if not verified
				continue;
			}

			// update contact
			$where = array(
				'contactID' => $contact_info->contactID,
				'accountID' => $this->auth->user->accountID
			);
			$data = array(
				'password' => $password_hash,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$res = $this->db->update('orgs_contacts', $data, $where, 1);

			// update emails in use
			$emails_in_use[] = $contact_info->email;

			// notify
			if ($this->settings_library->get('send_customer_password') == 1 && $this->crm_library->send_customer_welcome_email($contact_info->contactID, $password)) {
				$processed[] = $contact_info->orgID;
			}
		}

		if (count($processed) > 0) {
			if (count($processed) == 1) {
				$label = $this->settings_library->get_label('customer');
			} else {
				$label = $this->settings_library->get_label('customers');
			}
			$this->session->set_flashdata('success', 'Passwords have been assigned and sent to ' . count($processed) . ' ' . strtolower($label));
		}

		// if processed less than total
		if (count($processed) < count($org_ids)) {
			$unprocessed_orgs = array_diff($org_ids, $processed);
			$orgs_skipped = array();
			foreach ($unprocessed_orgs as $orgID) {
				$orgs_skipped[] = $orgs[$orgID]->name;
			}
			$this->session->set_flashdata('info', 'Passwords not sent to the following as they either already have passwords or have no main contact set: ' . implode(", ", $orgs_skipped));
		}

		redirect($this->bulk_data['redirect_to']);
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
	 * check to see if boolean is correct
	 * @param  int $var
	 * @return boolean
	 */
	public function check_boolean($var) {
		if ($var === 1 || $var === 0 || $var === '1' || $var === '0') {
			return TRUE;
		}
		return FALSE;
	}

}

/* End of file main.php */
/* Location: ./application/controllers/customers/main.php */
