<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

trait Book_trait {
	public function index($blockID, $hiddenflag = NULL) {

		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
			// add buttons
			$this->buttons = '<a class="btn btn-info" href="' . site_url('participants/view/' . $this->cart_library->familyID) . '"><i class="' . $this->fa_weight . ' fa-user"></i> Participant Account</a> <a class="btn btn-primary" href="' . site_url($this->cart_base . 'cart/close/' . $blockID) . '"><i class="' . $this->fa_weight . ' fa-times"></i> Close ' . ucwords($this->cart_library->cart_type) . '</a>';
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// set defaults
		$title = 'Book';
		$body_class = 'book';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$is_subscription_active = FALSE;
		$errors = array();
		$prevent_booking = FALSE;
		$participants = array();
		$selected_participants = array();
		$subs = array();
		$selected_subs = array();
		$subscriptions_only = FALSE;
		$selected_lessons = array();
		$already_in_cart = FALSE;
		$monitoring_fields = array();
		$monitoring_fields_configs = array();
		$monitoring_existing = array();
		$icon = 'shopping-cart';
		$online_booking_subscription_module = 0;
		$breadcrumb_levels = array(
			'booking/cart' => 'Booking Cart'
		);
		$blocks_where = [];
		if ($this->cart_library->cart_type == 'booking') {
			$icon = 'calendar-alt';
			$breadcrumb_levels = array(
				'booking/cart' => 'Edit Booking'
			);
			$blocks_where['show_all'] = TRUE;
		}

		// look up block
		$where = array(
			'bookings_blocks.blockID' => $blockID,
		);
		if (!$this->in_crm) {
			$where['bookings.disable_online_booking !='] = 1;
		}
		$blocks = $this->cart_library->get_blocks($where, $blocks_where);

		// if doesn't exist, 404
		if (count($blocks) == 0) {
			show_404();
		}

		// get first result
		foreach ($blocks as $block_info) {
			break;
		}

		// set title
		$title = $block_info->booking;
		$register_type = $block_info->register_type;
		$booking_requirement = $block_info->booking_requirement;

		// get participants link
		$new_participants_link = 'account/participants/new/' . $block_info->bookingID;
		$participant_id_field = 'childID';
		$participants_table = 'family_children';
		if ($this->in_crm) {
			$new_participants_link = 'booking/book/new/child';
		}
		if (strpos($block_info->register_type, 'individuals') === 0) {
			$new_participants_link = 'account/individual/new';
			$participant_id_field = 'contactID';
			$participants_table = 'family_contacts';
			if ($this->in_crm) {
				$new_participants_link = 'booking/book/new/individual';
			}
		}
		$new_adults_link ='';$adult_id_field='';$adults_table='';
		if (strpos($block_info->register_type, 'adults_children') === 0) {
			//add participants Link
			$new_participants_link = 'account/participants/new';
			$participant_id_field = 'childID';
			$participants_table = 'family_children';
			if ($this->in_crm) {
				$new_participants_link = 'booking/book/new/child';
			}

			//add adults Link
			$new_adults_link = 'account/individual/new';
			$adult_id_field = 'contactID';
			$adults_table = 'family_contacts';
			if ($this->in_crm) {
				$new_adults_link = 'booking/book/new/individual';
			}
		}

		// get booking
		$where = array(
			'bookings.bookingID' => $block_info->bookingID
		);
		$res = $this->db->from('bookings')
			->where($where)
			->limit(1)
			->get();
		foreach ($res->result() as $booking_info) {
			// get possible monitoring fields
			for ($i = 1; $i <= 20; $i++) {
				$field = 'monitoring' . $i;
				if (!empty(trim($booking_info->$field))) {
					$monitoring_fields[$i] = $booking_info->$field;
					$monitoring_fields_configs[$i] = array("entry_type" => $booking_info->{$field."_entry_type"}, "mandatory" => $booking_info->{$field."_mandatory"});
				}
			}
			// islington only - ID 35
			if ($this->cart_library->accountID == 35) {
				$monitoring_fields['medical'] = 'Tick if there are any medical conditions that you need to discuss with your instructor(s) prior to your cycling session.';
			}
		}

		// load libraries
		$this->load->library('form_validation');

		// check if user blacklisted
		if ($prevent_booking !== TRUE && $this->cart_library->contact_blacklisted == TRUE) {
			if (!$this->in_crm) {
				$error = 'Your account has been blocked from making online bookings, please contact us to discuss your options.';
				$prevent_booking = TRUE;
			} else {
				$error = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' has been blacklisted from making bookings. Please read the family notes for details before proceeding.';
			}
		}

		//get subscription module activation
		$query = $this->db->from("accounts")->where("accountID", $this->cart_library->accountID)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$online_booking_subscription_module = $result->addon_online_booking_subscription_module;
			}
		}

		// check if already booked sessions on this booking
		$already_booked_sessions = array();
		$already_booked_subscriptions = array();
		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'booking',
				'bookings_cart_sessions.bookingID' => $block_info->bookingID,
				'bookings_cart.cartID !=' => $this->cart_library->cartID // if editing booking
			);
			$res = $this->db->from('bookings_cart')->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')->where($where)->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					if (strpos($block_info->register_type, 'adults_children') === 0) {
						$already_booked_sessions[$row->lessonID][$row->date][] = ($row->childID == "")?$row->contactID:$row->childID;
					}else{
						$already_booked_sessions[$row->lessonID][$row->date][] = $row->$participant_id_field;
					}
				}
			}

			// get block bookingID
			$where = array(
				'bookings_cart.accountID' => $this->cart_library->accountID,
				'bookings_cart.familyID' => $this->cart_library->familyID,
				'bookings_cart.type' => 'cart',
				'participant_subscriptions.status' => 'active'
			);
			$res = $this->db->select('participant_subscriptions.subID, participant_subscriptions.childID, participant_subscriptions.contactID')
				->from('bookings_cart_subscriptions')
				->join('bookings_cart', 'bookings_cart.contactID = bookings_cart_subscriptions.contactID', 'LEFT')
				->join('participant_subscriptions','participant_subscriptions.accountID = bookings_cart_subscriptions.accountID AND
				(bookings_cart_subscriptions.childID = participant_subscriptions.childID OR bookings_cart_subscriptions.contactID = `'.$this->db->dbprefix("participant_subscriptions").'`.`contactID`)', "LEFT")
				->where($where)
				->group_by(array("participant_subscriptions.contactID", "participant_subscriptions.childID", "participant_subscriptions.subID"))
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		// check postcode restrictions
		if ($prevent_booking !== TRUE && !empty($block_info->booking_postcodes)) {
			$booking_postcodes = explode(',', $block_info->booking_postcodes);
			if (is_array($booking_postcodes) && count($booking_postcodes) > 0) {
				$allowed_postcodes = array();
				foreach ($booking_postcodes as $postcode) {
					// clean up
					$postcode = preg_replace("/[^A-Z0-9]/", '', strtoupper($postcode));
					if (!empty($postcode)) {
						$allowed_postcodes[] = $postcode;
					}
				}

				// check if any to match
				if (count($allowed_postcodes) > 0) {
					$postcode_allowed = FALSE;

					// clean up
					$contact_postcode = preg_replace("/[^A-Z0-9]/", '', strtoupper($this->cart_library->contact_postcode));

					// check for match
					foreach ($allowed_postcodes as $postcode) {
						// check for match
						if (substr($contact_postcode, 0, strlen($postcode)) == $postcode) {
							$postcode_allowed = TRUE;
						}
					}

					// final check
					if ($postcode_allowed != TRUE) {
						if (!$this->in_crm) {
							$error = 'You don\'t live within an eligible area for this event, please contact us if you believe this to be incorrect.';
							$prevent_booking = TRUE;
						} else {
							$error = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' doesn\'t live within an eligible area for this event, please check eligibility before proceeding.';
						}
					}
				}
			}
		}

		// check for bookable places
		$sold_out = TRUE;
		foreach ($blocks as $block) {
			if ($block->availability_status_class !== 'soldout') {
				$sold_out = FALSE;
			}
		}
		if ($sold_out && !$this->in_crm) {
			$error = 'Sold out';
			$prevent_booking = TRUE;
		}

		// check password
		if ($prevent_booking !== TRUE && !empty($block_info->online_booking_password) && !$this->in_crm) {
			$show_password_form = TRUE;

			// check is password already stored in session
			if ($this->session->userdata('online_booking_password') == $block_info->online_booking_password) {
				$show_password_form = FALSE;
			}

			// if posted
			if ($show_password_form === TRUE && $this->input->post()) {
				// set validation rules
				$this->form_validation->set_rules('online_booking_password', 'Password', 'trim|xss_clean|required');

				if ($this->form_validation->run() == FALSE) {
					$errors = $this->form_validation->error_array();
				} else if ($this->input->post('online_booking_password') != $block_info->online_booking_password) {
					$errors[] = 'Password is invalid';
				} else {
					// all ok
					$show_password_form = FALSE;

					// store password
					$this->session->set_userdata('online_booking_password', $this->input->post('online_booking_password'));
				}
			}

			if ($show_password_form === TRUE) {
				// output
				$data = array(
					'title' => $title,
					'body_class' => $body_class,
					'blockID' => $blockID,
					'success' => $success,
					'error' => $error,
					'errors' => $errors,
					'info' => $info,
					'block' => $block_info
				);
				$this->booking_view('online-booking/book/password', $data);
				return;
			}
		}

		// get participants
		$participants = $this->cart_library->get_participants($blockID);
		$fieldContact = NULL;$fieldChild = NULL; $arrayContact = array();$arrayChild = array();
		foreach($participants as $participant){
			if(isset($participant->contactID)){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->contactID);
			}
			if(isset($participant->childID)){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->childID);
			}
			if(isset($participant->type) && $participant->type === "child"){
				$fieldChild = 'childID';
				array_push($arrayChild, $participant->Id);
			}
			if(isset($participant->type) && $participant->type === "parent"){
				$fieldContact = 'contactID';
				array_push($arrayContact, $participant->Id);
			}
		}
		$already_booked_subscriptions = array();

		if ($prevent_booking !== TRUE) {
			// get block bookingID
			$query_part ='';
			if(!empty($fieldChild) && !empty($fieldContact)){
				$query_part = 'AND (childID IN ('.implode(",", $arrayChild).') OR
			contactID IN ('.implode(",", $arrayContact).'))';
			}elseif(!empty($fieldChild) && empty($fieldContact)){
				$query_part = 'AND childID IN ('.implode(",", $arrayChild).')';
			}elseif(empty($fieldChild) && !empty($fieldContact)){
				$query_part = 'AND contactID IN ('.implode(",", $arrayContact).')';
			}
			$sql = 'SELECT subID, childID, contactID,status FROM ' . $this->db->dbprefix('participant_subscriptions') . ' WHERE
			accountID = '.$this->cart_library->accountID.' AND status = "active" '.$query_part.' GROUP BY contactID, childID, subID';
			$res = $this->db->query($sql);

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$already_booked_subscriptions[empty($row->childID)?$row->contactID:$row->childID][] = $row->subID;
				}
			}
		}

		//Get Current subscription status
		$subscription_status = array();
		$query_part ='';
		if(!empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND (childID IN ('.implode(",", $arrayChild).') OR contactID IN ('.implode(",", $arrayContact).'))';
		}elseif(!empty($fieldChild) && empty($fieldContact)){
			$query_part = 'AND childID IN ('.implode(",", $arrayChild).')';
		}elseif(empty($fieldChild) && !empty($fieldContact)){
			$query_part = 'AND contactID IN ('.implode(",", $arrayContact).')';
		}

		$sql = 'SELECT subID, childID, contactID, status, modified FROM ' . $this->db->dbprefix('participant_subscriptions') . ' WHERE
						accountID = '.$this->cart_library->accountID.' AND status <> "active" '.$query_part.' GROUP BY contactID, childID, subID';
		$res = $this->db->query($sql);

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['status'] = $row->status;
				$action_performed_date = date("d-m-Y", strtotime($row->modified));
				$subscription_status[empty($row->childID)?$row->contactID:$row->childID][$row->subID]['valid'] = $action_performed_date;
			}
		}

		if($online_booking_subscription_module == 1) {
			$subscriptions_only = $block_info->subscriptions_only;

			foreach($participants as $participant) {
				// get subs
				$where = array(
					'subscriptions.bookingID' => $block_info->bookingID,
					'subscriptions.accountID' => $this->cart_library->accountID,
				);

				$subs_res = $this->db->select('subscriptions.subID, no_of_sessions_per_week, session_cut_off, subName, frequency, price, individual_subscription')
					->from('subscriptions')
					->where($where)
					->group_by('subscriptions.subID')
					->get();

				if($subs_res->num_rows() > 0) {
					foreach($subs_res->result() as $sub) {
						if($sub->individual_subscription == true) {
							$where = array(
								'childID' => $participant->$participant_id_field,
								'subID' => $sub->subID,
								'status !=' => 'cancelled'
							);
							$indivdual_subscription = $this->db->from('participant_subscriptions')->where($where)->get();
							if($indivdual_subscription->num_rows() == 0){
								continue;
							}
						}

						$where = array(
							'subscriptions_lessons_types.accountID' => $this->cart_library->accountID,
							'subscriptions_lessons_types.subID' => $sub->subID
						);


						/*$lesson_types = $this->db->select('GROUP_CONCAT( ' . $this->db->dbprefix('lesson_types') . '.name  SEPARATOR\', \') as types')
							->from('subscriptions_lessons_types')
							->join('lesson_types', 'subscriptions_lessons_types.typeID = lesson_types.typeID')
							->where($where)
							->get();

						$types = '';
						if($lesson_types->num_rows() > 0) {
							foreach($lesson_types->result() as $types) break;
							$types = ' (' . $types->types . ')';
						}*/
						$field='';
						if(isset($participant->$participant_id_field)){
							$field = $participant->$participant_id_field;
						}
						if(isset($participant->type)){
							$field = $participant->Id;
						}

						$subs[$field][$sub->subID] = array(
							'label' => $sub->subName . ' (' . currency_symbol($this->cart_library->accountID) . $sub->price . ' - ' . ucfirst($sub->frequency) . ')',
							'frequency' => ucfirst($sub->frequency),
							'price' => $sub->price,
							'no_of_sessions_per_week' => $sub->no_of_sessions_per_week,
							'session_cut_off' => $sub->session_cut_off
						);
					}
				}
			}
		}

		// if all checks passed, continue
		if ($prevent_booking !== TRUE) {
			// look up all current and future blocks in booking
			$where = array(
				'bookings_blocks.bookingID' => $block_info->bookingID
			);
			$search_fields = [];
			if (!$this->in_crm) {
				$where['bookings.disable_online_booking !='] = 1;
				$where['bookings_blocks.endDate >='] = date('Y-m-d');
				$search_fields['future_only'] = true;
			} else if ($this->cart_library->cart_type == 'booking') {
				$search_fields['show_all'] = TRUE;
			}
			// if booking requirement is remaining weeks and in crm
			if ($booking_requirement === 'remaining' && $this->in_crm) {
				if ($this->cart_library->cart_type == 'booking') {
					// booking, only look up weeks since booking date else it'll charge for past sessions as well
					$where_cart = [
						'accountID' => $this->cart_library->accountID,
						'cartID' => $this->cart_library->cartID
					];
					$res_cart = $this->db->from('bookings_cart')->where($where_cart)->limit(1)->get();
					if ($res_cart->num_rows() > 0) {
						foreach ($res_cart->result() as $cart_info) {
							$search_fields['sessions_after'] = $cart_info->booked;
						}
					}
				} else {
					// cart, only look up future weeks else it'll charge for past sessions as well
					$search_fields['future_only'] = true;
				}
			}
			$blocks = $this->cart_library->get_blocks($where, $search_fields);

			// no blocks found, 404
			if (count($blocks) == 0) {
				show_404();
			}

			// check if already in cart
			$booked_sessions = $this->cart_library->get_booked_sessions();
			foreach ($booked_sessions as $block_id => $sessions) {
				if (in_array($block_id, array_keys($blocks))) {
					foreach ($sessions as $date => $lessons) {
						foreach ($lessons as $lessonID => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (!isset($selected_lessons[$lessonID][$date])) {
									$selected_lessons[$lessonID][$date] = array();
								}
								$selected_lessons[$lessonID][$date][] = $participantID;
								$selected_participants[$participantID] = $participantID;
								$already_in_cart = TRUE;
							}
						}
					}
				}
			}

			if ($this->input->post('process') == 1) {
				$selected_participants = (array)$this->input->post('participants');
				$selected_lessons = (array)$this->input->post('lessons');
				$monitoring_existing = (array)$this->input->post('monitoring');
				$register_type = $this->input->post('register_type');
				$selected_subs = (array) $this->input->post('subscriptions');

				//Subscription only event
				if($online_booking_subscription_module && $block->subscriptions_only === '1'){
					foreach ($block->lessons as $lesson_index => $lesson_value){
						if(isset($already_booked_sessions) && isset($already_booked_sessions[$lesson_index])){
							continue;
						}
						foreach ($block->dates as $date_index => $date_value) {
							if (strtotime($date_index) > strtotime(date("Y-m-d"))) {
								foreach ($selected_participants as $participant_index => $participant_value) {
									$selected_lessons[$lesson_index][$date_index][$participant_index] = $participant_value;
								}
								break 2;
							}
						}
					}
				}

				// if booking all, only have first date, so populate with all dates
				if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
					$new_selected_lessons = array();
					if (count($selected_lessons) > 0) {
						// loop lessons
						foreach ($selected_lessons as $lessonID => $dates) {
							foreach ($dates as $date => $participantIDs) {
								foreach ($participantIDs as $participantID) {
									// loop block to find out which block session is in
									foreach ($blocks as $block) {
										if (array_key_exists($lessonID, $block->lessons)) {
											// session is in this block, get dates
											foreach ($block->dates as $tmp_date => $lessons) {
												// if session happens on this date, return
												if (array_key_exists($lessonID, $lessons)) {
													if (!isset($new_selected_lessons[$lessonID][$tmp_date])) {
														$new_selected_lessons[$lessonID][$tmp_date] = array();
													}
													$new_selected_lessons[$lessonID][$tmp_date][] = $participantID;
												}
											}
										}
									}
								}
							}
						}
					}
					$selected_lessons = $new_selected_lessons;
				}

				// if a block requires all sessions, force all (even if not selected on front end for some reason)
				if (count($selected_lessons) > 0) {
					// loop lessons
					foreach ($selected_lessons as $lessonID => $dates) {
						foreach ($dates as $date => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								// loop block to find out which block session is in
								foreach ($blocks as $block) {
									// if this session is in a block that requires all sessions selected
									if (array_key_exists($lessonID, $block->lessons) && $block->require_all_sessions == 1) {
										// loop dates and sessions and add misssing
										foreach ($block->dates as $tmp_date => $lessons) {
											foreach ($lessons as $lessonID => $lesson_details) {
												if (!isset($selected_lessons[$lessonID][$tmp_date])) {
													$selected_lessons[$lessonID][$tmp_date] = array();
												}
												if (!in_array($participantID, $selected_lessons[$lessonID][$tmp_date])) {
													$selected_lessons[$lessonID][$tmp_date][] = $participantID;
												}
											}
										}
									}
								}
							}
						}
					}
				}

				// check not trying to book sessions they already booked
				$new_selected_lessons = array();
				if (count($selected_lessons) > 0) {
					// loop lessons
					foreach ($selected_lessons as $lessonID => $dates) {
						foreach ($dates as $date => $participantIDs) {
							foreach ($participantIDs as $participantID) {
								if (isset($already_booked_sessions[$lessonID][$date]) && in_array($participantID, $already_booked_sessions[$lessonID][$date])) {
									// session already booked in another session, skip
								} else {
									// ok
									if (!isset($new_selected_lessons[$lessonID][$date])) {
										$new_selected_lessons[$lessonID][$date] = array();
									}
									$new_selected_lessons[$lessonID][$date][] = $participantID;
								}
							}
						}
					}
				}
				$selected_lessons = $new_selected_lessons;
				$res = $this->cart_library->process_block($blockID, $selected_lessons);


				if (!is_string($res)) {
					$blocks = $this->cart_library->get_blocks($where, $blocks_where);
					$selected_lessons = $res;
				} else {
					switch ($res) {
						case 'added':
							if (!$this->in_crm) {
								$success = 'This item has now been added to the <a href="' . site_url($this->cart_base . 'cart') . '">booking cart</a>. You can continue to browse or book other events on the page below or if your booking is now complete: <a href="' . site_url($this->cart_base . 'checkout') . '">click here to confirm and checkout</a>.<br>
								<a href="' . site_url($this->cart_base . 'cart') . '" class="btn">View Booking Cart</a> <a href="' . site_url($this->cart_base . 'checkout') . '" class="btn"><i class="fas fa-shopping-cart"></i> Checkout Now</a>';
								$this->session->set_flashdata('success', $success);
								$this->session->set_flashdata('added_to_cart', TRUE);
								redirect($this->online_booking->account->default_view);
							} else {
								$success = 'Booking added successfully';
								$this->session->set_flashdata('success', $success);
								redirect($this->cart_base . 'cart');
							}
							break;
						case 'updated':
							if (!$this->in_crm) {
								$success = 'Your booking has been updated successfully';
							} else {
								$success = 'Booking updated successfully';
							}
							$this->session->set_flashdata('success', $success);
							redirect($this->cart_base . 'cart');
							break;
					}
				}
			} else {

				if ($already_in_cart === TRUE) {
					if (!$this->in_crm) {
						$info = 'You already have this event in your cart which you can edit below';
					} else if ($this->cart_library->cart_type != 'booking') {
						$info = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' already has this event in their cart which you can edit below';
					}
				} else {
					$potential_participant = $this->input->get('participant');
					if (!empty($potential_participant) && array_key_exists($potential_participant, $participants)) {
						$selected_participants[] = $potential_participant;
					}
				}

				//check for selected subscriptions
				$where = array(
					'cartID' => $this->cart_library->cartID,
					'accountID' => $this->cart_library->accountID,
				);

				$res = $this->db->from('bookings_cart_subscriptions')
					->where($where)
					->get();

				if($res->num_rows() > 0) {
					foreach($res->result() as $sub) {
						$field_val = $sub->$participant_id_field;
						if(isset($sub->contactID) &&  $sub->contactID !== '0' && !empty($sub->contactID)){
							$field_val = $sub->contactID;
						}
						if(isset($sub->childID) &&  $sub->childID !== '0' && !empty($sub->childID)){
							$field_val = $sub->childID;
						}
						$selected_subs[$field_val] = $sub->subID;
						$selected_participants[$field_val] = $field_val;
					}
					$info = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' already has this event in their cart which you can edit below';
				}


				// check for existing monitoring fields
				$where = array(
					'bookingID' => $block_info->bookingID,
					'cartID' => $this->cart_library->cartID
				);
				$res = $this->db->from('bookings_cart_monitoring')
					->where($where)
					->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						foreach($monitoring_fields as $key => $label) {
							if ($key == 'medical') {
								continue;
							}
							$field = 'monitoring' . $key;
							if (!empty(trim($row->$field))) {
								$monitoring_existing[$key][$row->$participant_id_field] = $row->$field;
							}
						}
					}
				}

				// check for medical field
				if (array_key_exists('medical', $monitoring_fields) && count($participants) > 0) {
					// get participants
					$res = $this->db->select($participant_id_field . ', medical')
						->from($participants_table)
						->where_in($participant_id_field, array_keys($participants))
						->get();
					if ($res->num_rows() > 0) {
						foreach ($res->result() as $row) {
							if ($row->medical == 'Contact Participant') {
								$monitoring_existing['medical'][$row->$participant_id_field] = 1;
							}
						}
					}
				}
			}

		}

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		} else if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		} else if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		} else if ($this->cart_library->get_errors() !== FALSE) {
			$errors = $errors + $this->cart_library->get_errors();
		}

		// output
		$data = array(
			'title' => $title,
			'register_type' => $register_type,
			'body_class' => $body_class,
			'blockID' => $blockID,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'blocks' => $blocks,
			'participants' => $participants,
			'selected_participants' => $selected_participants,
			'subs' => $subs,
			'selected_subs' => $selected_subs,
			'subscriptions_only' => $subscriptions_only,
			'selected_lessons' => $selected_lessons,
			'new_participants_link' => $new_participants_link,
			'new_adults_link' => $new_adults_link,
			'already_in_cart' => $already_in_cart,
			'already_booked_sessions' => $already_booked_sessions,
			'already_booked_subscriptions' => $already_booked_subscriptions,
			'subscription_status' => $subscription_status,
			'monitoring_fields' => $monitoring_fields,
			'monitoring_fields_configs' => $monitoring_fields_configs,
			'monitoring_existing' => $monitoring_existing,
			'in_crm' => $this->in_crm,
			'cart_base' => $this->cart_base,
			'fa_weight' => $this->fa_weight,
			'buttons' => $this->buttons,
			'icon' => $icon,
			'hiddenflag' => $hiddenflag,
			'breadcrumb_levels' => $breadcrumb_levels
		);
		$view = 'online-booking/book/book';

		if ($prevent_booking === TRUE) {
			$view = 'online-booking/prevented';
		}
		if ($this->in_crm) {
			$this->crm_view($view, $data);
		} else {
			$this->booking_view($view, $data);
		}
	}
}
