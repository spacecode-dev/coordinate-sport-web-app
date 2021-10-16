<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Safety extends MY_Controller {

	public function __construct() {
		// allow all as coaches need to view print version, but restrict within individual controllers
		parent::__construct(FALSE, array(), array(), array('safety'));

		// if no access to any customer types
		if (!$this->auth->has_features('customers_schools') && !$this->auth->has_features('customers_schools_prospects') && !$this->auth->has_features('customers_orgs') && !$this->auth->has_features('customers_orgs_prospects')) {
			show_403();
		}

		$this->load->model('Orgs/OrgsSafetyModel');
	}

	/**
	 * show list of docs
	 * @return void
	 */
	public function index($org_id = NULL) {

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		if ($org_id == NULL) {
			show_404();
		}

		// look up org
		$where = array(
			'orgID' => $org_id
		);
		$res = $this->db->from('orgs')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$org_info = $row;
		}

		// set defaults
		$icon = 'book';
		$tab = 'safety';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $org_id] = $org_info->name;
		$page_base = 'customers/safety/' . $org_id;
		$section = 'customers';
		$title = 'Health & Safety';
		$buttons = '<a class="btn btn-success" href="' . site_url('customers/safety/camp/' . $org_id . '/new') . '"><i class="far fa-plus"></i> Event/Project Induction</a> <a class="btn btn-success" href="' . site_url('customers/safety/school/' . $org_id . '/new') . '"><i class="far fa-plus"></i> School Induction</a> <a class="btn btn-success" href="' . site_url('customers/safety/risk/' . $org_id . '/new') . '"><i class="far fa-plus"></i> Risk Assessment</a>';
		$success = NULL;
		$error = NULL;
		$info = NULL;

		// set where
		$where = array(
			'orgs_safety.orgID' => $org_id,
			'orgs_safety.accountID' => $this->auth->user->accountID
		);

		// set up search
		$search_where = array();
		$search_fields = array(
			'date_from' => NULL,
			'date_to' => NULL,
			'addressID' => NULL,
			'type' => NULL,
			'expired' => NULL,
			'search' => NULL
		);

		// if search
		if ($this->input->post()) {
			// load libraries
			$this->load->library('form_validation');

			// validate - need to do this so populates set_value
			$this->form_validation->set_rules('search_date_from', 'Date From', 'trim|xss_clean');
			$this->form_validation->set_rules('search_date_to', 'Date To', 'trim|xss_clean');
			$this->form_validation->set_rules('search_addressID', 'Address', 'trim|xss_clean');
			$this->form_validation->set_rules('search_type', 'Type', 'trim|xss_clean');
			$this->form_validation->set_rules('search_expired', 'Expired', 'trim|xss_clean');
			$this->form_validation->set_rules('search', 'Search', 'trim|xss_clean');

			// run validation
			$this->form_validation->run();

			$search_fields['date_from'] = set_value('search_date_from');
			$search_fields['date_to'] = set_value('search_date_to');
			$search_fields['addressID'] = set_value('search_addressID');
			$search_fields['type'] = set_value('search_type');
			$search_fields['expired'] = set_value('search_expired');
			$search_fields['search'] = set_value('search');

			$is_search = TRUE;

		} else if ($this->crm_library->last_segment() == 'recall' && is_array($this->session->userdata('search-customer-safety'))) {

			foreach ($this->session->userdata('search-customer-safety') as $key => $value) {
				$search_fields[$key] = $value;
			}

			$is_search = TRUE;

		}

		if (isset($is_search) && $is_search === TRUE) {

			// tell pagination is search, so all one one page
			$this->pagination_library->is_search();

			// store search fields
			$this->session->set_userdata('search-customer-safety', $search_fields);

			if ($search_fields['date_from'] != '') {
				$date_from = uk_to_mysql_date($search_fields['date_from']);
				if ($date_from !== FALSE) {
					$search_where[] = "`date` >= " . $this->db->escape($date_from);
				}
			}

			if ($search_fields['date_to'] != '') {
				$date_to = uk_to_mysql_date($search_fields['date_to']);
				if ($date_to !== FALSE) {
					$search_where[] = "`date` <= " . $this->db->escape($date_to);
				}
			}

			if ($search_fields['addressID'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('orgs_safety') . "`.`addressID` = " . $this->db->escape($search_fields['addressID']);
			}

			if ($search_fields['type'] != '') {
				$search_where[] = "`" . $this->db->dbprefix('orgs_safety') . "`.`type` = " . $this->db->escape($search_fields['type']);
			}

			switch ($search_fields['expired']) {
				case 'yes':
					$search_where[] = "`expiry` < CURDATE()";
					break;
				case 'no':
					$search_where[] = "`expiry` >= CURDATE()";
					break;
			}

			if (count($search_where) > 0) {
				$search_where = '(' . implode(' AND ', $search_where) . ')';
			}
		}

		// run query
		$res = $this->db->select('orgs_safety.*, orgs_addresses.address1, orgs_addresses.address2,
		 orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode')
			->from('orgs_safety')
			->join('orgs_addresses', 'orgs_safety.addressID = orgs_addresses.addressID', 'inner')
			->where($where)
			->where($search_where, NULL, FALSE)->order_by('added desc')->get();

		// workout pagination
		$total_items = $res->num_rows();

		$pagination = $this->pagination_library->calc($total_items);

		// run query again, but limited
		$res = $this->db->select('orgs_safety.*, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, lesson_types.name as lesson_type')->from('orgs_safety')->join('orgs_addresses', 'orgs_safety.addressID = orgs_addresses.addressID', 'inner')->join('lesson_types', 'orgs_safety.typeID = lesson_types.typeID', 'left')->where($where)->where($search_where, NULL, FALSE)->order_by('added desc')->limit($this->pagination_library->amount, $this->pagination_library->start)->get();

		// check for flash data
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// get addresses
		$where = array(
			'orgID' => $org_id,
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();

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
			'documents' => $res,
			'addresses' => $addresses,
			'org_id' => $org_id,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'error' => $error,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/safety', $data);
	}

	/**
	 * edit a camp induction
	 * @param  int $docID
	 * @param int $orgID
	 * @return void
	 */
	public function camp($docID = NULL, $orgID = NULL)
	{

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		$doc_info = new stdClass();

		// check if editing
		if ($docID != NULL) {

			// check if numeric
			if (!ctype_digit($docID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'docID' => $docID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$doc_info = $row;
				$orgID = $doc_info->orgID;
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
		$title = 'New Event/Project Induction';
		if ($docID != NULL) {
			$submit_to = 'customers/safety/camp/' . $docID;
			$title = mysql_to_uk_date($doc_info->date);
		} else {
			$submit_to = 'customers/safety/camp/' . $orgID . '/new/';
		}
		$return_to = 'customers/safety/' . $orgID;
		$icon = 'book';
		$tab = 'safety';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/safety/' . $orgID] = 'Health & Safety';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if not new, unserialize details
		if ($docID != NULL) {
			$doc_info->details = @$this->crm_library->mb_unserialize($doc_info->details);
			if (!is_array($doc_info->details)) {
				$doc_info->details = array();
			}
		}

		// brands
		$brand_where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_brand_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_brand_where[] = '`brandID` = ' . $this->db->escape($doc_info->brandID);
		}
		$brand_where['(' . implode(' OR ', $or_brand_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($brand_where, NULL, FALSE)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('addressID', 'Address', 'trim|xss_clean|required');
			$this->form_validation->set_rules('typeID', 'Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('byID', 'Assessor ', 'trim|xss_clean|required');
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('expiry', 'Expiry', 'trim|xss_clean|required|callback_check_date');

			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
			$this->form_validation->set_rules('venue_contact1', 'Emergency Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('venue_contact2', 'Secondary Emergency Contact', 'trim|xss_clean');
			$this->form_validation->set_rules('open_lockup', 'Open and Lock up Procedure', 'trim|xss_clean');
			$this->form_validation->set_rules('registration_area', 'Parent Registration Area', 'trim|xss_clean');
			$this->form_validation->set_rules('fire_procedure', 'Fire Evacuation Procedure/Emergency Exits', 'trim|xss_clean');
			$this->form_validation->set_rules('indoor_toilets', 'Toilets (Indoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('outdoor_toilets', 'Toilets (Outdoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('indoor_lunch', 'Lunch (Indoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('outdoor_lunch', 'Lunch (Outdoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('indoor_activity', 'Activity (Indoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('outdoor_activity', 'Activity (Outdoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('indoor_not', 'Areas Not for Use (Indoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('outdoor_not', 'Areas Not for Use (Outdoor)', 'trim|xss_clean');
			$this->form_validation->set_rules('accident_procedure', 'Procedure', 'trim|xss_clean');
			$this->form_validation->set_rules('equipment_additional', 'Any Additional Equipment', 'trim|xss_clean');
			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand'), 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'byID' => set_value('byID'),
					'addressID' => set_value('addressID'),
					'typeID' => NULL,
					'date' => uk_to_mysql_date(set_value('date')),
					'expiry' => uk_to_mysql_date(set_value('expiry')),
					'type' => 'camp induction',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID,
					'brandID' => set_value('brandID')
				);

				if ($docID == NULL) {
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if (set_value('typeID') != '') {
					$data['typeID'] = set_value('typeID');
				}

				$map_images = array();
				$venue_images = array();

				// details
				$details = array(
					'location' => set_value('location'),
					'venue_contact1' => $this->input->post('venue_contact1'),
					'venue_contact2' => $this->input->post('venue_contact2'),
					'open_lockup' => $this->input->post('open_lockup'),
					'registration_area' => $this->input->post('registration_area'),
					'fire_procedure' => $this->input->post('fire_procedure'),
					'indoor_toilets' => $this->input->post('indoor_toilets'),
					'outdoor_toilets' => $this->input->post('outdoor_toilets'),
					'indoor_lunch' => $this->input->post('indoor_lunch'),
					'outdoor_lunch' => $this->input->post('outdoor_lunch'),
					'indoor_activity' => $this->input->post('indoor_activity'),
					'outdoor_activity' => $this->input->post('outdoor_activity'),
					'indoor_not' => $this->input->post('indoor_not'),
					'outdoor_not' => $this->input->post('outdoor_not'),
					'accident_procedure' => $this->input->post('accident_procedure'),
					'equipment' => $this->input->post('equipment'),
					'equipment_details' => $this->input->post('equipment_details'),
					'equipment_additional' => $this->input->post('equipment_additional'),
					'venue_images' => $this->input->post('venue_images'),
					'map_images' => $this->input->post('map_images')
				);

				// final check for errors
				if (count($errors) == 0) {

					$path = UPLOADPATH . 'orgs/' . $orgID . '/safety/';

					// venue images
					for ($i = 0; $i < 5; $i++) {

						$delete_venue_images = $this->input->post('delete_venue_images');

						// delete if requested
						if (is_array($delete_venue_images) && array_key_exists($i, $delete_venue_images) && file_exists($path . $delete_venue_images[$i])) {
								unlink($path . $delete_venue_images[$i]);
								if (file_exists($path . 'thumb.' . $delete_venue_images[$i])) {
									unlink($path . 'thumb.' . $delete_venue_images[$i]);
								}
								unset($details['venue_images'][$i]);
						}

						// upload image
						$upload_res = $this->crm_library->handle_safety_upload($orgID, 'venue_images_' . $i);

						if ($upload_res !== NULL) {

							// save in array
							$details['venue_images'][$i] = $upload_res['file_name'];

							// delete previous file, if exists
							if (isset($doc_info->details['venue_images']) && is_array($doc_info->details['venue_images']) && array_key_exists($i, $doc_info->details['venue_images']) && file_exists($path . $doc_info->details['venue_images'][$i])) {
								unlink($path . $doc_info->details['venue_images'][$i]);
								if (file_exists($path . 'thumb.' . $doc_info->details['venue_images'][$i])) {
									unlink($path . 'thumb.' . $doc_info->details['venue_images'][$i]);
								}
							}
						}

					}

					// map images
					for ($i = 0; $i < 1; $i++) {

						$delete_map_images = $this->input->post('delete_map_images');

						// delete if requested
						if (is_array($delete_map_images) && array_key_exists($i, $delete_map_images) && file_exists($path . $delete_map_images[$i])) {
								unlink($path . $delete_map_images[$i]);
								if (file_exists($path . 'thumb.' . $delete_map_images[$i])) {
									unlink($path . 'thumb.' . $delete_map_images[$i]);
								}
								unset($details['map_images'][$i]);
						}

						// upload image
						$upload_res = $this->crm_library->handle_safety_upload($orgID, 'map_images_' . $i);

						if ($upload_res !== NULL) {

							// save in array
							$details['map_images'][$i] = $upload_res['file_name'];

							// delete previous file, if exists
							if (isset($doc_info->details['map_images']) && is_array($doc_info->details['map_images']) && array_key_exists($i, $doc_info->details['map_images']) && file_exists($path . $doc_info->details['map_images'][$i])) {
								unlink($path . $doc_info->details['map_images'][$i]);
								if (file_exists($path . 'thumb.' . $doc_info->details['map_images'][$i])) {
									unlink($path . 'thumb.' . $doc_info->details['map_images'][$i]);
								}
							}
						}

					}

					$data['details'] = serialize($details);

					if ($docID == NULL) {
						// insert
						$query = $this->db->insert('orgs_safety', $data);

						$affected_rows = $this->db->affected_rows();

					} else {
						$where = array(
							'docID' => $docID
						);

						// update
						$query = $this->db->update('orgs_safety', $data, $where);

						$affected_rows = $this->db->affected_rows();

						// set previous reads of this doc to outdated so have to reconfirm
						$where = array(
							'docID' => $docID,
							'accountID' => $this->auth->user->accountID
						);
						$res = $this->db->update('orgs_safety_read', array('outdated' => 1), $where);

					}

					// if inserted/updated
					if ($affected_rows == 1) {

						if ($orgID == NULL) {
							$this->session->set_flashdata('success', set_value('date') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('date') . ' has been updated successfully.');
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

		// get data
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// session types
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_where[] = '`typeID` = ' . $this->db->escape($doc_info->typeID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$lesson_types = $this->db->from('lesson_types')->where($where, NULL, FALSE)->order_by('name asc')->get();

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
			'doc_info' => $doc_info,
			'org_id' => $orgID,
			'addresses' => $addresses,
			'staff' => $staff,
			'breadcrumb_levels' => $breadcrumb_levels,
			'lesson_types' => $lesson_types,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'brands' => $brands
		);

		// load view
		$this->crm_view('customers/safety-camp', $data);
	}

	public function duplicate($docId = null) {

		if (empty($docId)) {
			show_404();
		}

		$docInfo = $this->OrgsSafetyModel->getById($docId);

		if (!$docInfo) {
			show_404();
		}

		$this->duplicateDoc($docInfo);
	}

	public function duplicateDoc($docInfo) {
		$data = [
			'accountID' => $this->auth->user->accountID,
			'orgID' 	=> $docInfo->orgID,
			'addressID' => $docInfo->addressID,
			'typeID' 	=> $docInfo->typeID,
			'byID' 		=> $docInfo->byID,
			'brandID' 	=> $docInfo->brandID,
			'type' 		=> $docInfo->type,
			'renewed' 	=> $docInfo->renewed,
			'date' 		=> $docInfo->date,
			'expiry' 	=> $docInfo->expiry,
			'added' 	=> mdate('%Y-%m-%d %H:%i:%s'),
			'modified' 	=> mdate('%Y-%m-%d %H:%i:%s'),
		];

		$redirect_to = '/customers/safety/' . $docInfo->orgID;
		$this->load->library('upload');

		if (!empty($docInfo->details)) {
			$details = unserialize($docInfo->details);
			$path = 'orgs/' . $docInfo->orgID . '/safety/';
			if (!empty($details['venue_images'])) {
				foreach ($details['venue_images'] as $key => $image) {
					$imagePath = $path . $image;

					$duplicateUpload = $this->crm_library->duplicate_upload($imagePath, $path, null, true);

					if ($duplicateUpload) {
						$details['venue_images'][$key] = $duplicateUpload;
					}

					//duplicate thumb
					$thumbPath = $path . 'thumb.' . $image;
					$this->crm_library->duplicate_upload($thumbPath, $path, 'thumb.' . $duplicateUpload, true);
				}
			}

			if (!empty($details['map_images'])) {
				foreach ($details['map_images'] as $key => $image) {
					$imagePath = $path . $image;

					$duplicateUpload = $this->crm_library->duplicate_upload($imagePath, $path, null, true);

					if ($duplicateUpload) {
						$details['map_images'][$key] = $duplicateUpload;
					}

					//duplicate thumb
					$thumbPath = $path . 'thumb.' . $image;
					$this->crm_library->duplicate_upload($thumbPath, $path, 'thumb.' . $duplicateUpload, true);
				}
			}

			$data['details'] = serialize($details);
		}

		$affectedRows = $this->OrgsSafetyModel->create($data);

		// if inserted/updated
		if ($affectedRows) {
			$this->session->set_flashdata('success', $docInfo->date . ' has been duplicated successfully.');
		}

		redirect($redirect_to);
	}

	/**
	 * edit a school induction
	 * @param  int $docID
	 * @param int $orgID
	 * @return void
	 */
	public function school($docID = NULL, $orgID = NULL)
	{

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		$doc_info = new stdClass();

		// check if editing
		if ($docID != NULL) {

			// check if numeric
			if (!ctype_digit($docID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'docID' => $docID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$doc_info = $row;
				$orgID = $doc_info->orgID;
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
		$title = 'New School Induction';
		if ($docID != NULL) {
			$submit_to = 'customers/safety/school/' . $docID;
			$title = mysql_to_uk_date($doc_info->date);
		} else {
			$submit_to = 'customers/safety/school/' . $orgID . '/new/';
		}
		$return_to = 'customers/safety/' . $orgID;
		$icon = 'book';
		$tab = 'safety';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/safety/' . $orgID] = 'Health & Safety';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if not new, unserialize details
		if ($docID != NULL) {
			$doc_info->details = @$this->crm_library->mb_unserialize($doc_info->details);
			if (!is_array($doc_info->details)) {
				$doc_info->details = array();
			}
		}

		// brands
		$brand_where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_brand_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_brand_where[] = '`brandID` = ' . $this->db->escape($doc_info->brandID);
		}
		$brand_where['(' . implode(' OR ', $or_brand_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($brand_where, NULL, FALSE)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('addressID', 'Address', 'trim|xss_clean|required');
			$this->form_validation->set_rules('typeID', 'Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('byID', 'Assessor ', 'trim|xss_clean|required');
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('expiry', 'Expiry', 'trim|xss_clean|required|callback_check_date');

			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
			$this->form_validation->set_rules('fire_alarm_tests', 'Alarm Tests (When)', 'trim|xss_clean');
			$this->form_validation->set_rules('fire_assembly_points', 'Assembly Points', 'trim|xss_clean');
			$this->form_validation->set_rules('fire_procedure', 'Procedure', 'trim|xss_clean');
			$this->form_validation->set_rules('accident_reporting_procedure', 'Reporting Procedure', 'trim|xss_clean');
			$this->form_validation->set_rules('accident_book', 'Specify location of accident reporting books', 'trim|xss_clean');
			$this->form_validation->set_rules('accident_contact', 'Specify the relevant school contact', 'trim|xss_clean');
			$this->form_validation->set_rules('behaviour_rewards', 'Rewards', 'trim|xss_clean');
			$this->form_validation->set_rules('behaviour_procedure', 'Procedures for bad behaviour', 'trim|xss_clean');
			$this->form_validation->set_rules('behaviour_sen_medical', 'SEN & Medical Information', 'trim|xss_clean');
			$this->form_validation->set_rules('further_dos_donts', 'Schools Do\'s and Don\'ts', 'trim|xss_clean');
			$this->form_validation->set_rules('further_helpful_info', 'Helpful delivery info', 'trim|xss_clean');
			$this->form_validation->set_rules('further_behaviour', 'Overview on the schools behaviour', 'trim|xss_clean');
			$this->form_validation->set_rules('further_carpark', 'Car Park Open and Close Times', 'trim|xss_clean');
			$this->form_validation->set_rules('equipment_additional', 'Any Additional Equipment', 'trim|xss_clean');
			$this->form_validation->set_rules('further_comments', 'Comments', 'trim|xss_clean');
			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand'), 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'byID' => set_value('byID'),
					'addressID' => set_value('addressID'),
					'typeID' => NULL,
					'date' => uk_to_mysql_date(set_value('date')),
					'expiry' => uk_to_mysql_date(set_value('expiry')),
					'type' => 'school induction',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID,
					'brandID' => set_value('brandID')
				);

				if ($docID == NULL) {
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if (set_value('typeID') != '') {
					$data['typeID'] = set_value('typeID');
				}

				$map_images = array();
				$venue_images = array();

				// details
				$details = array(
					'location' => set_value('location'),
					'fire_alarm_tests' => $this->input->post('fire_alarm_tests'),
					'fire_assembly_points' => $this->input->post('fire_assembly_points'),
					'fire_procedure' => $this->input->post('fire_procedure'),
					'accident_reporting_procedure' => $this->input->post('accident_reporting_procedure'),
					'accident_book' => $this->input->post('accident_book'),
					'accident_contact' => $this->input->post('accident_contact'),
					'behaviour_rewards' => $this->input->post('behaviour_rewards'),
					'behaviour_procedure' => $this->input->post('behaviour_procedure'),
					'behaviour_sen_medical' => $this->input->post('behaviour_sen_medical'),
					'further_dos_donts' => $this->input->post('further_dos_donts'),
					'further_helpful_info' => $this->input->post('further_helpful_info'),
					'further_behaviour' => $this->input->post('further_behaviour'),
					'further_carpark' => $this->input->post('further_carpark'),
					'equipment' => $this->input->post('equipment'),
					'equipment_details' => $this->input->post('equipment_details'),
					'equipment_additional' => $this->input->post('equipment_additional'),
					'further_comments' => $this->input->post('further_comments'),
					'venue_images' => $this->input->post('venue_images'),
					'map_images' => $this->input->post('map_images'),
				);

				// final check for errors
				if (count($errors) == 0) {

					$path = UPLOADPATH . 'orgs/' . $orgID . '/safety/';

					// venue images
					for ($i = 0; $i < 5; $i++) {

						$delete_venue_images = $this->input->post('delete_venue_images');

						// delete if requested
						if (is_array($delete_venue_images) && array_key_exists($i, $delete_venue_images) && file_exists($path . $delete_venue_images[$i])) {
								unlink($path . $delete_venue_images[$i]);
								if (file_exists($path . 'thumb.' . $delete_venue_images[$i])) {
									unlink($path . 'thumb.' . $delete_venue_images[$i]);
								}
								unset($details['venue_images'][$i]);
						}

						// upload image
						$upload_res = $this->crm_library->handle_safety_upload($orgID, 'venue_images_' . $i);

						if ($upload_res !== NULL) {

							// save in array
							$details['venue_images'][$i] = $upload_res['file_name'];

							// delete previous file, if exists
							if (isset($doc_info->details['venue_images']) && is_array($doc_info->details['venue_images']) && array_key_exists($i, $doc_info->details['venue_images']) && file_exists($path . $doc_info->details['venue_images'][$i])) {
								unlink($path . $doc_info->details['venue_images'][$i]);
								if (file_exists($path . 'thumb.' . $doc_info->details['venue_images'][$i])) {
									unlink($path . 'thumb.' . $doc_info->details['venue_images'][$i]);
								}
							}
						}

					}

					// map images
					for ($i = 0; $i < 1; $i++) {

						$delete_map_images = $this->input->post('delete_map_images');

						// delete if requested
						if (is_array($delete_map_images) && array_key_exists($i, $delete_map_images) && file_exists($path . $delete_map_images[$i])) {
								unlink($path . $delete_map_images[$i]);
								if (file_exists($path . 'thumb.' . $delete_map_images[$i])) {
									unlink($path . 'thumb.' . $delete_map_images[$i]);
								}
								unset($details['map_images'][$i]);
						}

						// upload image
						$upload_res = $this->crm_library->handle_safety_upload($orgID, 'map_images_' . $i);

						if ($upload_res !== NULL) {

							// save in array
							$details['map_images'][$i] = $upload_res['file_name'];

							// delete previous file, if exists
							if (isset($doc_info->details['map_images']) && is_array($doc_info->details['map_images']) && array_key_exists($i, $doc_info->details['map_images']) && file_exists($path . $doc_info->details['map_images'][$i])) {
								unlink($path . $doc_info->details['map_images'][$i]);
								if (file_exists($path . 'thumb.' . $doc_info->details['map_images'][$i])) {
									unlink($path . 'thumb.' . $doc_info->details['map_images'][$i]);
								}
							}
						}

					}

					$data['details'] = serialize($details);

					if ($docID == NULL) {
						// insert
						$query = $this->db->insert('orgs_safety', $data);

						$affected_rows = $this->db->affected_rows();

					} else {
						$where = array(
							'docID' => $docID
						);

						// update
						$query = $this->db->update('orgs_safety', $data, $where);

						$affected_rows = $this->db->affected_rows();

						// set previous reads of this doc to outdated so have to reconfirm
						$res = $this->db->update('orgs_safety_read', array('outdated' => 1), array('docID' => $docID));
					}

					// if inserted/updated
					if ($affected_rows == 1) {

						if ($orgID == NULL) {
							$this->session->set_flashdata('success', set_value('date') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('date') . ' has been updated successfully.');
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

		// get data
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();

		// session types
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_where[] = '`typeID` = ' . $this->db->escape($doc_info->typeID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$lesson_types = $this->db->from('lesson_types')->where($where, NULL, FALSE)->order_by('name asc')->get();

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
			'doc_info' => $doc_info,
			'org_id' => $orgID,
			'addresses' => $addresses,
			'staff' => $staff,
			'breadcrumb_levels' => $breadcrumb_levels,
			'lesson_types' => $lesson_types,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'brands' => $brands
		);

		// load view
		$this->crm_view('customers/safety-school', $data);
	}

	/**
	 * edit a risk assessment
	 * @param  int $docID
	 * @param int $orgID
	 * @return void
	 */
	public function risk($docID = NULL, $orgID = NULL)
	{

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		$doc_info = new stdClass();

		// check if editing
		if ($docID != NULL) {

			// check if numeric
			if (!ctype_digit($docID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'docID' => $docID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$doc_info = $row;
				$orgID = $doc_info->orgID;
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
		$title = 'New Risk Assessment';
		if ($docID != NULL) {
			$submit_to = 'customers/safety/risk/' . $docID;
			$title = mysql_to_uk_date($doc_info->date);
		} else {
			$submit_to = 'customers/safety/risk/' . $orgID . '/new/';
		}
		$return_to = 'customers/safety/' . $orgID;
		$icon = 'book';
		$tab = 'safety';
		$current_page = $org_info->type . 's';
		$breadcrumb_levels = array();
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
			$breadcrumb_levels['customers/prospects/' . $org_info->type . 's'] = 'Prospective ' . ucwords($org_info->type) . 's';
		} else {
			$breadcrumb_levels['customers/' . $org_info->type . 's'] = ucwords($org_info->type) . 's';
		}
		$breadcrumb_levels['customers/edit/' . $orgID] = $org_info->name;
		$breadcrumb_levels['customers/safety/' . $orgID] = 'Health & Safety';
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if not new, unserialize details
		if ($docID != NULL) {
			$doc_info->details = @$this->crm_library->mb_unserialize($doc_info->details);
			if (!is_array($doc_info->details)) {
				$doc_info->details = array();
			}
		}

		// brands
		$brand_where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_brand_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_brand_where[] = '`brandID` = ' . $this->db->escape($doc_info->brandID);
		}
		$brand_where['(' . implode(' OR ', $or_brand_where) . ')'] = NULL;
		$brands = $this->db->from('brands')->where($brand_where, NULL, FALSE)->order_by('name asc')->get();

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('addressID', 'Address', 'trim|xss_clean|required');
			$this->form_validation->set_rules('typeID', 'Session Type', 'trim|xss_clean');
			$this->form_validation->set_rules('byID', 'Assessor ', 'trim|xss_clean|required');
			$this->form_validation->set_rules('date', 'Date', 'trim|xss_clean|required|callback_check_date');
			$this->form_validation->set_rules('expiry', 'Expiry', 'trim|xss_clean|required|callback_check_date');

			$this->form_validation->set_rules('location', 'Location', 'trim|xss_clean');
			$this->form_validation->set_rules('who', 'Person/Group at Risk', 'trim|xss_clean|required');
			$this->form_validation->set_rules('final', 'Final Assessment & Comments', 'trim|xss_clean|required');

			$this->form_validation->set_rules('brandID', $this->settings_library->get_label('brand'), 'trim|xss_clean|required');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'byID' => set_value('byID'),
					'addressID' => set_value('addressID'),
					'typeID' => NULL,
					'date' => uk_to_mysql_date(set_value('date')),
					'expiry' => uk_to_mysql_date(set_value('expiry')),
					'type' => 'risk assessment',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID,
					'brandID' => set_value('brandID')
				);

				if ($docID == NULL) {
					$data['orgID'] = $orgID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				if (set_value('typeID') != '') {
					$data['typeID'] = set_value('typeID');
				}

				$map_images = array();
				$venue_images = array();

				// details
				$details = array(
					'location' => set_value('location'),
					'who' => set_value('who'),
					'final' => $this->input->post('final')
				);

				// final check for errors
				if (count($errors) == 0) {

					$data['details'] = serialize($details);

					if ($docID == NULL) {
						// insert
						$query = $this->db->insert('orgs_safety', $data);
						$docID = $this->db->insert_id();

						$affected_rows = $this->db->affected_rows();

					} else {
						$where = array(
							'docID' => $docID
						);

						// update
						$query = $this->db->update('orgs_safety', $data, $where);

						$affected_rows = $this->db->affected_rows();

						// set previous reads of this doc to outdated so have to reconfirm
						$res = $this->db->update('orgs_safety_read', array('outdated' => 1), array('docID' => $docID));
					}

					// if inserted/updated
					if ($affected_rows == 1) {

						if ($orgID == NULL) {
							$this->session->set_flashdata('success', set_value('date') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('date') . ' has been updated successfully.');
						}

						redirect('customers/safety/risk/' . $docID);

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

		// get data
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();
		$where = array(
			'docID' => $docID,
			'accountID' => $this->auth->user->accountID
		);
		$hazards = $this->db->from('orgs_safety_hazards')->where($where)->order_by('added asc')->get();

		// session types
		$where = array(
			'accountID' => $this->auth->user->accountID
		);
		$or_where = [
			'`active` = 1'
		];
		if ($docID != NULL) {
			$or_where[] = '`typeID` = ' . $this->db->escape($doc_info->typeID);
		}
		$where['(' . implode(' OR ', $or_where) . ')'] = NULL;
		$lesson_types = $this->db->from('lesson_types')->where($where, NULL, FALSE)->order_by('name asc')->get();

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
			'doc_info' => $doc_info,
			'doc_id' => $docID,
			'org_id' => $orgID,
			'addresses' => $addresses,
			'staff' => $staff,
			'hazards' => $hazards,
			'breadcrumb_levels' => $breadcrumb_levels,
			'lesson_types' => $lesson_types,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'brands' => $brands
		);

		// load view
		$this->crm_view('customers/safety-risk', $data);
	}

	/**
	 * edit a hazard
	 * @param  int $hazardID
	 * @param int $docID
	 * @return void
	 */
	public function hazard($hazardID = NULL, $docID = NULL)
	{

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		$hazard_info = new stdClass();

		// check if editing
		if ($hazardID != NULL) {

			// check if numeric
			if (!ctype_digit($hazardID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'hazardID' => $hazardID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('orgs_safety_hazards')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$hazard_info = $row;
				$docID = $hazard_info->docID;
			}

		}

		// required
		if ($docID == NULL) {
			show_404();
		}

		// look up doc
		$where = array(
			'docID' => $docID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$doc_info = $row;
			$orgID = $doc_info->orgID;
		}

		// required
		if ($orgID == NULL) {
			show_404();
		}

		// look up doc
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
		$title = 'New Hazard';
		if ($hazardID != NULL) {
			$submit_to = 'customers/safety/hazard/' . $hazardID;
			$title = $hazard_info->hazard;
		} else {
			$submit_to = 'customers/safety/hazard/' . $docID . '/new/';
		}
		$return_to = 'customers/safety/risk/' . $docID . '#hazards';
		$icon = 'book';
		$tab = 'safety';
		$current_page = $org_info->type . 's';
		if ($org_info->prospect == 1) {
			$current_page = 'prospective-' . $current_page;
		}
		$section = 'customers';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;

		// if posted
		if ($this->input->post()) {

			// set validation rules
			$this->form_validation->set_rules('hazard', 'Hazard', 'trim|xss_clean|required');
			$this->form_validation->set_rules('potential_effect', 'Potential Effect', 'trim|xss_clean|required');
			$this->form_validation->set_rules('likelihood', 'Likelihood', 'trim|xss_clean|required|greater_than[0]|less_than[6]');
			$this->form_validation->set_rules('severity', 'Severity', 'trim|xss_clean|required|greater_than[0]|less_than[6]');
			$this->form_validation->set_rules('risk', 'Risk', 'trim|xss_clean|required|greater_than[0]|less_than[26]');
			$this->form_validation->set_rules('control_measures', 'Control Measures', 'trim|xss_clean|required');
			$this->form_validation->set_rules('residual_risk', 'Residual Risk', 'trim|xss_clean|required|greater_than[0]|less_than[26]');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, prepare data
				$data = array(
					'hazard' => set_value('hazard'),
					'potential_effect' => $this->input->post('potential_effect'),
					'likelihood' => set_value('likelihood'),
					'severity' => set_value('severity'),
					'risk' => set_value('risk'),
					'control_measures' => $this->input->post('control_measures'),
					'residual_risk' => set_value('residual_risk'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if ($hazardID == NULL) {
					$data['orgID'] = $orgID;
					$data['docID'] = $docID;
					$data['byID'] = $this->auth->user->staffID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// final check for errors
				if (count($errors) == 0) {

					if ($hazardID == NULL) {
						// insert
						$query = $this->db->insert('orgs_safety_hazards', $data);

						$affected_rows = $this->db->affected_rows();

					} else {
						$where = array(
							'hazardID' => $hazardID
						);

						// update
						$query = $this->db->update('orgs_safety_hazards', $data, $where);

						$affected_rows = $this->db->affected_rows();
					}

					// set previous reads of this doc to outdated so have to reconfirm
					$where = array(
						'docID' => $docID,
						'accountID' => $this->auth->user->accountID
					);
					$res = $this->db->update('orgs_safety_read', array('outdated' => 1), $where);

					// if inserted/updated
					if ($affected_rows == 1) {

						if ($hazardID == NULL) {
							$this->session->set_flashdata('success', set_value('hazard') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('hazard') . ' has been updated successfully.');
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

		// get data
		$where = array(
			'orgID' => $orgID,
			'accountID' => $this->auth->user->accountID
		);
		$addresses = $this->db->from('orgs_addresses')->where($where)->order_by('address1 asc, address2 asc, address3 asc')->get();
		$where = array(
			'active' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$staff = $this->db->from('staff')->where($where)->order_by('first asc, surname asc')->get();
		$where = array(
			'docID' => $docID,
			'accountID' => $this->auth->user->accountID
		);
		$hazards = $this->db->from('orgs_safety_hazards')->where($where)->order_by('added asc')->get();

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
			'hazard_info' => $hazard_info,
			'doc_id' => $docID,
			'org_id' => $orgID,
			'addresses' => $addresses,
			'staff' => $staff,
			'hazards' => $hazards,
			'success' => $success,
			'errors' => $errors,
			'info' => $info
		);

		// load view
		$this->crm_view('customers/safety-hazard', $data);
	}

	/**
	 * delete a hazard
	 * @param  int $hazardID
	 * @return mixed
	 */
	public function remove_hazard($hazardID = NULL) {

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		// check params
		if (empty($hazardID)) {
			show_404();
		}

		$where = array(
			'hazardID' => $hazardID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_safety_hazards')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$hazard_info = $row;

			// all ok, delete
			$query = $this->db->delete('orgs_safety_hazards', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $hazard_info->hazard . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $hazard_info->hazard . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/safety/risk/' . $hazard_info->docID . '#hazards';

			redirect($redirect_to);
		}
	}

	/**
	 * view a document
	 * @param  int $docID
	 * @return void
	 */
	public function view($docID = NULL)
	{

		// check params
		if (empty($docID)) {
			show_404();
		}

		// check if numeric
		if (!ctype_digit($docID)) {
			show_404();
		}

		// if so, check user exists
		$where = array(
			'orgs_safety.docID' => $docID,
			'orgs_safety.accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->select('orgs_safety.*, staff.first, staff.surname, orgs_addresses.address1, orgs_addresses.address2, orgs_addresses.address3, orgs_addresses.town, orgs_addresses.county, orgs_addresses.postcode, orgs.name as org')->from('orgs_safety')->join('staff', 'orgs_safety.byID = staff.staffID', 'inner')->join('orgs_addresses', 'orgs_safety.addressID = orgs_addresses.addressID', 'inner')->join('orgs', 'orgs_safety.orgID = orgs.orgID', 'inner')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$doc_info = $row;
		}

		// if in, coaching, fulltime coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			// check if has sessions at this address, asssume not
			$has_pemission = FALSE;

			// check if has normal sessions at this address
			$where = array(
				'bookings_lessons_staff.staffID' => $this->auth->user->staffID,
				'bookings_lessons.addressID' => $doc_info->addressID,
				'bookings_lessons.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('bookings_lessons_staff.*')->from('bookings_lessons_staff')->join('bookings_lessons', 'bookings_lessons_staff.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->limit(1)->get();

			if ($res->num_rows() > 0) {
				$has_pemission = TRUE;
			}

			// check if has event at this address
			$where = array(
				'bookings_lessons_staff.staffID' => $this->auth->user->staffID,
				'bookings.addressID' => $doc_info->addressID,
				'bookings.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('bookings_lessons_staff.*')->from('bookings_lessons_staff')->join('bookings', 'bookings_lessons_staff.bookingID = bookings.bookingID')->where($where)->get();

			if ($res->num_rows() > 0) {
				$has_pemission = TRUE;
			}

			// check if has normal sessions at this address (via exception)
			$where = array(
				'bookings_lessons_exceptions.staffID' => $this->auth->user->staffID,
				'bookings_lessons.addressID' => $doc_info->addressID,
				'bookings_lessons.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('bookings_lessons_exceptions.*')->from('bookings_lessons_exceptions')->join('bookings_lessons', 'bookings_lessons_exceptions.lessonID = bookings_lessons.lessonID', 'inner')->where($where)->get();

			if ($res->num_rows() > 0) {
				$has_pemission = TRUE;
			}

			// check if has event at this address (via exception)
			$where = array(
				'bookings_lessons_exceptions.staffID' => $this->auth->user->staffID,
				'bookings.addressID' => $doc_info->addressID,
				'bookings.accountID' => $this->auth->user->accountID
			);

			$res = $this->db->select('bookings_lessons_exceptions.*')->from('bookings_lessons_exceptions')->join('bookings', 'bookings_lessons_exceptions.bookingID = bookings.bookingID')->where($where)->get();

			if ($res->num_rows() > 0) {
				$has_pemission = TRUE;
			}

			if ($has_pemission !== TRUE) {
				show_404();
			}
		}

		// get hazards
		$hazards = NULL;

		if ($doc_info->type == 'risk assessment') {
			$where = array(
				'docID' => $docID,
				'accountID' => $this->auth->user->accountID
			);
			$hazards = $this->db->from('orgs_safety_hazards')->where($where)->order_by('added asc')->get();
		}

		// check if confirmed
		$where = array(
			'staffID' => $this->auth->user->staffID,
			'docID' => $docID,
			'outdated !=' => 1,
			'accountID' => $this->auth->user->accountID
		);
		$confirmed = $this->db->from('orgs_safety_read')->where($where)->limit(1)->get();

		$doc_info->details = @$this->crm_library->mb_unserialize($doc_info->details);
		// if no or corrupt details, set as empty array
		if (!is_array($doc_info->details)) {
			$doc_info->details = array();
		}

		// prepare data for view
		$data = array(
			'doc_info' => $doc_info,
			'hazards' => $hazards,
			'confirmed' => $confirmed
		);

		$view_type = NULL;
		switch ($doc_info->type) {
			case 'camp induction':
				$view_type = 'camp';
				break;
			case 'school induction':
				$view_type = 'school';
				break;
			case 'risk assessment':
				$view_type = 'risk';
				break;
		}

		// load view
		$this->load->view('customers/safety-' . $view_type . '-view', $data);
	}

	/**
	 * confirm a document
	 * @param  int $docID
	 * @return mixed
	 */
	public function confirm($docID = NULL) {

		// check params
		if (empty($docID)) {
			show_404();
		}

		$where = array(
			'docID' => $docID,
			'staffID' => $this->auth->user->staffID,
			'outdated !=' => 1,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_safety_read')->where($where)->limit(1)->get();

		// already confirmed
		if ($query->num_rows() > 0) {
			show_404();
		}

		$data = array(
			'docID' => $docID,
			'staffID' => $this->auth->user->staffID,
			'outdated' => 0,
			'date' =>mdate('%Y-%m-%d %H:%i:%s'),
			'accountID' => $this->auth->user->accountID
		);

		// update
		$query = $this->db->insert('orgs_safety_read', $data);

		// determine which page to send the user back to
		$redirect_to = 'customers/safety/view/' . $docID . '#confirmation';

		redirect($redirect_to);
	}

	/**
	 * delete a document
	 * @param  int $docID
	 * @return mixed
	 */
	public function remove($docID = NULL) {

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		// check params
		if (empty($docID)) {
			show_404();
		}

		$where = array(
			'docID' => $docID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$doc_info = $row;

			// all ok, delete
			$query = $this->db->delete('orgs_safety', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', mysql_to_uk_date($doc_info->date) . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', mysql_to_uk_date($doc_info->date) . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'customers/safety/' . $doc_info->orgID;

			redirect($redirect_to);
		}
	}

	/**
	 * mark as renewed
	 * @param  int $docID
	 * @param string $value
	 * @return mixed
	 */
	public function renewed($docID = NULL, $value = NULL) {

		// deny from coaches + full time coach
		if (in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			show_404();
		}

		// check params
		if (empty($docID) || !in_array($value, array('yes', 'no'))) {
			show_404();
		}

		$where = array(
			'docID' => $docID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$doc_info = $row;

			$data = array(
				'renewed' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			if ($value == 'yes') {
				$data['renewed'] = 1;
			}

			// run query
			$query = $this->db->update('orgs_safety', $data, $where);

			// check results
			if ($this->db->affected_rows() > 0) {
				echo 'OK';
				exit();
			}
		}

	}

	/**
	 * check date is correct
	 * @param  string $date
	 * @return bool
	 */
	public function check_date($date) {

		// date not required
		if (empty($date)) {
			return TRUE;
		}

		// if set, check
		if (check_uk_date($date)) {
			return TRUE;
		}

		return FALSE;

	}

	/**
	 * jump to safety doc within list
	 * @param  int $doc_id
	 * @return mixed
	 */
	public function jumpto($doc_id = NULL) {

		// check params
		if (empty($doc_id)) {
			show_404();
		}

		// look up
		$where = array(
			'docID' => $doc_id,
			'accountID' => $this->auth->user->accountID
		);

		$res = $this->db->from('orgs_safety')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $doc_info) {}

		// build search
		$search_fields = array(
			'addressID' => $doc_info->addressID,
			'type' => $doc_info->type,
			'search' => 'true'
		);

		// determine destination
		$redirect_to = 'customers/safety/' . $doc_info->orgID;

		// store search fields
		$this->session->set_userdata('search-customer-safety', $search_fields);

		// go
		redirect($redirect_to . '/recall');
	}

}

/* End of file notes.php */
/* Location: ./application/controllers/customers/notes.php */
