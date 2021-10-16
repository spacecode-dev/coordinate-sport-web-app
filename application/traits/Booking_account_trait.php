<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

trait Booking_account_trait {
	// view booking in account
	public function booking($cartID, $ajax = NULL) {
		// if in crm
		if ($this->in_crm === TRUE) {
			$accountID = $this->auth->user->accountID;
		} else {
			// check auth
			$this->online_booking->require_auth();
			$accountID = $this->cart_library->accountID;
		}

		// set defaults
		$title = 'Booking';
		$body_class = 'account booking';
		$tab = 'bookings';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$buttons = NULL;
		$icon = 'calendar-alt';
		$breadcrumb_levels = array(
			'participants' => 'Participants',
			'participants/view/' . $this->cart_library->familyID => 'Participant Account',
			'participants/bookings/' . $this->cart_library->familyID => 'Bookings'
		);

		// get booking
		$where = array(
			'bookings_cart.accountID' => $accountID,
			'bookings_cart.cartID' => $cartID,
			'bookings_cart.type' => 'booking'
		);
		$res = $this->db->select('bookings_cart.*, family_contacts.first_name, family_contacts.last_name')
			->from('bookings_cart')
			->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'left')
			->where($where)
			->get();

		if ($res->num_rows() == 0) {
			show_404();
		}

		foreach ($res->result() as $row) {
			$booking = $row;
		}

		if ($this->in_crm) {
			// init contact cart
			if (!$this->crm_library->init_contact_cart($booking->contactID)) {
				show_404();
			}
			$buttons = '<a class="btn" href="' . site_url('participants/bookings/' . $this->cart_library->familyID) . '"><i class="far fa-angle-left"></i> Return to List</a> <a class="btn btn-warning" href="' . site_url('booking/cart/edit/' . $cartID) . '"><i class="far fa-pencil"></i> Edit</a>';
			$breadcrumb_levels = array(
				'participants' => 'Participants',
				'participants/view/' . $this->cart_library->familyID => 'Participant Account',
				'participants/bookings/' . $this->cart_library->familyID => 'Bookings'
			);
		}

