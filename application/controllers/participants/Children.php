<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Children extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * edit an child
	 * @param  int $childID
	 * @param int $familyID
	 * @return void
	 */
	public function edit($childID = NULL, $familyID = NULL)
	{

		$child_info = new stdClass();

		$fields = get_fields('participant');

		// check if editing
		if ($childID != NULL) {

			// check if numeric
			if (!ctype_digit($childID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'childID' => $childID,
				'accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->from('family_children')->where($where)->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$child_info = $row;
				$familyID = $child_info->familyID;

				// get child tags
				$child_info->tags = array();
				$where = array(
					'family_children_tags.accountID' => $this->auth->user->accountID,
					'family_children_tags.childID' => $childID
				);
				$res = $this->db->select('settings_tags.*')->from('family_children_tags')->join('settings_tags', 'family_children_tags.tagID = settings_tags.tagID', 'inner')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$child_info->tags[] = $row->name;
					}
				}

				//Get disabilities
				$child_info->disability = array();
				$where = array(
					'accountID' => $this->auth->user->accountID,
					'childID' => $childID
				);
				$res = $this->db->select('*')->from('family_disabilities')->where($where)->limit(1)->get();
				if ($res->num_rows() > 0) {
					$child_info->disability = $res->result()[0];
				}
			}

		}

		// required
		if ($familyID == NULL) {
			show_404();
		}

		// look up family
		$where = array(
			'familyID' => $familyID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$family_info = $row;
		}

		$where['main'] = 1;
		// Get Account Holder Info
		$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();
		foreach($query->result() as $row){
			$account_holder_info = $row;
		}


		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Participant';
		if ($childID != NULL) {
			$submit_to = 'participants/participant/edit/' . $childID;
			$title = $child_info->first_name . ' ' . $child_info->last_name;
		} else {
			$submit_to = 'participants/participant/' . $familyID . '/new/';
		}
		$return_to = 'participants/view/' . $familyID;
		$icon = 'child';
		$tab = 'details';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$add_school = 0;
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'New Participant'
 		);
		if ($familyID != NULL) {
			$breadcrumb_levels = array(
				'participants' => 'Participants',
				'participants/view/' . $familyID => 'Participant'
			);
		}

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

		// check if emergency contact 1 required - only for new children and if existing ones already have it
		$emergency_contact_1_required = FALSE;
		if ($childID == NULL || (isset($child_info->emergency_contact_1_name) && !empty($child_info->emergency_contact_1_name))) {
			$emergency_contact_1_required = TRUE;
		}

		// if posted
		if ($this->input->post()) {

			if ($this->input->post('add_school') == 1) {
				$add_school = 1;
			}

			// set validation rules
			$this->form_validation->set_rules('first_name', field_label('first_name', $fields, TRUE), 'trim|xss_clean' . required_field('first_name', $fields, 'validation'));
			$this->form_validation->set_rules('last_name', field_label('last_name', $fields, TRUE), 'trim|xss_clean' . required_field('last_name', $fields, 'validation'));
			$this->form_validation->set_rules('dob', field_label('dob', $fields, TRUE), 'trim|xss_clean' . required_field('dob', $fields, 'validation') . '|callback_check_dob');
			$this->form_validation->set_rules('gender', field_label('gender', $fields, TRUE), 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
			$this->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . ($this->input->post('gender')=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));

			if ($add_school != 1) {
				$this->form_validation->set_rules('orgID', field_label('orgID', $fields, TRUE), 'trim|xss_clean' . required_field('orgID', $fields, 'validation'));
			} else {
				$this->form_validation->set_rules('new_school', 'School', 'trim|xss_clean|required');
			}
			$this->form_validation->set_rules('medical', field_label('medical', $fields, TRUE), 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$disabilityFailed = false;
			if (empty($this->input->post('disability')) && required_field('disability', $fields)) {
				$disabilityFailed = true;
			}
			$this->form_validation->set_rules('behavioural_information', field_label('behavioural_information', $fields, TRUE), 'trim|xss_clean' . required_field('behavioural_information', $fields, 'validation'));
			$this->form_validation->set_rules('disability_info', field_label('disability_info', $fields, TRUE), 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
			$this->form_validation->set_rules('ethnic_origin', field_label('ethnic_origin', $fields, TRUE), 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
			$this->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
			$this->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . ($this->input->post('religion_specify')=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('photoConsent', field_label('photoConsent', $fields, TRUE), 'trim|xss_clean' . required_field('photoConsent', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_name', field_label('emergency_contact_1_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_phone', field_label('emergency_contact_1_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_phone', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_name', field_label('emergency_contact_2_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_phone', field_label('emergency_contact_2_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_phone', $fields, 'validation'));
			$this->form_validation->set_rules('profile_pic', field_label('profile_pic', $fields, TRUE), 'trim|xss_clean' . required_field('profile_pic', $fields, 'validation'));
			$this->form_validation->set_rules('tags', field_label('tags', $fields, TRUE), 'trim|xss_clean' . required_field('tags', $fields, 'validation'));
			$this->form_validation->set_rules('pin', field_label('pin', $fields, TRUE), 'trim|xss_clean' . required_field('pin', $fields, 'validation'));

			if ($this->form_validation->run() == FALSE || $disabilityFailed) {
				$errors = $this->form_validation->error_array();
				if ($disabilityFailed) {
					$errors[] = 'Disability is required';
				}
			} else {

				// all ok, prepare data
				$data = array(
					'first_name' => set_value('first_name'),
					'last_name' => set_value('last_name'),
					'gender' => NULL,
					'gender_specify' => NULL,
					'dob' => uk_to_mysql_date(set_value('dob')),
					'orgID' => set_value('orgID'),
					'medical' => set_value('medical'),
					'pin' => set_value('pin'),
					'behavioural_info' => set_value('behavioural_information'),
					'disability_info' => set_value('disability_info'),
					'ethnic_origin' => set_value('ethnic_origin'),
					'religion' => NULL,
					'religion_specify' => NULL,
					'photoConsent' => 0,
					'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				if (in_array(set_value('gender'), array('male', 'female', 'please_specify', 'other'))) {
					$data['gender'] = set_value('gender');
					if (set_value('gender')=="please_specify") {
						$data['gender_specify'] = set_value('gender_specify');
					}
				}

				if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
					$data['religion'] = set_value('religion');
					if (set_value('religion')=="please_specify") {
						$data['religion_specify'] = set_value('religion_specify');
					}
				}

				if (set_value('photoConsent') == 1) {
					$data['photoConsent'] = 1;
				}

				if ($childID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['familyID'] = $familyID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// update profile picture
				$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->auth->user->accountID, 500, 500, 50, 50, TRUE);

				if ($upload_res !== NULL) {
					$image_data = array(
						'name' => $upload_res['client_name'],
						'path' => $upload_res['raw_name'],
						'type' => $upload_res['file_type'],
						'size' => $upload_res['file_size']*1024,
						'ext' => substr($upload_res['file_ext'], 1)
					);
					$data['profile_pic'] = serialize($image_data);
				}

				// final check for errors
				if (count($errors) == 0) {

					// insert school
					if ($add_school == 1) {
						$school_data = array(
							'byID' => $this->auth->user->staffID,
							'name' => set_value('new_school'),
							'prospect' => 1,
							'partnership' => 0,
							'type' => 'school',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						$this->db->insert('orgs', $school_data);

						$data['orgID'] = $this->db->insert_id();

						// insert empty address
						$school_address_data = array(
							'orgID' => $data['orgID'],
							'byID' => $this->auth->user->staffID,
							'type' => 'main',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID
						);

						$this->db->insert('orgs_addresses', $school_address_data);

					}

					if ($childID == NULL) {
						// insert
						$query = $this->db->insert('family_children', $data);
						$childID = $this->db->insert_id();
						$successful = (bool)($this->db->affected_rows() == 1);
					} else {
						$where = array(
							'childID' => $childID,
							'accountID' => $this->auth->user->accountID
						);

						// update
						$query = $this->db->update('family_children', $data, $where);
						$successful = (bool)($this->db->affected_rows() == 1);

						//Delete disability data so it can be updated
						$where = array(
							'accountID' => $this->auth->user->accountID,
							'childID' => $childID
						);
						$query = $this->db->delete('family_disabilities', $where, 1);
					}

					//Add disability data
					if (is_array($this->input->post('disability'))) {
						$disability_data = array(
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID,
							'childID' => $childID
						);
						foreach ($this->input->post('disability') as $disability => $v) {
							$disability_data[$disability] = ($v=="1" ? 1 : NULL);
						}

						$this->db->insert('family_disabilities', $disability_data);
					}

					// if inserted/updated
					if ($successful) {

						// add/update tags
						$tags = $this->input->post('tags');
						if (!is_array($tags)) {
							$tags = array();
						}
						// remove existing
						$where = array(
							'childID' => $childID,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->delete('family_children_tags', $where);
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
									'childID' => $childID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);
								$this->db->insert('family_children_tags', $data);
							}
						}

						if ($familyID == NULL) {
							$this->session->set_flashdata('success', set_value('first_name') . ' ' . set_value('last_name') . ' has been created successfully.');
						} else {
							$this->session->set_flashdata('success', set_value('first_name') . ' ' . set_value('last_name') . ' has been updated successfully.');
						}

						redirect($return_to);

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

		// schools
		$where = array(
			'type' => 'school',
			'accountID' => $this->auth->user->accountID
		);
		$schools = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

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
			'child_info' => $child_info,
			'schools' => $schools,
			'familyID' => $familyID,
			'add_school' => $add_school,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'tag_list' => $tag_list,
			'fields' => $fields,
			'account_holder_info' => $account_holder_info,
			'emergency_contact_1_required' => $emergency_contact_1_required
		);

		// load view
		$this->crm_view('participants/child', $data);
	}

	/**
	 * delete an child
	 * @param  int $familyID
	 * @return mixed
	 */
	public function remove($childID = NULL) {

		// check params
		if (empty($childID)) {
			show_404();
		}

		$where = array(
			'childID' => $childID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_children')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$child_info = $row;

			// all ok, delete
			$query = $this->db->delete('family_children', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $child_info->first_name . ' ' . $child_info->last_name . ' has been removed successfully.');
			} else {
				$this->session->set_flashdata('error', $child_info->first_name . ' ' . $child_info->last_name . ' could not be removed.');
			}

			// determine which page to send the user back to
			$redirect_to = 'participants/view/' . $child_info->familyID;

			redirect($redirect_to);
		}
	}

	/**
	 * validation function for check a dob is valid and in past
	 * @param  string $date
	 * @return bool
	 */
	public function check_dob($date) {

		// valid if empty
		if (empty($date)) {
			return TRUE;
		}

		// check valid date
		if (!check_uk_date($date)) {
			return FALSE;
		}

		// check date is in future
		if (strtotime(uk_to_mysql_date($date)) > time()) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file children.php */
/* Location: ./application/controllers/participants/children.php */
