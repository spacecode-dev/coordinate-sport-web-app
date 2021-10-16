<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

trait Cart_trait {
	public function index($checkout = FALSE) {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
			// add buttons
			$this->buttons = '<a class="btn btn-info" href="' . site_url('participants/view/' . $this->cart_library->familyID) . '"><i class="' . $this->fa_weight . ' fa-user"></i> Participant Account</a> <a class="btn btn-primary" href="' . site_url($this->cart_base . 'cart/close') . '"><i class="' . $this->fa_weight . ' fa-times"></i> Close ';
			$this->buttons .= ucwords($this->cart_library->cart_type) . '</a>';

			// if resending confirmation
			if ($this->input->post('resend_confirmation')) {
				$only_current = FALSE;
				if ($this->input->post('resend_confirmation') == 'future') {
					$only_current = TRUE;
				}
				if ($this->crm_library->send_event_confirmation($this->cart_library->cartID, $only_current)) {
					$success = 'Confirmation resent successfully';
					$this->session->set_flashdata('success', $success);
				} else {
					$error = 'Confirmation could not be sent';
					if ($only_current) {
						$error .= ', have all sessions already ended?';
					}
					$this->session->set_flashdata('error', $error);
				}

				redirect('booking/cart');
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// set defaults
		$title = 'Booking Cart';
		$body_class = 'cart';
		$view = 'online-booking/cart/cart';
		$success = NULL;
		$error = NULL;
		$info = NULL;
		$errors = array();
		$blocks = array();
		$cart_summary = NULL;
		$childcarevoucher_providers = FALSE;
		$childcarevoucher_provider_notices = [];
		$terms = NULL;
		$min_payment = 0;
		$max_payment = 0;
		$payment_gateway = NULL;
		$stripe_pk = NULL;
		$stripe_sk = NULL;
		$sagepay_environment = NULL;
		$sagepay_vendor = NULL;
		$sagepay_encryption_password = NULL;
		$allow_payment_plan = FALSE;
		$available_payment_plans = array();
		$only_subscriptions = FALSE;
		$sub_payment_provider = NULL;
		$sub_payment_amount = NULL;
		$stripe_price_id = NULL;
		$includes_subscriptions = FALSE;
		$icon = 'shopping-cart';
		$breadcrumb_levels = array(
			'booking/cart' => 'Booking Cart'
		);
		if ($checkout == 'true') {
			$checkout = TRUE;
		}
		$prevent_checkout = FALSE;
		if ($this->cart_library->cart_type == 'booking') {
			$title = 'Edit Booking';
			$icon = 'calendar-alt';
			$breadcrumb_levels = array(
				'booking/cart' => 'Edit Booking'
			);
		}
		if ($checkout === TRUE) {
			$title = 'Checkout';
			$body_class = 'checkout';
			$view = 'online-booking/cart/checkout';
		}

		// load libraries
		$this->load->library('form_validation');

		// check if user blacklisted
		if ($prevent_checkout !== TRUE && $this->cart_library->contact_blacklisted == TRUE) {
			if (!$this->in_crm) {
				$error = 'Your account has been blocked from making online bookings, please contact us to discuss your options.';
				$prevent_checkout = TRUE;
			} else {
				$error = $this->settings_library->get_label('participant', $this->cart_library->accountID) . ' has been blacklisted from making bookings. Please read the family notes for details before proceeding.';
			}
		}

		// check if cart empty
		if ($this->cart_library->count == 0) {
			if ($this->in_crm) {
				$info = 'Cart is empty. Find a <a href="' . site_url('bookings/projects') . '">project</a> and select add ' . strtolower($this->settings_library->get_label('participant', $this->cart_library->accountID)) . ' to make a booking.';
			} else {
				$info = 'Your booking cart is empty. Browse our <a href="' . site_url() . '">events</a> to make a booking.';
			}
			$prevent_checkout = TRUE;
		}

		// if checkout and prevented, redirect to cart
		if ($checkout === TRUE && $prevent_checkout === TRUE) {
			redirect($this->cart_base . 'cart');
		}

		// if not prevented, look up
		if ($prevent_checkout !== TRUE) {
			// check for voucher
			if ($this->input->post('voucher') != '') {
				$res = $this->cart_library->apply_voucher($this->input->post('voucher'));
				if ($res !== TRUE) {
					$errors = $this->cart_library->get_errors();
				}
			}

			// get booked blocks
			$blocks = $this->cart_library->get_booked_blocks();

			// get cart summary
			$cart_summary = $this->cart_library->get_cart_summary();
		}

		// calculations complete
		if ($checkout === TRUE && $prevent_checkout !== TRUE && $this->cart_library->cart_type != 'booking') {
			// if checkout

			// validate cart
			$this->cart_library->validate_cart();
			$errors = $this->cart_library->get_errors();

			// refresh cart summary
			$cart_summary = $this->cart_library->get_cart_summary();

			$only_subscriptions = $this->cart_library->is_only_subscriptions();

			$includes_subscriptions = $this->cart_library->cart_includes_subscriptions();

			if($includes_subscriptions) {
				$sub_payment_provider = $this->cart_library->get_cart_subscription_payment_provider();
				if(is_array($sub_payment_provider) && in_array('stripe', $sub_payment_provider)) {
					$sub_payment_amount = $this->cart_library->get_cart_subscription_amount('stripe');
					$stripe_price_id = $this->cart_library->get_stripe_price_id();
					$amount_to_include_flag = 1;
				}
				if(is_array($sub_payment_provider) && in_array('gocardless', $sub_payment_provider)){
					$gocardless_total_amount = $this->cart_library->get_cart_subscription_amount('gocardless');
				}
			}

			// if require full payment include any amount over credit limit + booking total
			if ($this->settings_library->get('require_full_payment', $this->cart_library->accountID)) {
				// if has due balance
				if ($this->cart_library->get_family_account_balance() < 0) {
					$amount_over_credit_limit = ($this->cart_library->get_family_account_balance()*-1) - $this->cart_library->get_family_credit_limit();
					// if somehow positive, set to 0
					if ($amount_over_credit_limit < 0) {
						$amount_over_credit_limit = 0;
					}
				} else {
					// if account is in credit, reduce min payment
					$min_payment = $cart_summary['total'] - $this->cart_library->get_family_account_balance();
				}
			} else {
				// partial payment allowed, work out total + account balance minus credit limit
				$min_payment = $cart_summary['total'] + ($this->cart_library->get_family_account_balance()*-1) - $this->cart_library->get_family_credit_limit();
			}

			//Check if Gocardless subscription then remove amount from the min total
			if(isset($gocardless_total_amount)) {
				if($min_payment > 0) {
					$min_payment = $min_payment - $gocardless_total_amount;
				}
			}

			// if main payment negative due to credit limit, set to 0
			if ($min_payment < 0) {
				$min_payment = 0;
			}

			// if in crm, min payment is 0
			if ($this->in_crm) {
				$min_payment = 0;
			}

			// get payment gateway
			$payment_gateway = $this->settings_library->get('cc_processor', $this->cart_library->accountID);
			if ($this->in_crm && in_array($payment_gateway, ['stripe', 'sagepay'])) {
				// sage/stripe not available within crm
				$payment_gateway = NULL;
			}

			switch ($payment_gateway) {
				case 'stripe':
					$stripe_pk = $this->settings_library->get('stripe_pk', $this->cart_library->accountID);
					$stripe_sk = $this->settings_library->get('stripe_sk', $this->cart_library->accountID);
					if (empty($stripe_pk) || empty($stripe_sk)) {
						$prevent_checkout = TRUE;
						$error = 'Invalid payment gateway configuration';
					}
					break;
				case 'sagepay':
					$sagepay_environment = $this->settings_library->get('sagepay_environment', $this->cart_library->accountID);
					$sagepay_vendor = $this->settings_library->get('sagepay_vendor', $this->cart_library->accountID);
					$sagepay_encryption_password = $this->settings_library->get('sagepay_encryption_password', $this->cart_library->accountID);
					if (empty($sagepay_environment) || empty($sagepay_vendor) || empty($sagepay_encryption_password)) {
						$prevent_checkout = TRUE;
						$error = 'Invalid payment gateway configuration';
					}
					$sagepay_is_production = FALSE;
					if ($sagepay_environment == 'production') {
						$sagepay_is_production = TRUE;
					}
					// sage pay only supports GBP
					if (currency_code($this->cart_library->accountID) !== 'GBP') {
						$prevent_checkout = TRUE;
						$error = currency_code($this->cart_library->accountID) .  ' currency not supported';
					}
					break;
				default:
					if (!$this->in_crm && $cart_summary['total'] > 0) {
						$prevent_checkout = TRUE;
						$error = 'Missing payment gateway configuration';
					}
					break;
			}

			// work out max payment
			$max_payment = $cart_summary['total'] + ($this->cart_library->get_family_account_balance()*-1);
			if($includes_subscriptions){
				$max_payment = $cart_summary['total'];
			}
			if ($max_payment < 0) {
				$max_payment = 0;
			}

			// get list of childcare voucher providers
			$childcarevoucher_providers = $this->cart_library->get_childcarevoucher_providers();

			// get list of childcare voucher provider notices
			$childcarevoucher_provider_notices = $this->cart_library->get_childcarevoucher_providers(TRUE);

			// get terms
			$terms = $this->settings_library->get('terms_individual', $this->cart_library->accountID);

			// check if can pay by payment plan (not in crm)
			$args = array(
				'accountID' => $this->cart_library->accountID
			);
			$this->load->library('gocardless_library', $args);

			if (!$this->in_crm && $this->gocardless_library->valid_config() && $this->input->get('redirect_flow_id') && (is_array($sub_payment_provider) && in_array('gocardless', $sub_payment_provider))) {
				// valid config
				if($this->gocardless_library->confirm_mandate()) {
					$sub_success = $this->gocardless_library->start_subscription($this->cart_library->cartID, $this->cart_library->contactID);
					$data = array(
						'type' => 'booking',
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'booked' => mdate('%Y-%m-%d %H:%i:%s')
					);
					$cartID = $this->cart_library->cartID;
					$where = array(
						'cartID' => $cartID,
						'accountID' => $this->cart_library->accountID
					);
					$res = $this->db->update('bookings_cart', $data, $where, 1);

					// calc family balance
					$this->crm_library->recalc_family_balance($this->cart_library->familyID);

					if($sub_success) {
						$cart_success_redirect = site_url('account');
						$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";

						//Validate profile details after booking is made
						$validation = $this->online_booking->validate_profile_details("existing");

						if ($validation!==true) {
							$this->session->set_flashdata('errors', $validation);
							$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
							$cart_success_redirect = site_url("account/profile");
						}
						$this->session->set_flashdata('success', $cart_success_message);

						// redirect
						redirect($cart_success_redirect);
					}
				}
			} elseif (!$this->in_crm && $this->gocardless_library->valid_config()) {
				$where = array(
					'bookings_cart.cartID' => $this->cart_library->cartID
				);
				$res = $this->db->select('MAX('. $this->db->dbprefix('bookings_cart_sessions') . '.date) as max_date')
					->from('bookings_cart')
					->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
					->where($where)
					->group_by('bookings_cart.cartID')
					->get();
				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						if (strtotime($row->max_date) > strtotime('+2 months')) {
							// work out months to date of last block booked
							$now = new DateTime(date('Y-m-d H:i:s', time()));
							$end = new DateTime(date('Y-m-d H:i:s', strtotime($row->max_date)));
							$diff = $now->diff($end);
							$min_months = 2;
							$max_months = $diff->m + ($diff->y*12);
							// only allow if 2 or more months in future
							if ($max_months >= $min_months) {
								$allow_payment_plan = TRUE;
								for ($i=$min_months; $i <= $max_months; $i++) {
									// if 3 months, skip as its same deal as 2, but with unequal payments due to taking 2 months first
									if ($i == 3) {
										continue;
									}
									// work out interval amount, by dividing total amount / number of payments
									$interval_amount = round($cart_summary['total']/$i, 2);
									// get interval count
									$interval_count = $i;
									// get interval unit
									$interval_unit = 'month';
									// get interval length, e.g. every x period
									$interval_length = 1;
									// set set up fee to 0 payment
									$setup_fee = 0;

									// if more than 2 intervals originally, take 1 payment as set up fee
									// gocardless library needs updating if we want to support this
									/*if ($interval_count > 2) {
										$interval_count--;
										$setup_fee = $interval_amount;
									}*/

									// work out label
									if ($setup_fee == 0) {
										$label = $interval_count . ' monthly payment';
										if ($interval_count != 1) {
											$label .= 's';
										}
										$label .= ' of ' . currency_symbol($this->cart_library->accountID) . number_format($interval_amount, 2);
									} else {
										$label = 'Initial payment of ' . currency_symbol($this->cart_library->accountID) . number_format($setup_fee + $interval_amount, 2) . ' followed by ' . ($interval_count-1) . ' monthly payment';
										if ($interval_count != 1) {
											$label .= 's';
										}
										$label .= ' of ' . currency_symbol($this->cart_library->accountID) . number_format($interval_amount, 2);
									}

									// use original values
									$available_payment_plans[$i] = array(
										'label' => $label,
										'interval_count' => $i,
										'interval_length' => $interval_length,
										'interval_unit' => $interval_unit,
									);
								}
							}
						}
					}
				}
			}

			if ($prevent_checkout !== TRUE && $this->input->post('action') == 'checkout') {
				// set validation rules
				if ($childcarevoucher_providers !== FALSE) {
					$this->form_validation->set_rules('childcarevoucher', 'Childcare Voucher', 'trim|xss_clean');
					if ($this->input->post('childcarevoucher') == '1') {
						$this->form_validation->set_rules('childcarevoucher_providerID', 'Childcare Voucher Provider', 'trim|xss_clean|required|callback_check_childcarevoucher_provider');
						$min_payment = 0;
					}
				}

				$this->form_validation->set_rules('payment_amount', 'Payment Amount', 'trim|xss_clean|greater_than_equal_to[' . $min_payment . ']|less_than_equal_to[' . $max_payment . ']');

				if (!empty(trim(strip_tags($terms)))) {
					$this->form_validation->set_rules('agree_terms', 'You must agree to the terms & conditions', 'trim|xss_clean|required|callback_is_checked');
				}

				if ($allow_payment_plan) {
					$this->form_validation->set_rules('payment_method', 'Payment Method', 'trim|xss_clean|required');
					if ($this->input->post('payment_method') == 'plan') {
						$this->form_validation->set_rules('payment_plan', 'Payment Plan', 'trim|xss_clean|required');
					}
				}

				if ($this->form_validation->run() == FALSE) {
					$errors = $this->form_validation->error_array();
				} else {
					// save above fields
					$data = array(
						'subtotal' => $cart_summary['subtotal'],
						'discount' => $cart_summary['discount'],
						'total' => $cart_summary['total'],
						'balance' => $cart_summary['total'],
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					if (set_value('childcarevoucher_providerID') > 0) {
						$data['childcarevoucher_providerID'] = set_value('childcarevoucher_providerID');
						// look up provider
						$where = array(
							'accountID' => $this->cart_library->accountID,
							'providerID' => set_value('childcarevoucher_providerID'),
							'active' => 1
						);
						$res = $this->db->from('settings_childcarevoucherproviders')->where($where)->limit(1)->get();
						if ($res->num_rows() > 0) {
							foreach ($res->result() as $row) {
								$data['childcarevoucher_provider'] = $row->name;
								$data['childcarevoucher_ref'] = $row->reference;
							}
						}
					}

					// update cart
					$where = array(
						'cartID' => $this->cart_library->cartID,
						'accountID' => $this->cart_library->accountID
					);
					$res = $this->db->update('bookings_cart', $data, $where, 1);

					// determine success redirect
					$cart_success_redirect = 'account/booking/' . $this->cart_library->cartID . '#details';
					$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
					if ($this->in_crm) {
						$cart_success_redirect = 'participants/bookings/view/' . $this->cart_library->cartID;
						$cart_success_message = "Booking successful. You can find the details below and we've also sent the " . strtolower($this->settings_library->get_label('participant', $this->cart_library->accountID)) . " a confirmation email with this information";
					}
					else {
						//Validate profile details after booking is made
						$validation = $this->online_booking->validate_profile_details("existing");

						if ($validation!==true) {
							$this->session->set_flashdata('errors', $validation);
							$cart_success_redirect = site_url("account/profile");
							$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
						}
					}

					// check payment amount
					if (($cart_summary['total'] == 0 || set_value('payment_amount') == 0 || $max_payment <= 0) && $includes_subscriptions === FALSE) {
						// convert to booking
						$data = array(
							'type' => 'booking',
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'booked' => mdate('%Y-%m-%d %H:%i:%s')
						);
						$cartID = $this->cart_library->cartID;
						$where = array(
							'cartID' => $cartID,
							'accountID' => $this->cart_library->accountID
						);
						$res = $this->db->update('bookings_cart', $data, $where, 1);

						// calc family balance
						$this->crm_library->recalc_family_balance($this->cart_library->familyID);

						// send booking email
						$this->crm_library->send_event_confirmation($cartID);

						// tell user
						$this->session->set_flashdata('success', $cart_success_message);

						// if in crm, close cart
						if ($this->in_crm) {
							// remove from session
							$this->session->unset_userdata('cart_contactID');
							$this->session->unset_userdata('cart_cartID');
						}

						// redirect
						redirect($cart_success_redirect);
					}
					else if ($allow_payment_plan && set_value('payment_method') === 'plan') {
						// get plan
						$plan = $available_payment_plans[set_value('payment_plan')];

						// insert payment plan
						$data = array(
							'accountID' => $this->cart_library->accountID,
							'familyID' => $this->cart_library->familyID,
							'contactID' => $this->cart_library->contactID,
							'cartID' => $this->cart_library->cartID,
							'interval_count' => $plan['interval_count'],
							'interval_length' => $plan['interval_length'],
							'interval_unit' => $plan['interval_unit'],
							'amount' => $cart_summary['total'],
							'status' => 'inactive',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						);
						$this->db->insert('family_payments_plans', $data);
						$planID = $this->db->insert_id();

						$mandate_link = $this->gocardless_library->new_payment_plan($planID, FALSE);
						$cartID = $this->cart_library->cartID;

						if ($mandate_link !== FALSE) {
							if (is_string($mandate_link)) {
								// redirect
								redirect($mandate_link);
							} else {
								// already has a mandate, convert to booking
								$data = array(
									'type' => 'booking',
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'booked' => mdate('%Y-%m-%d %H:%i:%s')
								);
								$where = array(
									'cartID' => $cartID,
									'accountID' => $this->cart_library->accountID
								);
								$res = $this->db->update('bookings_cart', $data, $where, 1);

								// calc family balance
								$this->crm_library->recalc_family_balance($this->cart_library->familyID);

								// send booking email
								$this->crm_library->send_event_confirmation($cartID);

								// tell user
								$this->session->set_flashdata('success', $cart_success_message);
								redirect($cart_success_redirect);
							}
						} else {
							$error = 'Error setting up payment plan';
						}
					}
					else if ($only_subscriptions !== TRUE || ($allow_payment_plan === TRUE && set_value('payment_method') === 'card')) {
						switch ($payment_gateway) {
							case 'stripe';
								\Stripe\Stripe::setApiKey($stripe_sk);
								try {
									$payment_intent_id = $this->input->post('payment_intent_id');

									// if no payment intent

									if (empty($payment_intent_id)) {
										if(!$includes_subscriptions) {
											throw new \Exception("Error receiving payment data", 1);
										}
									}else{
										// add description
										$desc = $this->cart_library->contact_name . ' (#' . $this->cart_library->contactID . ')';
										\Stripe\PaymentIntent::update($payment_intent_id, [
											'description' => $desc
										]);

										// capture intent
										$intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);

										$intent->capture();
										if($intent->status === "succeeded"){
											// apply payment to account
											$data = array(
												'accountID' => $this->cart_library->accountID,
												'familyID' => $this->cart_library->familyID,
												'contactID' => $this->cart_library->contactID,
												'amount' => ($intent->amount/100),
												'method' => 'online',
												'transaction_ref' => $intent->id,
												'locked' => 1,
												'added' => mdate('%Y-%m-%d %H:%i:%s'),
												'modified' => mdate('%Y-%m-%d %H:%i:%s')
											);
											$this->db->insert('family_payments', $data);
											$paymentID = $this->db->insert_id();

											// send payment email
											$this->crm_library->send_payment_confirmation($paymentID);
										}
									}

									//check error for subscription first
									if($includes_subscriptions) {
										$subscriptions_content = $this->input->post('subscriptions')[0];
										$subscriptions = (array)json_decode(stripslashes($subscriptions_content));
										if(count($subscriptions) == 0 && is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)){
											throw new \Exception("Error receiving subscription data", 1);
										}
									}

									if($includes_subscriptions) {
										$subscriptions_content = $this->input->post('subscriptions')[0];
										$subscriptions = (array)json_decode(stripslashes($subscriptions_content));
										if(is_array($sub_payment_provider) && in_array('stripe', $sub_payment_provider) && count($subscriptions) > 0) {
											// if no payment intent
											if (count($subscriptions) == 0) {
												throw new \Exception("Error receiving subscription data", 1);
											}

											$where = array(
												'bookings_cart_subscriptions.cartID' => $this->cart_library->cartID,
												'bookings_cart_subscriptions.accountID' => $this->cart_library->accountID
											);

											$bookings_register_type = $this->db->select('register_type')
												->from('bookings')
												->join('bookings_cart_subscriptions', 'bookings.bookingID = bookings_cart_subscriptions.bookingID', 'inner')
												->where($where)
												->get();

											$participants_id_field = 'childID';
											$check_flag = FALSE;

											foreach ($bookings_register_type->result() as $register_type) {
												if (strpos($register_type->register_type, 'individuals') === 0) {
													$participants_id_field = 'contactID';;
												}
												if (strpos($register_type->register_type, 'adults_children') === 0) {
													$check_flag = TRUE;
												}
											}

											$subs = $this->db
												->select('subscriptions.subID, subscriptions.price, subscriptions.stripe_price_id, subscriptions.frequency, subscriptions.subName, bookings_cart_subscriptions.childID, bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.bookingID')
												->from('bookings_cart_subscriptions')
												->join('subscriptions', '(' . $this->db->dbprefix('bookings_cart_subscriptions') . '.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->db->dbprefix('subscriptions') . '.bookingID)', 'inner')
												->where($where)
												->group_by('bookings_cart_subscriptions.subID')
												->get();

											foreach ($subs->result() as $sub) {
												//check if current cart item is stripe subscription
												if(!isset($subscriptions[$sub->stripe_price_id])){
													continue;
												}

												//check if has active subscription
												$participantID = $sub->$participants_id_field;
												if($check_flag){
													if(!empty($sub->childID) && $sub->childID !== "0")
													{
														$participantID = $sub->childID;
													}else{
														$participantID = $sub->contactID;
													}
													$participants_id_field= $this->cart_library->check_user_type($participantID);
												}

												if($subscriptions[$sub->stripe_price_id]->qty > 1){
													if(!empty($subscriptions[$sub->stripe_price_id]->childID)){
														$childIDs = explode(",",$subscriptions[$sub->stripe_price_id]->childID);
														foreach($childIDs as $childId){
															if(empty($childId)){
																continue;
															}
															$where = array(
																'status' => 'active',
																'accountID' => $this->cart_library->accountID,
																'childID' => $childId,
																'subID' => $sub->subID
															);

															$res = $this->db->from('participant_subscriptions')->where($where)->get();
															if ($res->num_rows() > 0) {
																continue;
															}

															$data = array(
																'childID' => $childId,
																'accountID' => $this->cart_library->accountID,
																'subID' => $sub->subID,
																'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
																'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete')?'inactive':$subscriptions[$sub->stripe_price_id]->status,
																'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																'added' => mdate('%Y-%m-%d %H:%i:%s')
															);

															$res = $this->db->insert('participant_subscriptions', $data);

															//send email and covert to booking
															if ($this->db->insert_id()) {
																if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
																	if(is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
																		$data = array(
																			'type' => 'booking',
																			'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																			'booked' => mdate('%Y-%m-%d %H:%i:%s')
																		);
																		$where = array(
																			'cartID' => $this->cart_library->cartID,
																			'accountID' => $this->cart_library->accountID
																		);

																		$res = $this->db->update('bookings_cart', $data, $where, 1);
																	}

																	if (!$this->in_crm) {
																		//Validate profile details after booking is made
																		$validation = $this->online_booking->validate_profile_details("existing");

																		if ($validation!==true) {
																			$this->session->set_flashdata('errors', $validation);
																			$cart_success_redirect = site_url("account/profile");
																			$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
																		}
																		else {
																			$cart_success_redirect = site_url('account');
																			$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
																		}
																	} else {
																		$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
																		$cart_success_message = "Booking successful. You can find the details below.";
																	}

																	$this->session->set_flashdata('success', $cart_success_message);
																}
															}
														}
													}
													if(!empty($subscriptions[$sub->stripe_price_id]->contactID)){
														$contactIDs = explode(",",$subscriptions[$sub->stripe_price_id]->contactID);
														foreach($contactIDs as $contactId){
															if(empty($contactId)){
																continue;
															}
															$where = array(
																'status' => 'active',
																'accountID' => $this->cart_library->accountID,
																'contactID' => $contactId,
																'subID' => $sub->subID
															);

															$res = $this->db->from('participant_subscriptions')->where($where)->get();
															if ($res->num_rows() > 0) {
																continue;
															}

															$data = array(
																'contactID' => $contactId,
																'accountID' => $this->cart_library->accountID,
																'subID' => $sub->subID,
																'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
																'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete')?'inactive':$subscriptions[$sub->stripe_price_id]->status,
																'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																'added' => mdate('%Y-%m-%d %H:%i:%s')
															);

															$res = $this->db->insert('participant_subscriptions', $data);

															//send email and covert to booking
															if ($this->db->insert_id()) {
																if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
																	if(is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
																		$data = array(
																			'type' => 'booking',
																			'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																			'booked' => mdate('%Y-%m-%d %H:%i:%s')
																		);
																		$where = array(
																			'cartID' => $this->cart_library->cartID,
																			'accountID' => $this->cart_library->accountID
																		);

																		$res = $this->db->update('bookings_cart', $data, $where, 1);
																	}

																	if (!$this->in_crm) {
																		//Validate profile details after booking is made
																		$validation = $this->online_booking->validate_profile_details("existing");

																		if ($validation!==true) {
																			$this->session->set_flashdata('errors', $validation);
																			$cart_success_redirect = site_url("account/profile");
																			$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
																		}
																		else {
																			$cart_success_redirect = site_url('account');
																			$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
																		}
																	} else {
																		$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
																		$cart_success_message = "Booking successful. You can find the details below.";
																	}
																	$this->session->set_flashdata('success', $cart_success_message);
																}
															}
														}
													}
												}else {
													$where = array(
														'status' => 'active',
														'accountID' => $this->cart_library->accountID,
														$participants_id_field => $participantID,
														'subID' => $sub->subID
													);

													$res = $this->db->from('participant_subscriptions')->where($where)->get();
													if ($res->num_rows() > 0) {
														continue;
													}

													$data = array(
														$participants_id_field => $participantID,
														'accountID' => $this->cart_library->accountID,
														'subID' => $sub->subID,
														'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
														'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete') ? 'inactive' : $subscriptions[$sub->stripe_price_id]->status,
														'modified' => mdate('%Y-%m-%d %H:%i:%s'),
														'added' => mdate('%Y-%m-%d %H:%i:%s')
													);

													$res = $this->db->insert('participant_subscriptions', $data);

													//send email and covert to booking
													if ($this->db->insert_id()) {
														if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
															if (is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
																$data = array(
																	'type' => 'booking',
																	'modified' => mdate('%Y-%m-%d %H:%i:%s'),
																	'booked' => mdate('%Y-%m-%d %H:%i:%s')
																);
																$where = array(
																	'cartID' => $this->cart_library->cartID,
																	'accountID' => $this->cart_library->accountID
																);

																$res = $this->db->update('bookings_cart', $data, $where, 1);
															}

															if (!$this->in_crm) {
																//Validate profile details after booking is made
																$validation = $this->online_booking->validate_profile_details("existing");

																if ($validation!==true) {
																	$this->session->set_flashdata('errors', $validation);
																	$cart_success_redirect = site_url("account/profile");
																	$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
																}
																else {
																	$cart_success_redirect = site_url('account');
																	$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
																}
															} else {
																$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
																$cart_success_message = "Booking successful. You can find the details below.";
															}
															$this->session->set_flashdata('success', $cart_success_message);
														}
													}
												}
											}
										}
										if(is_array($sub_payment_provider) && in_array('gocardless', $sub_payment_provider)) {
											$mandate_link = $this->gocardless_library->new_subscription($this->cart_library->contactID);
											if ($mandate_link !== FALSE) {
												$mandate_link;
												redirect($mandate_link);
											} else {
												$sub_success = $this->gocardless_library->start_subscription($this->cart_library->cartID, $this->cart_library->contactID);

												$data = array(
													'type' => 'booking',
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'booked' => mdate('%Y-%m-%d %H:%i:%s')
												);
												$cartID = $this->cart_library->cartID;
												$where = array(
													'cartID' => $cartID,
													'accountID' => $this->cart_library->accountID
												);
												$res = $this->db->update('bookings_cart', $data, $where, 1);

												// calc family balance
												$this->crm_library->recalc_family_balance($this->cart_library->familyID);

												if ($sub_success) {
													if (!$this->in_crm) {
														//Validate profile details after booking is made
														$validation = $this->online_booking->validate_profile_details("existing");

														if ($validation!==true) {
															$this->session->set_flashdata('errors', $validation);
															$cart_success_redirect = site_url("account/profile");
															$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
														}
														else {
															$cart_success_redirect = site_url('account');
															$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
														}
													} else {
														$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
														$cart_success_message = "Booking successful. You can find the details below.";
													}
													$this->session->set_flashdata('success', $cart_success_message);
												}
											}
										}
									}

									// convert to booking
									$data = array(
										'type' => 'booking',
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'booked' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$cartID = $this->cart_library->cartID;
									$where = array(
										'cartID' => $cartID,
										'accountID' => $this->cart_library->accountID
									);
									$res = $this->db->update('bookings_cart', $data, $where, 1);

									// send booking email
									$this->crm_library->send_event_confirmation($cartID);

									// tell user
									$this->session->set_flashdata('success', $cart_success_message);

									// calc family balance
									$this->crm_library->recalc_family_balance($this->cart_library->familyID);

									// if in crm, close cart
									if ($this->in_crm) {
										// remove from session
										$this->session->unset_userdata('cart_contactID');
										$this->session->unset_userdata('cart_cartID');
									}

									// redirect
									redirect($cart_success_redirect);
								} catch(\Stripe\Error\Card $e) {
									$body = $e->getJsonBody();
									$errors[] = str_replace('The', 'Your', $body['error']['message']);
								} catch(\Stripe\Error\InvalidRequest $e) {
									$body = $e->getJsonBody();
									$errors[] = str_replace('The', 'Your', $body['error']['message']);
								} catch (\Exception $e) {
									$errors[] = $e->getMessage();
								}
								break;
							case 'sagepay':
								$sagePay = new \Eurolink\SagePayForm\Builder([
									'isProduction' => $sagepay_is_production,
									'encryptPassword' => $sagepay_encryption_password,
									'vendor' => $sagepay_vendor,
								]);
								$sagePay->setVendorTxCode($this->cart_library->accountID . '-' . $this->cart_library->familyID . '-' . $this->cart_library->contactID . '-' . date('YmdHis') . '-' . $this->cart_library->cartID);
								$sagePay->setDescription('Payment from ' . $this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name);
								$sagePay->setCurrency(currency_code($this->cart_library->accountID));
								$sagePay->setAmount(set_value('payment_amount'));
								$sagePay->setSendEMail(1);
								$sagePay->setBillingSurname($this->online_booking->user->last_name);
								$sagePay->setBillingFirstnames($this->online_booking->user->first_name);
								$sagePay->setBillingCity($this->online_booking->user->town);
								$sagePay->setBillingPostCode($this->online_booking->user->postcode);
								$sagePay->setBillingAddress1($this->online_booking->user->address1);
								$sagePay->setBillingAddress2($this->online_booking->user->address2);
								$sagePay->setCustomerEMail($this->online_booking->user->email);
								$sagePay->setCustomerName($this->online_booking->user->first_name . ' ' . $this->online_booking->user->last_name);
								$sagePay->setBillingCountry('GB');
								$sagePay->setDeliverySameAsBilling();
								$sagePay->setSuccessURL(site_url('sagepay/checkout'));
								$sagePay->setFailureURL(site_url('sagepay/checkout'));
								?><form method="POST" action="<?php echo $sagePay->getFormEndpoint(); ?>" id="sagepay">
								<input type="hidden" name="VPSProtocol" value="<?php echo $sagePay->getVPSProtocol(); ?>">
								<input type="hidden" name="TxType" value="<?php echo $sagePay->getTxType(); ?>">
								<input type="hidden" name="Vendor" value="<?php echo $sagePay->getVendorCode(); ?>">
								<input type="hidden" name="Crypt" value="<?php echo $sagePay->getCrypt(); ?>">
								<button type="submit">Pay with SagePay</button>
							</form>
								<script>
									document.getElementById('sagepay').submit();
								</script><?php

								if($includes_subscriptions) {
									$mandate_link = $this->gocardless_library->new_subscription($this->cart_library->contactID);

									if($mandate_link !== FALSE) {
										redirect($mandate_link);
									} else {
										$this->gocardless_library->start_subscription($this->cart_library->cartID, $this->cart_library->contactID, TRUE);
									}
								}

								exit();
								break;
							default:
								// if within crm, redirect to payment page
								if ($this->in_crm) {
									// convert to booking
									$data = array(
										'type' => 'booking',
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'booked' => mdate('%Y-%m-%d %H:%i:%s')
									);
									$cartID = $this->cart_library->cartID;
									$where = array(
										'cartID' => $cartID,
										'accountID' => $this->cart_library->accountID
									);
									$res = $this->db->update('bookings_cart', $data, $where, 1);

									// calc family balance
									$this->crm_library->recalc_family_balance($this->cart_library->familyID);

									// send booking email
									$this->crm_library->send_event_confirmation($cartID);

									// prefill payment details
									$this->session->set_flashdata('payment_amount', set_value('payment_amount'));
									$this->session->set_flashdata('payment_contactID', $this->cart_library->contactID);
									$this->session->set_flashdata('payment_method', 'card');

									// tell user
									$cart_success_redirect = 'participants/payments/' . $this->cart_library->familyID . '/new';
									$cart_success_message = "Booking successful. You can apply a payment below";
									$this->session->set_flashdata('success', $cart_success_message);

									// if in crm, close cart
									if ($this->in_crm) {
										// remove from session
										$this->session->unset_userdata('cart_contactID');
										$this->session->unset_userdata('cart_cartID');
									}

									// redirect
									redirect($cart_success_redirect);
									break;
								}
								break;
						}
					} else if ($only_subscriptions === TRUE) {
						if(is_array($sub_payment_provider) && in_array('stripe', $sub_payment_provider)) {
							$subscriptions_content = $this->input->post('subscriptions')[0];
							$subscriptions = (array)json_decode(stripslashes($subscriptions_content));

							// if no payment intent
							if (count($subscriptions) == 0) {
								throw new \Exception("Error receiving subscription data", 1);
							}

							$where = array(
								'bookings_cart_subscriptions.cartID' => $this->cart_library->cartID,
								'bookings_cart_subscriptions.accountID' => $this->cart_library->accountID
							);

							$bookings_register_type = $this->db->select('register_type')
								->from('bookings')
								->join('bookings_cart_subscriptions', 'bookings.bookingID = bookings_cart_subscriptions.bookingID', 'inner')
								->where($where)
								->get();

							$participants_id_field = 'childID';
							$check_flag = FALSE;

							foreach ($bookings_register_type->result() as $register_type) {
								if (strpos($register_type->register_type, 'individuals') === 0) {
									$participants_id_field = 'contactID';;
								}
								if (strpos($register_type->register_type, 'adults_children') === 0) {
									$check_flag = TRUE;
								}
							}

							$subs = $this->db
								->select('subscriptions.subID, subscriptions.price, subscriptions.stripe_price_id, subscriptions.frequency, subscriptions.subName, bookings_cart_subscriptions.childID, bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.bookingID')
								->from('bookings_cart_subscriptions')
								->join('subscriptions', '(' . $this->db->dbprefix('bookings_cart_subscriptions') . '.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->db->dbprefix('subscriptions') . '.bookingID)', 'inner')
								->where($where)
								->group_by('bookings_cart_subscriptions.subID')
								->get();

							foreach ($subs->result() as $sub) {

								//check if current cart item is stripe subscription
								if(!isset($subscriptions[$sub->stripe_price_id])){
									continue;
								}

								//check if has active subscription
								$participantID = $sub->$participants_id_field;

								if($check_flag){
									if(!empty($sub->childID) && $sub->childID !== "0")
									{
										$participantID = $sub->childID;
									}else{
										$participantID = $sub->contactID;
									}
									$participants_id_field= $this->cart_library->check_user_type($participantID);
								}

								if($subscriptions[$sub->stripe_price_id]->qty > 1){
									if(!empty($subscriptions[$sub->stripe_price_id]->childID)){
										$childIDs = explode(",",$subscriptions[$sub->stripe_price_id]->childID);
										foreach($childIDs as $childId){
											if(empty($childId)){
												continue;
											}
											$where = array(
												'status' => 'active',
												'accountID' => $this->cart_library->accountID,
												'childID' => $childId,
												'subID' => $sub->subID
											);

											$res = $this->db->from('participant_subscriptions')->where($where)->get();
											if ($res->num_rows() > 0) {
												continue;
											}

											$data = array(
												'childID' => $childId,
												'accountID' => $this->cart_library->accountID,
												'subID' => $sub->subID,
												'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
												'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete')?'inactive':$subscriptions[$sub->stripe_price_id]->status,
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'added' => mdate('%Y-%m-%d %H:%i:%s')
											);

											$res = $this->db->insert('participant_subscriptions', $data);

											//send email and covert to booking
											if ($this->db->insert_id()) {
												if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
													if(is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
														$data = array(
															'type' => 'booking',
															'modified' => mdate('%Y-%m-%d %H:%i:%s'),
															'booked' => mdate('%Y-%m-%d %H:%i:%s')
														);
														$where = array(
															'cartID' => $this->cart_library->cartID,
															'accountID' => $this->cart_library->accountID
														);

														$res = $this->db->update('bookings_cart', $data, $where, 1);
													}

													if (!$this->in_crm) {
														//Validate profile details after booking is made
														$validation = $this->online_booking->validate_profile_details("existing");

														if ($validation!==true) {
															$this->session->set_flashdata('errors', $validation);
															$cart_success_redirect = site_url("account/profile");
														}
														else {
															$cart_success_redirect = site_url('account');
															$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
														}
													} else {
														$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
														$cart_success_message = "Booking successful. You can find the details below.";
													}
													$this->session->set_flashdata('success', $cart_success_message);
												}
											}
										}
									}
									if(!empty($subscriptions[$sub->stripe_price_id]->contactID)){
										$contactIDs = explode(",",$subscriptions[$sub->stripe_price_id]->contactID);
										foreach($contactIDs as $contactId){
											if(empty($contactId)){
												continue;
											}
											$where = array(
												'status' => 'active',
												'accountID' => $this->cart_library->accountID,
												'contactID' => $contactId,
												'subID' => $sub->subID
											);

											$res = $this->db->from('participant_subscriptions')->where($where)->get();
											if ($res->num_rows() > 0) {
												continue;
											}

											$data = array(
												'contactID' => $contactId,
												'accountID' => $this->cart_library->accountID,
												'subID' => $sub->subID,
												'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
												'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete')?'inactive':$subscriptions[$sub->stripe_price_id]->status,
												'modified' => mdate('%Y-%m-%d %H:%i:%s'),
												'added' => mdate('%Y-%m-%d %H:%i:%s')
											);

											$res = $this->db->insert('participant_subscriptions', $data);

											//send email and covert to booking
											if ($this->db->insert_id()) {
												if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
													if(is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
														$data = array(
															'type' => 'booking',
															'modified' => mdate('%Y-%m-%d %H:%i:%s'),
															'booked' => mdate('%Y-%m-%d %H:%i:%s')
														);
														$where = array(
															'cartID' => $this->cart_library->cartID,
															'accountID' => $this->cart_library->accountID
														);

														$res = $this->db->update('bookings_cart', $data, $where, 1);
													}

													if (!$this->in_crm) {
														//Validate profile details after booking is made
														$validation = $this->online_booking->validate_profile_details("existing");

														if ($validation!==true) {
															$this->session->set_flashdata('errors', $validation);
															$cart_success_redirect = site_url("account/profile");
															$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
														}
														else {
															$cart_success_redirect = site_url('account');
															$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
														}
													} else {
														$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
														$cart_success_message = "Booking successful. You can find the details below.";
													}
													$this->session->set_flashdata('success', $cart_success_message);
												}
											}
										}
									}
								}else {

									$where = array(
										'status' => 'active',
										'accountID' => $this->cart_library->accountID,
										$participants_id_field => $participantID,
										'subID' => $sub->subID
									);

									$res = $this->db->from('participant_subscriptions')->where($where)->get();
									if ($res->num_rows() > 0) {
										continue;
									}

									$data = array(
										$participants_id_field => $participantID,
										'accountID' => $this->cart_library->accountID,
										'subID' => $sub->subID,
										'stripe_subscription_id' => $subscriptions[$sub->stripe_price_id]->strip_sub_id,
										'status' => ($subscriptions[$sub->stripe_price_id]->status === 'incomplete') ? 'inactive' : $subscriptions[$sub->stripe_price_id]->status,
										'modified' => mdate('%Y-%m-%d %H:%i:%s'),
										'added' => mdate('%Y-%m-%d %H:%i:%s')
									);

									$res = $this->db->insert('participant_subscriptions', $data);

									//send email and covert to booking
									if ($this->db->insert_id()) {
										if ($this->gocardless_library->send_confirm_email($sub->subID, $this->cart_library->contactID)) {
											if (is_array($sub_payment_provider) && !in_array('gocardless', $sub_payment_provider)) {
												$data = array(
													'type' => 'booking',
													'modified' => mdate('%Y-%m-%d %H:%i:%s'),
													'booked' => mdate('%Y-%m-%d %H:%i:%s')
												);
												$where = array(
													'cartID' => $this->cart_library->cartID,
													'accountID' => $this->cart_library->accountID
												);

												$res = $this->db->update('bookings_cart', $data, $where, 1);
											}

											if (!$this->in_crm) {
												//Validate profile details after booking is made
												$validation = $this->online_booking->validate_profile_details("existing");

												if ($validation!==true) {
													$this->session->set_flashdata('errors', $validation);
													$cart_success_redirect = site_url("account/profile");
													$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
												}
												else {
													$cart_success_redirect = site_url('account');
													$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
												}
											} else {
												$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
												$cart_success_message = "Booking successful. You can find the details below.";
											}
											$this->session->set_flashdata('success', $cart_success_message);
										}
									}
								}
							}
						}
						if(is_array($sub_payment_provider) && in_array('gocardless', $sub_payment_provider)) {

							$mandate_link = $this->gocardless_library->new_subscription($this->cart_library->contactID);
							if ($mandate_link !== FALSE) {
								redirect($mandate_link);
							} else {
								$sub_success = $this->gocardless_library->start_subscription($this->cart_library->cartID, $this->cart_library->contactID);

								$data = array(
									'type' => 'booking',
									'modified' => mdate('%Y-%m-%d %H:%i:%s'),
									'booked' => mdate('%Y-%m-%d %H:%i:%s')
								);
								$cartID = $this->cart_library->cartID;
								$where = array(
									'cartID' => $cartID,
									'accountID' => $this->cart_library->accountID
								);
								$res = $this->db->update('bookings_cart', $data, $where, 1);

								// calc family balance
								$this->crm_library->recalc_family_balance($this->cart_library->familyID);

								if ($sub_success) {
									if (!$this->in_crm) {
										//Validate profile details after booking is made
										$validation = $this->online_booking->validate_profile_details("existing");

										if ($validation!==true) {
											$this->session->set_flashdata('errors', $validation);
											$cart_success_redirect = site_url("account/profile");
											$cart_success_message = "Thank you for your booking. You can find the booking details in your account, we will send you a confirmation email with this information";
										}
										else {
											$cart_success_redirect = site_url('account');
											$cart_success_message = "Thank you for your booking. You can find your details below and we will send you a confirmation email with this information";
										}
									} else {
										$cart_success_redirect = site_url('families/bookings/' . $this->cart_library->familyID);
										$cart_success_message = "Booking successful. You can find the details below.";
									}
									$this->session->set_flashdata('success', $cart_success_message);
								}
							}
						}
						// redirect
						redirect($cart_success_redirect);
					}
				}
			}
		} else {
			// validate cart
			$this->cart_library->validate_cart();
			$errors = $this->cart_library->get_errors();

			// refresh cart summary
			$cart_summary = $this->cart_library->get_cart_summary();
		}

		// check for flashdata
		if ($this->session->flashdata('success')) {
			$success = $this->session->flashdata('success');
		}
		if ($this->session->flashdata('info')) {
			$info = $this->session->flashdata('info');
		}
		if ($this->session->flashdata('error')) {
			$error = $this->session->flashdata('error');
		}

		// output
		$data = array(
			'title' => $title,
			'body_class' => $body_class,
			'success' => $success,
			'error' => $error,
			'errors' => $errors,
			'info' => $info,
			'blocks' => $blocks,
			'cart_summary' => $cart_summary,
			'childcarevoucher_providers' => $childcarevoucher_providers,
			'childcarevoucher_provider_notices' => $childcarevoucher_provider_notices,
			'terms' => $terms,
			'min_payment' => $min_payment,
			'max_payment' => $max_payment,
			'checkout' => $checkout,
			'payment_gateway' => $payment_gateway,
			'stripe_pk' => $stripe_pk,
			'stripe_sk' => $stripe_sk,
			'allow_payment_plan' => $allow_payment_plan,
			'available_payment_plans' => $available_payment_plans,
			'only_subscriptions' => $only_subscriptions,
			'includes_subscriptions' => $includes_subscriptions,
			'sub_payment_provider' => $sub_payment_provider,
			'sub_payment_amount' => $sub_payment_amount,
			'stripe_price_id' => $stripe_price_id,
			'in_crm' => $this->in_crm,
			'cart_base' => $this->cart_base,
			'fa_weight' => $this->fa_weight,
			'buttons' => $this->buttons,
			'icon' => $icon,
			'breadcrumb_levels' => $breadcrumb_levels
		);
		if ($prevent_checkout === TRUE) {
			$view = 'online-booking/prevented';
		}
		if ($this->in_crm) {
			$this->crm_view($view, $data);
		} else {
			$this->booking_view($view, $data);
		}
	}

	public function remove($blockID) {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// remove block
		if ($this->cart_library->remove_block($blockID)) {
			$success = 'Your booking cart has been updated successfully';
			$this->session->set_flashdata('success', $success);
		} else {
			$info = 'Your booking cart could not be updated';
			$this->session->set_flashdata('info', $info);
		}
		redirect($this->cart_base . 'cart');
		return;
	}

	public function remove_subscription($userID, $bookingID) {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// remove block
		if ($this->cart_library->remove_user_subscription($userID, $bookingID)) {
			$success = 'Your booking cart has been updated successfully';
			$this->session->set_flashdata('success', $success);
		} else {
			$info = 'Your booking cart could not be updated';
			$this->session->set_flashdata('info', $info);
		}
		redirect($this->cart_base . 'cart');
		return;
	}

	public function empty() {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		// empty cart
		if ($this->cart_library->clear()) {
			$success = 'Your booking cart has been emptied successfully';
			$this->session->set_flashdata('success', $success);
		} else {
			$info = 'Your booking cart could not be updated';
			$this->session->set_flashdata('info', $info);
		}
		redirect($this->cart_base . 'cart');
	}

	public function removevoucher($id) {
		// if in crm
		if ($this->in_crm === TRUE) {
			// check for contact cart
			if (!$this->crm_library->get_contact_cart()) {
				return $this->prevented();
			}
		} else {
			// check auth
			$this->online_booking->require_auth();
		}

		$res = $this->cart_library->remove_voucher($id);
		if ($res === TRUE) {
			$success = 'Voucher removed successfully';
			$this->session->set_flashdata('success', $success);
		}

		// redirect
		$redirect_to = 'cart';
		$this->load->library('user_agent');
		if (stripos($this->agent->referrer(), 'checkout') !== FALSE) {
			$redirect_to = 'checkout';
		}
		redirect($this->cart_base . $redirect_to);
	}

	// validate childcarevoucher
	public function check_childcarevoucher_provider($providerID) {

		if (empty($providerID)) {
			return TRUE;
		}

		$childcarevoucher_providers = $this->cart_library->get_childcarevoucher_providers();

		if ($childcarevoucher_providers === FALSE) {
			return FALSE;
		}

		if (!array_key_exists($providerID, $childcarevoucher_providers)) {
			return FALSe;
		}

		return TRUE;
	}

	/**
	 * check if a field is checked
	 * @param  string $value
	 * @return boolean
	 */
	public function is_checked($value) {

		if (empty($value)) {
			return FALSE;
		}

		return TRUE;
	}

	/**
	 * validation function for check a dob is valid and in past
	 * @param  string $date
	 * @return bool
	 */
	public function check_dob($date) {

		$this->crm_library->check_dob($date);
	}

	/**
	 * check mobile number is valid
	 * @param  string $number
	 * @return mixed
	 */
	public function check_mobile($number = NULL) {
		$this->crm_library->check_mobile($number, $this->cart_library->accountID);
	}

	/**
	 * check if either this or another field is filled in
	 * @param  string $value
	 * @param  string $value2
	 * @return boolean
	 */
	public function phone_or_mobile($value, $value2) {

		$this->crm_library->phone_or_mobile($value, $value2);

	}


	/**
	 * format postcode and check is correct
	 * @param  string $postcode
	 * @return mixed
	 */
	public function check_postcode($postcode) {

		$this->crm_library->check_postcode($postcode, $this->cart_library->accountID);

	}
}