		// get booking items
		$where = array(
			'bookings_cart_sessions.accountID' => $this->cart_library->accountID,
			'bookings_cart.familyID' => $this->cart_library->familyID,
			'bookings_cart_sessions.cartID' => $cartID,
		);
		$res = $this->db->select('bookings_cart_sessions.*, bookings.register_type, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_children.first_name as child_first, family_children.last_name as child_last')
			->from('bookings_cart_sessions')
			->join('bookings_cart', 'bookings_cart_sessions.cartID = bookings_cart.cartID', 'inner')
			->join('bookings_blocks', 'bookings_cart_sessions.blockID = bookings_blocks.blockID', 'inner')
			->join('bookings_lessons', 'bookings_cart_sessions.lessonID = bookings_lessons.lessonID', 'inner')
			->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
			->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'left')
			->where($where)
			->order_by('bookings_blocks.startDate asc, bookings_cart_sessions.date asc')
			->get();

		// get booking items
		$where = array(
			'bookings_cart_subscriptions.accountID' => $this->cart_library->accountID,
			'bookings_cart.familyID' => $this->cart_library->familyID,
			'bookings_cart_subscriptions.cartID' => $cartID,
		);
		$res_sub = $this->db->select('subscriptions.*, bookings_cart_subscriptions.*, bookings.register_type, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_children.first_name as child_first, family_children.last_name as child_last')
			->from('bookings_cart_subscriptions')
			->join('bookings_cart', 'bookings_cart_subscriptions.cartID = bookings_cart.cartID', 'inner')
			->join('bookings', 'bookings_cart_subscriptions.bookingID = bookings.bookingID', 'inner')
			->join('subscriptions', 'bookings_cart_subscriptions.subID = subscriptions.subID', 'inner')
			->join('participant_subscriptions', 'bookings_cart_subscriptions.subID = participant_subscriptions.subID', 'inner')
			->join('family_contacts', 'bookings_cart_subscriptions.contactID = family_contacts.contactID', 'left')
			->join('family_children', 'bookings_cart_subscriptions.childID = family_children.childID', 'left')
			->where($where)
			->group_by('bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.childID, bookings_cart_subscriptions.subID')
			->get();

		if ($res_sub->num_rows() == 0 && $res->num_rows() == 0) {
			show_404();
		}

		$blockIDs = array();
		$blocks = array();
		$booked_sessions = array();
		$session_prices = array();
		$session_totals = array();
		$block_totals = array();
		$block_priced = array();
		$cartArray = array();
		$childArray = array();
		$contactArray = array();

		$booked_sessions_temp = array();

		if($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$blockIDs[$row->blockID] = $row->blockID;
				// determine participant
				$row->participant = $row->child_first . ' ' . $row->child_last;
				if (strpos($row->register_type, 'individuals') === 0) {
					$row->participant = $row->contact_first . ' ' . $row->contact_last;
				}

				// add participant to lesson
				if (!isset($booked_sessions[$row->blockID][$row->date][$row->lessonID])) {
					$booked_sessions[$row->blockID][$row->date][$row->lessonID] = array();
				}
				if($row->register_type === "adults_children"){
					if($row->childID != ""){
						$row->participant = $row->child_first . ' ' . $row->child_last;
					}else{
						$row->participant = $row->contact_first . ' ' . $row->contact_last;
					}
				}
				$booked_sessions[$row->blockID][$row->date][$row->lessonID][] = $row->participant;
				$cartArray[$row->blockID] = $row->cartID;
				$contactArray[$row->blockID] = $row->contactID;
				$childArray[$row->blockID] = $row->childID;

				// session prices
				if (!isset($session_prices[$row->blockID][$row->date][$row->lessonID])) {
					$session_prices[$row->blockID][$row->date][$row->lessonID] = 0;
				}
				$session_prices[$row->blockID][$row->date][$row->lessonID] += $row->price;

				if(!empty($row->contactID)) {
					$where = array(
						'cartID' => $row->cartID,
						'accountID' => $row->accountID,
						'contactID' => $row->contactID
					);
					$subs = $this->db->select('*')
						->from('bookings_cart_subscriptions')
						->where($where)->get();
					if ($subs->num_rows() > 0) {
						$session_prices[$row->blockID][$row->date][$row->lessonID] -= $row->price;
					}
				}else{
					$where = array(
						'cartID' => $row->cartID,
						'accountID' => $row->accountID,
						'childID' => $row->childID
					);
					$subs = $this->db->select('*')
						->from('bookings_cart_subscriptions')
						->where($where)->get();
					if ($subs->num_rows() > 0) {
						$session_prices[$row->blockID][$row->date][$row->lessonID] -= $row->price;
					}
				}

				// session totals
				if (!isset($session_totals[$row->blockID][$row->date][$row->lessonID])) {
					$session_totals[$row->blockID][$row->date][$row->lessonID] = 0;
				}
				$session_totals[$row->blockID][$row->date][$row->lessonID] += $row->total;

				// block totals
				if (!isset($block_totals[$row->blockID])) {
					$block_totals[$row->blockID] = 0;
				}
				$block_totals[$row->blockID] += $row->total;

				// check if block priced
				if ($row->block_priced == 1) {
					$block_priced[$row->blockID] = true;
				}
			}
		}

		/*if($res_sub->num_rows() > 0) {
			foreach ($res_sub->result() as $row) {
				$blockIDs[$row->blockID] = $row->blockID;
			}
		}*/

		// get all blocks
		$custom_where = " AND `" . $this->db->dbprefix("bookings_blocks") . "`.`blockID` IN (" . $this->db->escape_str(implode(',', $blockIDs)) . ")";
		$blocks = $this->cart_library->get_blocks(array(), array('show_all' => TRUE), $custom_where);

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'info' => $info,
			'tab' => $tab,
			'booking' => $booking,
			'subscriptions' => $res_sub,
			'blocks' => $blocks,
			'booked_sessions' => $booked_sessions,
			'cartArray' => $cartArray,
			'childArray' => $childArray,
			'contactArray' => $contactArray,
			'session_prices' => $session_prices,
			'session_totals' => $session_totals,
			'block_totals' => $block_totals,
			'block_priced' => $block_priced,
			'in_crm' => $this->in_crm,
			'fa_weight' => $this->fa_weight,
			'buttons' => $buttons,
			'breadcrumb_levels' => $breadcrumb_levels,
			'icon' => $icon,
			'ajax' => $ajax
		);

		$view = 'online-booking/account/booking';
		if(!empty($ajax)){
			$this->load->view($view, $data);
		} else if ($this->in_crm) {
			$this->crm_view($view, $data);
		} else {
			$this->booking_view('online-booking/account/booking', $data, 'templates/online-booking-account');
		}
	}

	public function participant($childID = 'new', $bookingID = NULL) {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				show_404();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// set defaults
		$title = 'Add Participant';
		$body_class = 'participant lightbox p-10 overflow-iframe';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$child_info = NULL;

		$where = array(
			'familyID' => $this->cart_library->familyID,
			'accountID' => $this->cart_library->accountID,
			'main' => 1
		);
		// Get Account Holder Info
		$query = $this->db->from('family_contacts')->where($where)->limit(1)->get();
		foreach($query->result() as $row){
			$account_holder_info = $row;
		}
		$fields = get_fields("participant");

		// look up child
		if ($childID != 'new') {
			$where = array(
				'childID' => $childID,
				'familyID' => $this->cart_library->familyID,
				'accountID' => $this->cart_library->accountID
			);
			$res = $this->db->from('family_children')->where($where)->limit(1)->get();
			if ($res->num_rows() == 1) {
				foreach($res->result() as $row) {
					$child_info = $row;
					$title = $child_info->first_name . ' ' . $child_info->last_name;
				}

				$child_info->disability = array();
				$where = array(
					'accountID' => $this->cart_library->accountID,
					'childID' => $childID
				);
				$res = $this->db->select('*')->from('family_disabilities')->where($where)->limit(1)->get();
				if ($res->num_rows() > 0) {
					$child_info->disability = $res->result()[0];
				}
			} else {
				// not found
				$childID = 'new';
			}
		}

		// check if emergency contact 1 required - only for new children and if existing ones already have it
		$emergency_contact_1_required = FALSE;
		if ($childID == 'new' || (isset($child_info->emergency_contact_1_name) && !empty($child_info->emergency_contact_1_name))) {
			$emergency_contact_1_required = TRUE;
		}

		// get schools
		$where = array(
			'accountID' => $this->cart_library->accountID,
			'type' => 'school'
		);
		$schools = $this->db->from('orgs')->where($where)->order_by('name asc')->get();

		// load libraries
		$this->load->library('form_validation');

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean' . required_field('gender', $fields, 'validation'));
			$this->form_validation->set_rules('gender_specify', 'Specific Gender', 'trim|xss_clean' . ($this->input->post('gender')=="please_specify" ? required_field('gender_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|xss_clean|callback_check_dob' . required_field('dob', $fields, 'validation'));
			if ($this->input->post('add_school') == 1) {
				$this->form_validation->set_rules('new_school', 'School', 'trim|xss_clean' . required_field('orgID', $fields, 'validation'));
			} else {
				$this->form_validation->set_rules('orgID', 'School', 'trim|xss_clean' . required_field('orgID', $fields, 'validation'));
			}

			$this->form_validation->set_rules('medical', 'Medical Information', 'trim|xss_clean' . required_field('medical', $fields, 'validation'));
			$disabilityFailed = false;
			if (empty($this->input->post('disability')) && required_field('disability', $fields)) {
				$disabilityFailed = true;
			}
			$this->form_validation->set_rules('disability_info', 'Disability Information', 'trim|xss_clean' . required_field('disability_info', $fields, 'validation'));
			$this->form_validation->set_rules('behavioural_information', 'Behavioural Information', 'trim|xss_clean' . required_field('behavioural_information', $fields, 'validation'));
			$this->form_validation->set_rules('religion', 'Religion', 'trim|xss_clean' . required_field('religion', $fields, 'validation'));
			$this->form_validation->set_rules('religion_specify', 'Specific Religion', 'trim|xss_clean' . ($this->input->post('religion')=="please_specify" ? required_field('religion_specify', $fields, 'validation') : ""));
			$this->form_validation->set_rules('ethnic_origin', 'Ethnic Origin', 'trim|xss_clean' . required_field('ethnic_origin', $fields, 'validation'));
			$this->form_validation->set_rules('photoConsent', 'Photo Consent', 'trim|xss_clean' . required_field('photoConsent', $fields, 'validation'));

			if ($emergency_contact_1_required === TRUE) {
				$this->form_validation->set_rules('emergency_contact_1_name', 'Emergency Contact 1 Name', 'trim|xss_clean'.required_field('emergency_contact_1_name', $fields, 'validation'));
				$this->form_validation->set_rules('emergency_contact_1_phone', 'Emergency Contact 1 Phone', 'trim|xss_clean'.required_field('emergency_contact_1_phone', $fields, 'validation'));
			} else {
				$this->form_validation->set_rules('emergency_contact_1_name', 'Emergency Contact 1 Name', 'trim|xss_clean');
				$this->form_validation->set_rules('emergency_contact_1_phone', 'Emergency Contact 1 Phone', 'trim|xss_clean');
			}
			$this->form_validation->set_rules('emergency_contact_2_name', 'Emergency Contact 2 Name', 'trim|xss_clean'.required_field('emergency_contact_2_name', $fields, 'validation'));
			$this->form_validation->set_rules('emergency_contact_2_phone', 'Emergency Contact 2 Phone', 'trim|xss_clean'.required_field('emergency_contact_2_phone', $fields, 'validation'));

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
				if ($disabilityFailed) {
					$errors[] = 'Disability is required';
				}
			} else {

				// all ok, set data
				$child_data = array(
					'accountID' => $this->cart_library->accountID,
					'familyID' => $this->cart_library->familyID,
					'first_name' => set_value('first_name'),
					'last_name' => set_value('last_name'),
					'orgID' => set_value('orgID'),
					'dob' => NULL,
					'gender' => NULL,
					'gender_specify' => NULL,
					'photoConsent' => 0,
					'medical' => set_value('medical'),
					'pin' => set_value('pin'),
					'disability_info' => set_value('disability_info'),
					'behavioural_info' => set_value('behavioural_information'),
					'ethnic_origin' => set_value('ethnic_origin'),
					'religion' => NULL,
					'religion_specify' => NULL,
					'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				if (in_array(set_value('gender'), array('male', 'female', 'please_specify', 'other'))) {
					$child_data['gender'] = set_value('gender');
					if (set_value('gender')=="please_specify") {
						$child_data['gender_specify'] = set_value('gender_specify');
					}
				}

				if (in_array(set_value('religion'), array_keys($this->settings_library->religions))) {
					$child_data['religion'] = set_value('religion');
					if (set_value('religion')=="please_specify") {
						$child_data['religion_specify'] = set_value('religion_specify');
					}
				}
				if (set_value('dob') != '') {
					$child_data['dob'] = uk_to_mysql_date(set_value('dob'));
				}
				if (set_value('photoConsent') == 1) {
					$child_data['photoConsent'] = 1;
				}
				// if adding new school
				if ($this->input->post('add_school') == 1) {
					$school_data = array(
						'accountID' => $this->cart_library->accountID,
						'name' => set_value('new_school'),
						'prospect' => 1,
						'partnership' => 0,
						'type' => 'school',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$res = $this->db->insert('orgs', $school_data);
					if ($this->db->affected_rows() == 1) {
						$orgID = $this->db->insert_id();

						// add org address
						$address_data = array(
							'accountID' => $this->cart_library->accountID,
							'orgID' => $orgID,
							'type' => 'main',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s')
						);
						$res = $this->db->insert('orgs_addresses', $address_data);

						// save
						$child_data['orgID'] = $orgID;
					} else {
						$errors[] = 'Could not add new school';
					}
				}

				// update profile picture
				$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->cart_library->accountID, 500, 500, 50, 50, TRUE);

				if ($upload_res !== NULL) {
					$image_data = array(
						'name' => $upload_res['client_name'],
						'path' => $upload_res['raw_name'],
						'type' => $upload_res['file_type'],
						'size' => $upload_res['file_size']*1024,
						'ext' => substr($upload_res['file_ext'], 1)
					);
					$child_data['profile_pic'] = serialize($image_data);
				}

				if (count($errors) == 0) {
					if ($childID == 'new') {
						$child_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
						$res = $this->db->insert('family_children', $child_data);
						$successful = (bool)($this->db->affected_rows() == 1);
					} else {
						$where = array(
							'childID' => $childID,
							'familyID' => $this->cart_library->familyID,
							'accountID' => $this->cart_library->accountID
						);
						$res = $this->db->update('family_children', $child_data, $where, 1);

						$successful = (bool)($this->db->affected_rows() == 1);

						//Delete disability data so it can be updated
						$where = array(
							'accountID' => $this->cart_library->accountID,
							'childID' => $childID
						);
						$query = $this->db->delete('family_disabilities', $where, 1);
					}

					if ($successful) {
						$verb = 'updated';
						if ($childID == 'new') {
							$childID = $this->db->insert_id();
							$verb = 'added';
						}

						//Add disability data
						if (is_array($this->input->post('disability'))) {
							$disability_data = array(
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'accountID' => $this->cart_library->accountID,
								'childID' => $childID
							);
							foreach ($this->input->post('disability') as $disability => $v) {
								$disability_data[$disability] = ($v=="1" ? 1 : NULL);
							}

							$this->db->insert('family_disabilities', $disability_data);
						}

						// tell user
						$success = 'Participant has been ' . $verb . ' successfully';
						$this->session->set_flashdata('child_success',  $success);

						// pass certain info back for when making booking
						$child_info = new stdClass;
						$child_info->participantID = $childID;
						$child_info->name = $child_data['first_name'] . ' ' . $child_data['last_name'];
						$child_info->dob = $child_data['dob'];

						if($bookingID != NULL) {
							$where = array(
								'subscriptions.bookingID' => $bookingID,
								'subscriptions.accountID' => $this->cart_library->accountID,
								'subscriptions.individual_subscription' => false
							);

							$subs_res = $this->db->select('subscriptions.subID, subName, frequency, price')
											->from('subscriptions')
											->where($where)
											->group_by('subscriptions.subID')
											->get();

							$subscriptions = array();
							foreach($subs_res->result() as $sub) {

								$where = array(
									'subscriptions_lessons_types.accountID' => $this->cart_library->accountID,
									'subscriptions_lessons_types.subID' => $sub->subID
								);

								$lesson_types = $this->db->select('GROUP_CONCAT( ' . $this->db->dbprefix('lesson_types') . '.name  SEPARATOR\', \') as types')
														->from('subscriptions_lessons_types')
														->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID')
														->where($where)
														->get();

								$types = '';
								if($lesson_types->num_rows() > 0) {
									foreach($lesson_types->result() as $types) break;
									$types = ' (' . $types->types . ')';
								}

								$sub->types = $types;
								$subscriptions[] = $sub;
							}
							$child_info->subscriptions = $subscriptions;
						}
					} else {
						$error = 'Error saving data';
					}
				}
			}
		}

		// check for flashdata
		/*if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else*/ if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'lightbox' => TRUE,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'schools' => $schools,
			'child_info' => $child_info,
			'fa_weight' => $this->fa_weight,
			'fields' => $fields,
			'account_holder_info' => $account_holder_info,
			'in_crm' => $this->in_crm,
			'emergency_contact_1_required' => $emergency_contact_1_required,
			'bookingID' => $bookingID
		);
		$view = 'online-booking/account/participant';
		if ($this->in_crm) {
			$this->crm_view($view, $data);
		} else {
			$this->booking_view($view, $data);
		}
	}

	public function individual($contactID = 'new') {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				show_404();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// set defaults
		$title = 'Add Participant';
		$body_class = 'participant lightbox';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$contact_info = NULL;

		// look up child
		if ($contactID != 'new') {
			$where = array(
				'contactID' => $contactID,
				'familyID' => $this->cart_library->familyID,
				'accountID' => $this->cart_library->accountID
			);
			$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();
			if ($res->num_rows() == 1) {
				foreach($res->result() as $row) {
					$contact_info = $row;
					$title = $contact_info->first_name . ' ' . $contact_info->last_name;
					// don't allow editing contacts on online booking
					show_404();
				}
			} else {
				// not found
				$contactID = 'new';
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// if posted
		if ($this->input->post()) {
			// set validation rules
			$this->form_validation->set_rules('title', 'Title', 'trim|xss_clean');
			$this->form_validation->set_rules('first_name', 'First Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('last_name', 'Last Name', 'trim|xss_clean|required');
			$this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean|callback_check_dob');

			if ($this->settings_library->get('require_participant_email', $this->cart_library->accountID) == 1) {
				$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|required|valid_email|callback_check_email[' . $contactID . ']');
			} else {
				$this->form_validation->set_rules('email', 'Email', 'trim|xss_clean|valid_email|callback_check_email[' . $contactID . ']');
			}
			if ($this->settings_library->get('require_mobile', $this->cart_library->accountID) == 1) {
				$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|required|callback_check_mobile');
			} else {
				$this->form_validation->set_rules('mobile', 'Mobile', 'trim|xss_clean|callback_check_mobile');
			}
			$this->form_validation->set_rules('phone', 'Other Phone', 'trim|xss_clean|callback_phone_or_mobile[' . $this->input->post('mobile') . ']');
			$this->form_validation->set_rules('workPhone', 'Work Phone', 'trim|xss_clean');

			$this->form_validation->set_rules('address1', 'Address 1', 'trim|xss_clean|required');
			$this->form_validation->set_rules('address2', 'Address 2', 'trim|xss_clean');
			$this->form_validation->set_rules('address3', 'Address 3', 'trim|xss_clean');
			$this->form_validation->set_rules('town', 'Town', 'trim|xss_clean|required');
			$this->form_validation->set_rules('county', localise('county', $this->cart_library->accountID), 'trim|xss_clean|required');
			$this->form_validation->set_rules('postcode', 'Post Code', 'trim|xss_clean|required|callback_check_postcode');

			$this->form_validation->set_rules('gender', 'Gender', 'trim|xss_clean');
			$this->form_validation->set_rules('medical', 'Medical Information', 'trim|xss_clean');
			$this->form_validation->set_rules('disability_info', 'Disability Information', 'trim|xss_clean');
			$this->form_validation->set_rules('ethnic_origin', 'Ethnic Origin', 'trim|xss_clean');

			$this->form_validation->set_rules('emergency_contact_1_name', 'Emergency Contact 1 Name', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_1_phone', 'Emergency Contact 1 Phone', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_2_name', 'Emergency Contact 2 Name', 'trim|xss_clean');
			$this->form_validation->set_rules('emergency_contact_2_phone', 'Emergency Contact 2 Phone', 'trim|xss_clean');

			if ($this->form_validation->run() == FALSE) {
				$errors = $this->form_validation->error_array();
			} else {

				// all ok, set data
				$contact_data = array(
					'accountID' => $this->cart_library->accountID,
					'familyID' => $this->cart_library->familyID,
					'title' => NULL,
					'first_name' => set_value('first_name'),
					'last_name' => set_value('last_name'),
					'phone' => set_value('phone'),
					'mobile' => set_value('mobile'),
					'workPhone' => set_value('workPhone'),
					'dob' => NULL,
					'address1' => set_value('address1'),
					'address2' => set_value('address2'),
					'address3' => set_value('address3'),
					'town' => set_value('town'),
					'county' => set_value('county'),
					'postcode' => set_value('postcode'),
					'gender' => NULL,
					'medical' => set_value('medical'),
					'disability_info' => set_value('disability_info'),
					'ethnic_origin' => set_value('ethnic_origin'),
					'emergency_contact_1_name' => null_if_empty(set_value('emergency_contact_1_name')),
					'emergency_contact_1_phone' => null_if_empty(set_value('emergency_contact_1_phone')),
					'emergency_contact_2_name' => null_if_empty(set_value('emergency_contact_2_name')),
					'emergency_contact_2_phone' => null_if_empty(set_value('emergency_contact_2_phone')),
					'email' => set_value('email'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				if (set_value('title') != '') {
					$contact_data['title'] = set_value('title');
				}
				if (in_array(set_value('gender'), array('male', 'female', 'other'))) {
					$contact_data['gender'] = set_value('gender');
				}
				if (set_value('dob') != '') {
					$contact_data['dob'] = uk_to_mysql_date(set_value('dob'));
				}

				// update profile picture
				$upload_res = $this->crm_library->handle_image_upload('profile_pic', FALSE, $this->cart_library->accountID, 500, 500, 50, 50, TRUE);

				if ($upload_res !== NULL) {
					$image_data = array(
						'name' => $upload_res['client_name'],
						'path' => $upload_res['raw_name'],
						'type' => $upload_res['file_type'],
						'size' => $upload_res['file_size']*1024,
						'ext' => substr($upload_res['file_ext'], 1)
					);
					$contact_data['profile_pic'] = serialize($image_data);
				}

				if ($contactID == 'new') {
					$contact_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
					$res = $this->db->insert('family_contacts', $contact_data);
				} else {
					$where = array(
						'contactID' => $contactID,
						'familyID' => $this->cart_library->familyID,
						'accountID' => $this->cart_library->accountID
					);
					$res = $this->db->update('family_contacts', $contact_data, $where, 1);
				}

				if ($this->db->affected_rows() == 1) {
					$verb = 'updated';
					if ($contactID == 'new') {
						$contactID = $this->db->insert_id();
						$verb = 'added';
					}

					// geocode address
					if ($res_geocode = geocode_address($contact_data['address1'], $contact_data['town'], $contact_data['postcode'], $this->cart_library->accountID)) {
						$where = array(
							'contactID' => $contactID,
							'accountID' => $this->cart_library->accountID
						);
						$res_update = $this->db->set('location', 'ST_GeomFromText("POINT(' . $res_geocode['lat'] . ' ' . $res_geocode['lng'] . ')")', FALSE)->where($where)->limit(1)->update('family_contacts');
					}

					// tell user
					$success = 'Participant has been ' . $verb . ' successfully';
					//$this->session->set_flashdata('success',  $success);

					// pass certain info back for when making booking
					$contact_info = new stdClass;
					$contact_info->participantID = $contactID;
					$contact_info->name = $contact_data['first_name'] . ' ' . $contact_data['last_name'];
					$contact_info->dob = $contact_data['dob'];
				} else {
					$error = 'Error saving data';
				}
			}
		}

		// check for flashdata
		/*if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else*/ if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'lightbox' => TRUE,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'contact_info' => $contact_info,
			'fa_weight' => $this->fa_weight,
			'in_crm' => $this->in_crm
		);
		$view = 'online-booking/account/individual';
		if ($this->in_crm) {
			$this->crm_view($view, $data);
		} else {
			$this->booking_view($view, $data);
		}
	}

	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		return $this->crm_library->check_postcode($postcode, $this->cart_library->accountID);

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
	 * @param  int $contactID
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
			'accountID' => $this->cart_library->accountID
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
		return $this->crm_library->check_mobile($number, $this->cart_library->accountID);
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
