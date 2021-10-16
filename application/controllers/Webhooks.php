<?php

use function GuzzleHttp\json_decode;

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webhooks extends MY_Controller {

	public function __construct() {
		// allow public access
		parent::__construct(TRUE);
	}

	/**
	 * unsubscribe user from newsletter if unsubscribed at mail chimp
	 * @return void
	 */
	public function mailchimp() {

		// get vars
		$type = $this->input->post('type');
		$data = $this->input->post('data');

		// validate
		if ($type == 'unsubscribe' && is_array($data) && array_key_exists('email', $data) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) && array_key_exists('list_id', $data)) {

			// family contact
			echo 'FAMILY-';
			$where = array(
				'family_contacts.email' => $data['email'],
				'brands.mailchimp_id' => $data['list_id']
			);

			$res = $this->db->select('family_contacts_newsletters.id')
				->from('family_contacts')
				->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'inner')
				->join('brands', 'family_contacts_newsletters.brandID = brands.brandID', 'inner')
				->where($where)
				->limit(1)
				->get();

			if ($res->num_rows() == 0) {
				// check if main list
				$where = array(
					'family_contacts.email' => $data['email'],
					'family_contacts.marketing_consent' => 1,
					'accounts_settings.key' => 'mailchimp_audience_id',
					'accounts_settings.value' => $data['list_id']
				);

				$res = $this->db->select('family_contacts.*, accounts.company, accounts.booking_customdomain, accounts.booking_subdomain')
					->from('family_contacts')
					->join('accounts_settings', 'family_contacts.accountID = accounts_settings.accountID', 'inner')
					->join('accounts', 'family_contacts.accountID = accounts.accountID', 'inner')
					->where($where)
					->limit(1)
					->get();

				if ($res->num_rows() == 0) {
					echo 'NOTFOUND<br />';
				} else {
					foreach ($res->result_array() as $contact_data) {
						$where = array(
							'contactID' => $contact_data['contactID']
						);
					}

					$update_data = [
						'marketing_consent' => 0,
						'marketing_consent_date' => mdate('%Y-%m-%d %H:%i:%s')
					];

					$res = $this->db->update('family_contacts',  $update_data, $where, 1);

					if ($this->db->affected_rows() > 0) {
						// insert note
						$details = 'Contact: ' . $contact_data['first_name'] . ' ' . $contact_data['last_name'] . '
						By: Mailchimp
						IP: ' . get_ip_address() . '
						Hostname: ' . gethostbyaddr(get_ip_address());
						$summary = 'Marketing Consent: ';
						if ($contact_data['marketing_consent'] == 1) {
							$summary .= 'Yes';
						} else {
							$summary .= 'No';
						}
						$summary .= ', Privacy Agreed: ';
						if ($contact_data['privacy_agreed'] == 1) {
							$summary .= 'Yes';
						} else {
							$summary .= 'No';
						}
						$summary .= ', Source: ';
						if (strtolower($contact_data['source']) == 'other' && !empty($contact_data['source_other'])) {
							$summary .= $contact_data['source_other'];
						} else if (!empty($contact_data['source'])) {
							$summary .= $contact_data['source'];
						} else {
							$summary .= 'Unknown';
						}
						$insert_data = array(
							'type' => 'privacy',
							'summary' => $summary,
							'content' => $details,
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'familyID' => $contact_data['familyID'],
							'accountID' => $contact_data['accountID']
						);
						$res = $this->db->insert('family_notes', $insert_data);

						// set message
						$subject = $this->settings_library->get('participant_consent_changed_subject', $contact_data['accountID']);
						$message = $this->settings_library->get('participant_consent_changed', $contact_data['accountID']);

						// get booking site link
						$booking_link = null;
						if (!empty($contact_data['booking_customdomain'])) {
							$booking_link = PROTOCOL . '://' . $contact_data['booking_customdomain'];
						} else if (!empty($contact_data['booking_subdomain'])) {
							$booking_link = PROTOCOL . '://' . $contact_data['booking_subdomain'] . '.' . ROOT_DOMAIN;
						}

						// set tags
						$smart_tags = array(
							'first_name' => $contact_data['first_name'],
							'changed_by' => $contact_data['first_name'] . ' ' . $contact_data['last_name'],
							'changed_at' => date('d/m/Y H:i'),
							'company' => $contact_data['company'],
							'link' => $booking_link
						);

						// replace
						foreach ($smart_tags as $key => $value) {
							$message = str_replace('{' . $key . '}', $value, $message);
							$subject = str_replace('{' . $key . '}', $value, $subject);
						}

						// send
						$this->crm_library->send_email($contact_data['email'], $subject, $message, array(), FALSE, $contact_data['accountID']);

						echo 'OK<br />';
					} else {
						echo 'NOTFOUND<br />';
					}
				}
			} else {
				foreach ($res->result() as $row) {
					$where = array(
						'id' => $row->id
					);
				}

				$res = $this->db->delete('family_contacts_newsletters', $where, 1);

				if ($this->db->affected_rows() > 0) {
					echo 'OK<br />';
				} else {
					echo 'NOTFOUND<br />';
				}
			}

			// org contact
			echo 'ORG-';
			$where = array(
				'orgs_contacts.email' => $data['email'],
				'brands.mailchimp_id' => $data['list_id']
			);

			$res = $this->db->select('orgs_contacts_newsletters.id')->from('orgs_contacts')->join('orgs_contacts_newsletters', 'orgs_contacts.contactID = orgs_contacts_newsletters.contactID', 'inner')->join('brands', 'orgs_contacts_newsletters.brandID = brands.brandID', 'inner')->where($where)->limit(1)->get();

			if ($res->num_rows() == 0) {
				echo 'NOTFOUND<br />';
			} else {
				foreach ($res->result() as $row) {
					$where = array(
						'id' => $row->id
					);
				}

				$res = $this->db->delete('orgs_contacts_newsletters', $where, 1);

				if ($this->db->affected_rows() > 0) {
					echo 'OK<br />';
				} else {
					echo 'NOTFOUND<br />';
				}
			}

		} else {
			echo "INVALIDPARAMS";
		}

		return TRUE;
	}

	/**
	 * process sms delivery report
	 * @return void
	 */
	public function smsreport() {

		// load config
		$this->config->load('textlocal', TRUE);

		// get vars
		$number = $this->input->post('number');
		$status = $this->input->post('status');
		$customID = $this->input->post('customID');

		// validate
		if (!empty($number) && !empty($status) && !empty($customID)) {

			// switch status
			switch ($status) {
				case 'D':
					$status = "delivered";
					break;
				case 'U':
					$status = "undelivered";
					break;
				case 'I':
					$status = "invalid";
					break;
				case '?':
				default:
					$status = "unknown";
					break;
			}

			$where = array(
				'notificationID' => $customID,
				'destination' => $number
			);

			$data = array(
				'status' => $status,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$res = $this->db->update('family_notifications', $data, $where, 1);

			if ($this->db->affected_rows() > 0) {
				echo 'OK';
			} else {
				echo 'NOTFOUND';
			}

		} else {
			echo "INVALIDPARAMS";
		}

		return TRUE;
	}

	/**
	 * handle gocardless callbacks
	 * @return mixed
	 */
	public function gocardless($accountID) {

		// load library
		$params = array(
			'accountID' => $accountID
		);
		$this->load->library('gocardless_library', $params);

		return $this->gocardless_library->handle_webhook();
	}

	public function stripe($accountID) {
		\Stripe\Stripe::setApiKey($this->settings_library->get('stripe_sk', $accountID));
		$endpoint_secret = $this->settings_library->get('stripe_whs', $accountID);

		$payload = file_get_contents('php://input');
		$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

		$event = null;

		try {
			$event = \Stripe\Webhook::constructEvent(
				$payload, $sig_header, $endpoint_secret
			);
		} catch(\UnexpectedValueException $e) {
			http_response_code(400);
			exit();
		} catch(\Stripe\Exception\SignatureVerificationException $e) {
			// Invalid signature
			http_response_code(400);
			exit();
		}

		switch($event->type) {
			case 'invoice.paid':
				$invoice = $event->data->object;
				if(isset($invoice->subscription)) {
					$subscription_metadata = \Stripe\Subscription::retrieve(
						$invoice->subscription,
						[]
					);

					if(isset($subscription_metadata->metadata->cartID)){

						//For new customers
						//Fetch cart detail directly

						$where = array(
							'bookings_cart.cartID' => $subscription_metadata->metadata->cartID
						);
						$res = $this->db
							->select('bookings_cart.familyID, bookings_cart.contactID,bookings_cart.accountID')
							->from('bookings_cart')
							->where($where)
							->get();

						if ($res->num_rows() > 0) {
							foreach ($res->result() as $cart_info) {
							}
							// build data
							$db_data = array(
								'contactID' => $cart_info->contactID,
								'method' => 'online',
								'transaction_ref' => $invoice->lines->data[0]->subscription . '-' . $invoice->id,
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'note' => 'Direct Debit'
							);

							if ($invoice->billing_reason === "subscription_create") {
								$db_data['is_first_payment'] = '2';
							} else {
								$db_data['is_first_payment'] = '0';
							}

							$db_data['familyID'] = $cart_info->familyID;
							$db_data['accountID'] = $cart_info->accountID;

							$db_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
							$db_data['amount'] = $invoice->amount_paid / 100;
							$this->db->insert('family_payments', $db_data);
							$paymentID = $this->db->insert_id();

							// recalc balance
							$this->crm_library->recalc_family_balance($cart_info->familyID);

							//Add data to family payment sessions
							$res = $this->db
								->select('bookings_cart_sessions.sessionID')
								->from('bookings_cart')
								->join('bookings_cart_sessions', "bookings_cart.cartID = bookings_cart_sessions.cartID", "inner")
								->where($where)
								->get();
							$session_data = array();
							if ($res->num_rows() > 0) {
								$cnt = 0;
								foreach($res->result() as $session_info){
									$session_data[] = array(
										'accountID' => $cart_info->accountID,
										'familyID' => $cart_info->familyID,
										'paymentID' => $paymentID,
										'sessionID' => $session_info->sessionID,
										'is_sub' => '1',
										'amount' => ($cnt == 0)?($invoice->amount_paid / 100):0
									);
									$cnt++;
									
								}
								if(count($session_data) > 0){
									$this->db->insert_batch('family_payments_sessions', $session_data);
								}
							}

							// send confirmation
							$this->crm_library->send_payment_confirmation($paymentID);
							http_response_code(200);
							exit();
						} else {
							http_response_code(400);
							exit();
						}

					}else {
						//For old customers

						//Find customer using customer id
						$where = array(
							'stripe_customer_id' => $invoice->customer
						);

						$res = $this->db->from('family_contacts')->where($where)->limit(1)->get();

						foreach ($res->result() as $contact_info) break;

						// look up subscription
						$where = array(
							'participant_subscriptions.stripe_subscription_id' => $invoice->lines->data[0]->subscription,
							'participant_subscriptions.status' => 'active',
							'participant_subscriptions.accountID' => $accountID
						);

						$res = $this->db
							->select('participant_subscriptions.*, subscriptions.*')
							->from('participant_subscriptions')
							->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID', 'inner')
							->where($where)
							->limit(1)
							->get();

						if ($res->num_rows() > 0) {
							foreach ($res->result() as $sub_info) {
							}

							// Implemented because sometimes webhook is faster then our process
							$where_check = array(
								'accountID' => $sub_info->accountID,
								'familyID' => $contact_info->familyID,
								'contactID' => $contact_info->contactID
							);

							//update payment date
							$data = array(
								'last_payment_date' => mdate('%Y-%m-%d %H:%i:%s', strtotime($invoice->created))
							);

							$this->db->update('participant_subscriptions', $data, $where, 1);

							// build data
							$db_data = array(
								'contactID' => $contact_info->contactID,
								'method' => 'online',
								'transaction_ref' => $invoice->lines->data[0]->subscription . '-' . $invoice->id,
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'note' => 'Direct Debit'
							);

							if ($invoice->billing_reason === "subscription_create") {
								$db_data['is_first_payment'] = '1';
							} else {
								$db_data['is_first_payment'] = '0';
							}

							$db_data['familyID'] = $contact_info->familyID;
							$db_data['accountID'] = $sub_info->accountID;

							$db_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
							$db_data['amount'] = $invoice->amount_paid / 100;
							$this->db->insert('family_payments', $db_data);
							$paymentID = $this->db->insert_id();

							// recalc balance
							$this->crm_library->recalc_family_balance($contact_info->familyID);

							//Add data to family_payment_sessions
							$cart_contact_field = 'bookings_cart_subscriptions.contactID';
							$contact_id = $sub_info->contactID;
							if(!empty($sub_info->childID)){
								$cart_contact_field = 'bookings_cart_subscriptions.childID';
								$contact_id = $sub_info->childID;
							}
							$where_session = array(
								$cart_contact_field => $contact_id,
								'bookings_cart_subscriptions.subID' => $sub_info->subID,
								'bookings_cart_subscriptions.accountID' => $accountID
							);
							$res = $this->db
								->select('bookings_cart_subscriptions.cartID')
								->from('bookings_cart_subscriptions')
								->where($where_session)
								->order_by('bookings_cart_subscriptions.added desc')
								->limit(1)
								->get();
							if($res->num_rows() > 0){
								foreach($res->result() as $cart){
									break;
								}
								$where_session = array(
									'cartID' => $cart->cartID,
									'accountID' => $accountID
								);
								$res = $this->db
									->select('bookings_cart_sessions.sessionID')
									->from('bookings_cart_sessions')
									->where($where_session)
									->get();

								$session_data = array();
								if ($res->num_rows() > 0) {
									
									foreach($res->result() as $session_info){
										$session_data[] = array(
											'accountID' => $accountID,
											'familyID' => $contact_info->familyID,
											'paymentID' => $paymentID,
											'sessionID' => $session_info->sessionID,
											'is_sub' => '1',
											'amount' => ($cnt == 0)?($invoice->amount_paid / 100):0
										);
										$cnt++;
									}
									if(count($session_data) > 0){
										$this->db->insert_batch('family_payments_sessions', $session_data);
									}
								}
							}

							// send confirmation
							$this->crm_library->send_payment_confirmation($paymentID);
							http_response_code(200);
							exit();
						} else {
							http_response_code(400);
							exit();
						}
					}
				}
				break;
			case 'invoice.payment_failed':
				$invoice = $event->data->object;
				break;
			case 'customer.subscription.deleted':
				$customer_object = $event->data->object;
				if(isset($customer_object) && $customer_object->object === "subscription"){
					//Find customer using customer id
					$where = array(
						'stripe_customer_id' => $customer_object->customer
					);

					$res = $this->db->from('family_contacts')->where($where)->get();

					$contacts = array();$familyID = '';
					foreach ($res->result() as $contact_info){
						$contacts[] = "'".$contact_info->contactID."'";
						$familyID = $contact_info->familyID;
					}

					$res = $this->db->from('family_children')->where('familyID', $familyID)->get();

					$children = array();
					foreach ($res->result() as $child_info) {
						$children[] = "'".$child_info->childID."'";
					}

					// look up subscription
					$where = array(
						'participant_subscriptions.stripe_subscription_id' => $customer_object->items->data[0]->subscription,
						'participant_subscriptions.status' => 'active'
					);

					$query = 'SELECT '.$this->db->dbprefix('participant_subscriptions').'.*, '.$this->db->dbprefix('subscriptions').'.*
					FROM '.$this->db->dbprefix('participant_subscriptions').'
					INNER JOIN '.$this->db->dbprefix('subscriptions').' ON '.$this->db->dbprefix('subscriptions').'.subID = '.$this->db->dbprefix('subscriptions').'.subID
					WHERE '.$this->db->dbprefix('participant_subscriptions').'.stripe_subscription_id = "'.$customer_object->items->data[0]->subscription.'"
					AND ('.$this->db->dbprefix('participant_subscriptions').'.status = "active" OR '.$this->db->dbprefix('participant_subscriptions').'.status = "inactive")
					AND ('.$this->db->dbprefix('participant_subscriptions').'.contactID IN ('.implode(",", $contacts).')
					OR '.$this->db->dbprefix('participant_subscriptions').'.childID IN('.implode(",", $children).'))';
					$res = $this->db->query($query);

					if ($res->num_rows() > 0) {
						$counter = 0;
						foreach ($res->result() as $sub_info) {
							if($customer_object->quantity != $counter){
								$where_clause = array(
									'id' => $sub_info->id
								);
								$update_data['status'] = 'cancelled';
								$res = $this->db->update('participant_subscriptions',  $update_data, $where_clause, 1);
							}
							$counter++;
						}
						http_response_code(200);
						exit();
					}else{
						http_response_code(400);
						exit();
					}
				}else{
					http_response_code(400);
					exit();
				}
				break;
			default:
				// Unexpected event type
				http_response_code(400);
				exit();
		}

	}
}

/* End of file webhooks.php */
/* Location: ./application/controllers/webhooks.php */
