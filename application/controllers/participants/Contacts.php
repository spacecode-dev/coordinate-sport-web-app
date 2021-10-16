<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contacts extends MY_Controller {

	public function __construct() {
		// deny from coaches + full time coach
		parent::__construct(FALSE, array('coaching', 'fulltimecoach'), array(), array('participants'));
	}

	/**
	 * edit a contact
	 * @param  int $contactID
	 * @param int $familyID
	 * @return void
	 */
	public function edit($contactID = NULL, $familyID = NULL)
	{

		$contact_info = new stdClass();

		$fields = get_fields('account_holder');

		// check if editing
		if ($contactID != NULL) {

			// check if numeric
			if (!ctype_digit($contactID)) {
				show_404();
			}

			// if so, check user exists
			$where = array(
				'family_contacts.contactID' => $contactID,
				'family_contacts.accountID' => $this->auth->user->accountID
			);

			// run query
			$query = $this->db->select('family_contacts.*, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('family_contacts_newsletters') . '.brandID SEPARATOR \',\') AS newsletters')->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'left')->from('family_contacts')->where($where)->group_by('family_contacts.contactID')->limit(1)->get();

			// no match
			if ($query->num_rows() == 0) {
				show_404();
			}

			// match
			foreach ($query->result() as $row) {
				$contact_info = $row;
				$familyID = $contact_info->familyID;

				// get contact tags
				$contact_info->tags = array();
				$where = array(
					'family_contacts_tags.accountID' => $this->auth->user->accountID,
					'family_contacts_tags.contactID' => $contactID
				);
				$res = $this->db->select('settings_tags.*')->from('family_contacts_tags')->join('settings_tags', 'family_contacts_tags.tagID = settings_tags.tagID', 'inner')->where($where)->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						$contact_info->tags[] = $row->name;
					}
				}

				//Get disabilities
				$contact_info->disability = array();
				$where = array(
					'accountID' => $this->auth->user->accountID,
					'contactID' => $contactID
				);
				$res = $this->db->select('*')->from('family_disabilities')->where($where)->limit(1)->get();
				if ($res->num_rows() > 0) {
					$contact_info->disability = $res->result()[0];
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

		// brands
		$where = array(
			'accountID' => $this->auth->user->accountID,
			'active' => 1,
			'mailchimp_id !=' => '',
			'mailchimp_id IS NOT NULL' => NULL
		);
		$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

		// load libraries
		$this->load->library('form_validation');

		// set defaults
		$title = 'New Account Holder';
		if ($contactID != NULL) {
			$submit_to = 'participants/contacts/edit/' . $contactID;
			$title = $contact_info->first_name . ' ' . $contact_info->last_name;
		} else {
			$submit_to = 'participants/contacts/' . $familyID . '/new/';
		}
		$return_to = 'participants/view/' . $familyID;
		$icon = 'user';
		$tab = 'details';
		$current_page = 'participants';
		$section = 'participants';
		$buttons = '<a class="btn" href="' . site_url($return_to) . '"><i class="far fa-angle-left"></i> Return to List</a>';
		$errors = array();
		$success = NULL;
		$info = NULL;
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $familyID => 'New Account Holder'
 		);
		if ($familyID != NULL) {
			$breadcrumb_levels = array(
				'participants' => 'Participants',
				'participants/view/' . $familyID => 'Account Holder'
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

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('title', field_label('title', $fields, TRUE), 'trim|xss_clean' . required_field('title', $fields, 'validation'));
			$this->form_validation->set_rules('first_name', field_label('first_name', $fields, TRUE), 'trim|xss_clean' . required_field('first_name', $fields, 'validation'));
			$this->form_validation->set_rules('last_name', field_label('last_name', $fields, TRUE), 'trim|xss_clean' . required_field('last_name', $fields, 'validation'));
			$this->form_validation->set_rules('gender', field_label('gender', $fields, TRUE), 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
			$this->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . ($this->input->post('gender')=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('gender_since_birth', 'Gender Since Birth', 'trim|xss_clean' . required_field('gender_since_birth', $fields, 'validation'));
			$this->form_validation->set_rules('sexual_orientation', 'Sexual Orientation', 'trim|xss_clean'. required_field('sexual_orientation', $fields, 'validation'));
			$this->form_validation->set_rules('sexual_orientation_specify', 'Specific Sexual Orientation', 'trim|xss_clean' . ($this->input->post('sexual_orientation')=="please_specify" ? required_field('sexual_orientation_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('dob', field_label('dob', $fields, TRUE), 'trim|xss_clean' . required_field('dob', $fields, 'validation') . '|callback_check_dob');
			$this->form_validation->set_rules('medical', field_label('medical', $fields, TRUE), 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$disabilityFailed = false;
			if (empty($this->input->post('disability')) && required_field('disability', $fields)) {
				$disabilityFailed = true;
			}
			$this->form_validation->set_rules('behavioural_information', field_label('behavioural_information', $fields, TRUE), 'trim|xss_clean' . required_field('behavioural_information', $fields, 'validation'));
			$this->form_validation->set_rules('disability_info', field_label('disability_info', $fields, TRUE), 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
			$this->form_validation->set_rules('ethnic_origin', field_label('ethnic_origin', $fields, TRUE), 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
			$this->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
			$this->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . ($this->input->post('religion')=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('eRelationship', field_label('eRelationship', $fields, TRUE), 'trim|xss_clean' . required_field('eRelationship', $fields, 'validation'));
			$this->form_validation->set_rules('address1', field_label('address1', $fields, TRUE), 'trim|xss_clean' . required_field('address1', $fields, 'validation'));
			$this->form_validation->set_rules('address2', field_label('address2', $fields, TRUE), 'trim|xss_clean' . required_field('address2', $fields, 'validation'));
			$this->form_validation->set_rules('address3', field_label('address3', $fields, TRUE), 'trim|xss_clean' . required_field('address3', $fields, 'validation'));
			$this->form_validation->set_rules('town', field_label('town', $fields, TRUE), 'trim|xss_clean' . required_field('town', $fields, 'validation'));
			$this->form_validation->set_rules('county', field_label('county', $fields, TRUE), 'trim|xss_clean' . required_field('county', $fields, 'validation'));
			$this->form_validation->set_rules('postcode', field_label('postcode', $fields, TRUE), 'trim|xss_clean' . required_field('postcode', $fields, 'validation') . '|callback_check_postcode');
			$this->form_validation->set_rules('mobile', field_label('mobile', $fields, TRUE), 'trim|xss_clean' . required_field('mobile', $fields, 'validation') . '|callback_check_mobile');
			$this->form_validation->set_rules('phone', field_label('phone', $fields, TRUE), 'trim|xss_clean' . required_field('phone', $fields, 'validation'));
			$this->form_validation->set_rules('workPhone', field_label('workPhone', $fields, TRUE), 'trim|xss_clean' . required_field('workPhone', $fields, 'validation'));
			$this->form_validation->set_rules('email', field_label('email', $fields, TRUE), 'trim|xss_clean' . required_field('email', $fields, 'validation') . '|valid_email|callback_check_email[' . $contactID . ']');
			$this->form_validation->set_rules('emergency_contact_1_name', field_label('emergency_contact_1_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_1_phone', field_label('emergency_contact_1_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_1_phone', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_name', field_label('emergency_contact_2_name', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_phone', field_label('emergency_contact_2_phone', $fields, TRUE), 'trim|xss_clean' . required_field('emergency_contact_2_phone', $fields, 'validation'));

			$this->form_validation->set_rules('password', 'Password', 'trim|xss_clean|min_length[8]|matches[password_confirm]');
			$this->form_validation->set_rules('password_confirm', 'Confirm Password', 'trim|xss_clean');

			$this->form_validation->set_rules('notify', field_label('notify', $fields, TRUE), 'trim|xss_clean' . required_field('notify', $fields, 'validation').'|callback_notify_need_password');
			$this->form_validation->set_rules('blacklisted', field_label('blacklisted', $fields, TRUE), 'trim|xss_clean' . required_field('blacklisted', $fields, 'validation'));

			$this->form_validation->set_rules('tags', field_label('tags', $fields, TRUE), 'trim|xss_clean' . required_field('tags', $fields, 'validation'));

			if ($contactID == NULL) {
				$this->form_validation->set_rules('marketing_consent', 'Marketing Consent', 'trim|xss_clean');
				$this->form_validation->set_rules('privacy_agreed', 'Privacy Agreed', 'trim|required|xss_clean');
			}

			if ($this->form_validation->run() == FALSE || $disabilityFailed) {
				$errors = $this->form_validation->error_array();
				if ($disabilityFailed) {
					$errors[] = 'Disability is required';
				}
			} else {

				if ($contactID == NULL && set_value('privacy_agreed') != 1 && time() >= strtotime('2018-05-25')) {
					$errors[] = 'Privacy policy must be agreed to';
				}

				// all ok, prepare data
				$data = array(
					'title' => NULL,
					'first_name' => set_value('first_name'),
					'last_name' => set_value('last_name'),
					'relationship' => set_value('eRelationship'),
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'address3' => set_value('address3'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'phone' => set_value('phone'),
					'mobile' => set_value('mobile'),
					'workPhone' => set_value('workPhone'),
					'email' => set_value('email'),
					'gender' => NULL,
					'gender_specify' => NULL,
					'gender_since_birth' => NULL,
					'sexual_orientation' => NULL,
					'sexual_orientation_specify' => NULL,
					'dob' => NULL,
					'medical' => set_value('medical'),
					'behavioural_info' => set_value('behavioural_information'),
					'disability_info' => set_value('disability_info'),
					'ethnic_origin' => set_value('ethnic_origin'),
					'religion' => NULL,
					'religion_specify' => NULL,
					'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
					'blacklisted' => 0,
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->auth->user->accountID
				);

				//Check Already Other Contact Available in Family
				if ($contactID == NULL) {
					$where_check = array("accountID" => $this->auth->user->accountID,
					"familyID" => $familyID);
					$res = $this->db->from("family_contacts")->where($where_check)->get();
					if($res->num_rows() == 0){
						$data["main"] = '1';
					}
				}

				if (set_value('title') != '') {
					$data['title'] = set_value('title');
				}

				if (in_array(set_value('gender'), array('male', 'female', 'please_specify', 'other'))) {
					$data['gender'] = set_value('gender');
					if (set_value('gender')=="please_specify") {
						$data['gender_specify'] = set_value('gender_specify');
					}
				}

				if (in_array(set_value('gender_since_birth'), array("yes", "no", "prefer_not_to_say"))) {
					$data['gender_since_birth'] = set_value('gender_since_birth');
				}

				if (in_array(set_value('sexual_orientation'), array_keys($this->settings_library->sexual_orientations))) {
					$data['sexual_orientation'] = set_value('sexual_orientation');
					if (set_value('sexual_orientation')=="please_specify") {
						$data['sexual_orientation_specify'] = set_value('sexual_orientation_specify');
					}
				}

				if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
					$data['religion'] = set_value('religion');
					if (set_value('religion')=="please_specify") {
						$data['religion_specify'] = set_value('religion_specify');
					}
				}

				if (set_value('dob') != '') {
					$data['dob'] = uk_to_mysql_date(set_value('dob'));
				}

				if ($contactID == NULL) {
					$data['byID'] = $this->auth->user->staffID;
					$data['familyID'] = $familyID;
					$data['added'] = mdate('%Y-%m-%d %H:%i:%s');
					$data['marketing_consent'] = intval(set_value('marketing_consent'));
					$data['marketing_consent_date'] = mdate('%Y-%m-%d %H:%i:%s');
					$data['privacy_agreed'] = intval(set_value('privacy_agreed'));
					$data['privacy_agreed_date'] = mdate('%Y-%m-%d %H:%i:%s');
				}

				// check if password entered
				if (set_value('password') != '') {
					// generate hash
					$password_hash = password_hash(set_value('password'), PASSWORD_BCRYPT);

					// check hash
					if (password_verify(set_value('password'), $password_hash)) {

						// save
						$data['password'] = $password_hash;

					}
				}

				if (set_value('blacklisted') == '1') {
					$data['blacklisted'] = 1;
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

					if ($contactID == NULL) {
						// insert
						$query = $this->db->insert('family_contacts', $data);
						$contactID = $this->db->insert_id();
						$just_added = TRUE;
						$successful = (bool)($this->db->affected_rows() == 1);

					} else {
						$where = array(
							'contactID' => $contactID
						);

						// update
						$query = $this->db->update('family_contacts', $data, $where);
						$successful = (bool)($this->db->affected_rows() == 1);

						//Delete disability data so it can be updated
						$where = array(
							'accountID' => $this->auth->user->accountID,
							'contactID' => $contactID
						);
						$query = $this->db->delete('family_disabilities', $where, 1);
					}

					//Add disability data
					if (is_array($this->input->post('disability'))) {
						$disability_data = array(
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $this->auth->user->accountID,
							'contactID' => $contactID
						);
						foreach ($this->input->post('disability') as $disability => $v) {
							$disability_data[$disability] = ($v=="1" ? 1 : NULL);
						}

						$this->db->insert('family_disabilities', $disability_data);
					}

					// if inserted/updated
					if ($successful) {

						// geocode address
						if ($res_geocode = geocode_address($data['address1'], $data['town'], $data['postcode'])) {
							$where = array(
								'contactID' => $contactID,
								'accountID' => $this->auth->user->accountID
							);
							$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
						}

						// add/update tags
						$tags = $this->input->post('tags');
						if (!is_array($tags)) {
							$tags = array();
						}
						// remove existing
						$where = array(
							'contactID' => $contactID,
							'accountID' => $this->auth->user->accountID
						);
						$this->db->delete('family_contacts_tags', $where);
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
									'contactID' => $contactID,
									'added' => mdate('%Y-%m-%d %H:%i:%s'),
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'accountID' => $this->auth->user->accountID
								);
								$this->db->insert('family_contacts_tags', $data);
							}
						}

						// if just added
						if (isset($just_added)) {
							// insert note
							$details = 'Contact: ' . set_value('first_name') . ' ' . set_value('last_name') . '
							By: ' . $this->auth->user->first . ' ' . $this->auth->user->surname . ' (Staff)
							IP: ' . get_ip_address() . '
							Hostname: ' . gethostbyaddr(get_ip_address());
							$summary = 'Marketing Consent: ';
							if (set_value('marketing_consent') == 1) {
								$summary .= 'Yes';
							} else {
								$summary .= 'No';
							}
							$summary .= ', Privacy Agreed: ';
							if (set_value('privacy_agreed') == 1) {
								$summary .= 'Yes';
							} else {
								$summary .= 'No';
							}
							$data = array(
								'type' => 'privacy',
								'summary' => $summary,
								'content' => $details,
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'familyID' => $familyID,
								'accountID' => $this->auth->user->accountID,
								'byID' => $this->auth->user->byID
							);
							$query = $this->db->insert('family_notes', $data);

							// update newsletter
							if ($brands->num_rows() > 0) {
								$newsletters = $this->input->post('newsletters');
								if (!is_array($newsletters)) {
									$newsletters = array();
								}
								foreach ($brands->result() as $brand) {
									// set where
									$where = array(
										'brandID' => $brand->brandID,
										'contactID' => $contactID,
										'accountID' => $this->auth->user->accountID
									);

									// process
									if (in_array($brand->brandID, $newsletters)) {
										// check if exists
										$res = $this->db->from('family_contacts_newsletters')->where($where)->limit(1)->get();

										// if not, insert
										if ($res->num_rows() == 0) {
											$data = $where;
											$this->db->insert('family_contacts_newsletters', $data);
										}
									} else {
										// remove
										$this->db->delete('family_contacts_newsletters', $where, 1);
									}
								}
							}
						}

						// tell user
						$success = set_value('first_name') . ' ' . set_value('last_name') . ' has been ';
						if (isset($just_added)) {
							$success .= 'created';
						} else {
							$success .= 'updated';
						}

						if ($this->settings_library->get('send_new_participant') == 1 && set_value('notify') == 1 && $this->crm_library->send_participant_welcome_email($contactID, $this->input->post('password'))) {
							$success .= ' and contact notified';
						}
						$success .= ' successfully.';

						$this->session->set_flashdata('success', $success);

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
			'contact_info' => $contact_info,
			'contactID' => $contactID,
			'familyID' => $familyID,
			'brands' => $brands,
			'breadcrumb_levels' => $breadcrumb_levels,
			'success' => $success,
			'errors' => $errors,
			'info' => $info,
			'tag_list' => $tag_list,
			'fields' => $fields
		);

		// load view
		$this->crm_view('participants/contact', $data);
	}

	/**
	 * delete an contact
	 * @param  int $familyID
	 * @return mixed
	 */
	public function remove($contactID = NULL) {

		// check params
		if (empty($contactID)) {
			show_404();
		}

		$where = array(
			'contactID' => $contactID,
			'accountID' => $this->auth->user->accountID
		);

		// run query
		$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();

		// no match
		if ($query->num_rows() == 0) {
			show_404();
		}

		// match
		foreach ($query->result() as $row) {
			$contact_info = $row;

			// if main contact, check for children
			if ($contact_info->main == 1) {
				$where = array(
					'familyID' => $contact_info->familyID,
					'accountID' => $this->auth->user->accountID
				);
				$children = $this->db->from('family_children')->where($where)->get();
				if ($children->num_rows() > 0) {
					show_404();
				}
			}

			// determine which page to send the user back to
			$redirect_to = 'participants/view/' . $contact_info->familyID;

			// all ok, delete
			$query = $this->db->delete('family_contacts', $where);

			if ($this->db->affected_rows() == 1) {
				$this->session->set_flashdata('success', $contact_info->first_name . ' ' . $contact_info->last_name . ' has been removed successfully.');

				// check if any contacts or children left
				$where = array(
					'familyID' => $contact_info->familyID,
					'accountID' => $this->auth->user->accountID
				);
				$children = $this->db->from('family_children')->where($where)->get();
				$contacts = $this->db->from('family_contacts')->where($where)->get();
				if ($children->num_rows() == 0 && $contacts->num_rows() == 0) {
					// no, delete family
					$query = $this->db->delete('family', $where);
					$redirect_to = 'participants';
				}
			} else {
				$this->session->set_flashdata('error', $contact_info->first_name . ' ' . $contact_info->last_name . ' could not be removed.');
			}

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

	/**
	 * validation function for checking email is unique, except in specified user record
	 * @param  string $email
	 * @param  int $user_id
	 * @return bool
	 */
	public function check_email($email = NULL, $contactID = NULL) {
		// if not email specified, skip
		if (empty($email)) {
			return TRUE;
		}

		// check email not in use with anyone on this account
		$where = array(
			'email' => $email,
			'accountID' => $this->auth->user->accountID
		);

		// exclude current user, if set
		if (!empty($contactID)) {
			$where['contactID !='] = $contactID;
		}

		// check
		$query = $this->db->get_where('family_contacts', $where, 1);

		// check results
		if ($query->num_rows() == 0) {
			// none matching, so ok
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * check mobile number is valid
	 * @param  string $number
	 * @return mixed
	 */
	public function check_mobile($number = NULL) {
		return $this->crm_library->check_mobile($number);
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

	/**
	 * validation function for notify as need password
	 * @param  string $val
	 * @return bool
	 */
	public function notify_need_password($val) {

		// valid if empty
		if (empty($val)) {
			return TRUE;
		}

		// check has email and password
		if (empty($this->input->post('email')) || empty($this->input->post('password'))) {
			return FALSE;
		}

		return TRUE;
	}

}

/* End of file contacts.php */
/* Location: ./application/controllers/participants/contacts.php */
