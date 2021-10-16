<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Gocardless_library {

	private $CI;
	private $client;
	private $accountID;
	private $account_info;
	private $debug = TRUE;

	public function __construct($params = array()) {
		// get CI instance
		$this->CI =& get_instance();

		if (isset($params['accountID'])) {
			$this->accountID = $params['accountID'];
		} else if (isset($this->CI->auth->user->accountID)) {
			$this->accountID = $this->CI->auth->user->accountID;
		} else {
			return;
		}

		// check valid config
		if ($this->valid_config() !== TRUE) {
			return FALSE;
		}

		// look up account
		$where = array(
			'accountID' => $this->accountID,
		);

		$res = $this->CI->db->from('accounts')->where($where)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $this->account_info) {}

		try {

			// get access token
			$access_token = $this->CI->settings_library->get('gocardless_access_token', $this->accountID);

			// set environment
			switch ($this->CI->settings_library->get('gocardless_environment', $this->accountID)) {
				case 'sandbox':
				default:
					$environment = \GoCardlessPro\Environment::SANDBOX;
					break;
				case 'production':
					$environment = \GoCardlessPro\Environment::LIVE;
					break;
			}
			$this->client = new \GoCardlessPro\Client(array(
				'access_token' => $access_token,
				'environment'  => $environment
			));

		} catch (Exception $e) {
			show_error('Could not connect to GoCardless', 500);
			return FALSE;
		}
	}


	public function new_subscription($contactID) {
		// look up contact and ensure doesn't have existing mandate
		$where = array(
			'family_contacts.contactID' => $contactID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.gc_customer_id IS NULL' => NULL,
			'family_contacts.gc_mandate_id IS NULL' => NULL
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		} else {
			foreach ($res->result() as $contact_info) {}

			try {
				$params = [
					"params" => [
						// This will be shown on the payment pages
						"description" => '',
						// Not the access token
						"session_token" => session_id(),
						"success_redirect_url" => site_url('checkout'),
						// Optionally, prefill customer details on the payment page
						"prefilled_customer" => [
							"given_name" => $contact_info->first_name,
							"family_name" => $contact_info->last_name,
							"email" => $contact_info->email,
							"address_line1" => $contact_info->address1,
							"city" => $contact_info->town,
							"postal_code" => $contact_info->postcode
						]
					]
				];


				$redirect_flow = $this->client->redirectFlows()->create($params);

				// update contact with redirect id
				$data = array(
					'gc_redirect_id' => $redirect_flow->id,
					'modified' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$this->CI->db->update('family_contacts', $data, $where, 1);

				return $redirect_flow->redirect_url;

			} catch (Exception $e) {
				if ($this->debug) {
					show_error($e->getMessage(), 500);
				}
				return FALSE;
			}
		}
	}
	public function check_user_type($Id)
	{
		$where = array(
			'accountID' => $this->accountID,
			'contactID' => $Id
		);
		$res = $this->CI->db->select('contactID')->from('family_contacts')->where($where)->get();
		if ($res->num_rows() > 0) {
			return 'contactID';
		}
		return 'childID';
	}

	public function start_subscription($cartID, $contactID, $paid = FALSE) {
		$where = array (
			'contactID' => $contactID,
			'accountID' => $this->accountID,
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) break;

		$mandate_id = $contact_info->gc_mandate_id;

		$where = array (
			'bookings_cart_subscriptions.cartID' => $cartID,
			'bookings_cart_subscriptions.accountID' => $this->accountID
		);

		$bookings_register_type = $this->CI->db->select('register_type')
			->from('bookings')
			->join('bookings_cart_subscriptions', 'bookings.bookingID = bookings_cart_subscriptions.bookingID', 'inner')
			->where($where)
			->get();

		$participants_id_field = 'childID';

		$check_flag = FALSE;
		foreach($bookings_register_type->result() as $register_type) {
			if (strpos($register_type->register_type, 'individuals') === 0) {
				$participants_id_field = 'contactID';
			}
			if (strpos($register_type->register_type, 'adults_children') === 0) {
				$check_flag = TRUE;
			}
		}

		$where = array(
			'bookings_cart_subscriptions.cartID' => $cartID,
			'bookings_cart_subscriptions.accountID' => $this->accountID,
			'subscriptions.payment_provider' => 'gocardless'
		);

		$subs = $this->CI->db
			->select('subscriptions.subID, subscriptions.payment_provider, subscriptions.price, subscriptions.frequency, subscriptions.subName, bookings_cart_subscriptions.childID, bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.bookingID')
			->from('bookings_cart_subscriptions')
			->join('subscriptions', '(' . $this->CI->db->dbprefix('bookings_cart_subscriptions') . '.subID = subscriptions.subID AND bookings_cart_subscriptions.bookingID = ' . $this->CI->db->dbprefix('subscriptions') . '.bookingID)', 'inner')
			->where($where)
			->group_by('bookings_cart_subscriptions.contactID, bookings_cart_subscriptions.childID, subscriptions.subID')
			->get();
		foreach($subs->result() as $sub) {

			//check if has active subscription
			$participantID = $sub->$participants_id_field;
			if($check_flag){
				if(!empty($sub->childID) && $sub->childID !== "0")
				{
					$participantID = $sub->childID;
				}else{
					$participantID = $sub->contactID;
				}
				$participants_id_field= $this->check_user_type($participantID);
			}

			$where = array(
				'status' => 'active',
				'accountID' => $this->accountID,
				$participants_id_field => $participantID,
				'subID' => $sub->subID
			);

			$res = $this->CI->db->from('participant_subscriptions')->where($where)->get();
			if($res->num_rows() > 0) {
				continue;
			}

			$start_date = NULL;
			//if prepaid with credit card work out first payment date
			if($paid) {
				switch($sub->frequency) {
					case 'yearly':
						$start_date = date('Y-m-d', strtotime('+1 year') );
						break;
					case 'monthly':
						$start_date = date('Y-m-d', strtotime('+1 month'));
						break;
				}
			}

			try {
				$params = [
					"params" => [
						"name" => $sub->subName,
						"amount" => round($sub->price * 100),
						"currency" => currency_code($this->accountID),
						"interval_unit" => $sub->frequency,
						"start_date" => $start_date,
						"links" => [
							"mandate" => $mandate_id
						],
						"metadata" => [
							"subID" => $sub->subID
						]
					]
				];

				$subscription = $this->client->subscriptions()->create($params);

				$data = array(
					$participants_id_field => $participantID,
					'accountID' => $this->accountID,
					'subID' => $sub->subID,
					'gc_subscription_id' => $subscription->id,
					'status' => 'active',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'added' => mdate('%Y-%m-%d %H:%i:%s')
				);

				$res = $this->CI->db->insert('participant_subscriptions', $data);

				//send email
				if(is_string($subscription->id) && $this->CI->db->insert_id()) {
					$this->send_confirm_email($sub->subID, $contactID);
				}

			} catch (Exception $e) {
				if ($this->debug) {
					show_error($e->getMessage(), 500);
				}
				return FALSE;
			}
		}

		if($paid === FALSE) {
			// convert to booking
			$data = array(
				'type' => 'booking',
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'booked' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$where = array(
				'cartID' => $cartID,
				'accountID' => $this->accountID
			);

			$res = $this->CI->db->update('bookings_cart', $data, $where, 1);

			// calc family balance
			$this->CI->crm_library->recalc_family_balance($contact_info->familyID);

			// send booking email
			$this->CI->crm_library->send_event_confirmation($cartID);

			return TRUE;
		}

		return TRUE;
	}

	public function update_subscription($subID) {
		$where = array(
			'participant_subscriptions.subID' => $subID,
			'participant_subscriptions.accountID' => $this->accountID,
			'subscriptions.payment_provider' => 'gocardless'
		);

		$res = $this->CI->db->select('participant_subscriptions.gc_subscription_id, participant_subscriptions.childID, subscriptions.subName, subscriptions.price, subscriptions.bookingID, subscriptions.subID, subscriptions.frequency, subscriptions.no_of_sessions_per_week')
			->from('participant_subscriptions')
			->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID')
			->where($where)
			->get();

		if($res->num_rows() == 0) {
			return FALSE;
		}

		foreach($res->result() as $sub) {
			if($sub->gc_subscription_id != NULL) {
				try{
					$params = [
						"params" => [
							"name" => $sub->subName,
							"amount" => round($sub->price * 100),
						]
					];

					$this->client->subscriptions()->update($sub->gc_subscription_id, $params);
				} catch (Exception $e) {
					if ($this->debug) {
						show_error($e->getMessage(), 500);
					}
					return FALSE;
				}
			}

			//email each user of update
			$where = array (
				'family_children.childID' => $sub->childID,
				'family_contacts.accountID' => $this->accountID
			);

			$res = $this->CI->db->select('family_contacts.first_name, family_contacts.email, family_contacts.familyID, family_contacts.contactID')
				->from('family_contacts')
				->join('family_children', 'family_contacts.familyID = family_children.familyID')
				->where($where)->limit(1)->get();

			if($res->num_rows() == 0) {
				return FALSE;
			}

			foreach ($res->result() as $contact_info) break;

			//send email
			$smart_tags = array();
			$smart_tags['contact_first'] = $contact_info->first_name;

			//get company name
			$where = array(
				'accountID' => $this->accountID
			);
			$res = $this->CI->db->select('company')->from('accounts')->where($where)->limit(1)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// replace tags
					$smart_tags['company'] = $row->company;
				}
			}

			//get org name
			$where = array(
				'bookings.accountID' => $this->accountID,
				'bookings.bookingID' => $sub->bookingID
			);

			$res = $this->CI->db->select('orgs.name as org_name, bookings.name as bookings_name')
				->from('orgs')
				->join('bookings', 'orgs.orgID = bookings.orgID')
				->where($where)
				->limit(1)
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// replace tags
					$smart_tags['org_name'] = $row->org_name;
					$smart_tags['event_name'] = $row->bookings_name;
				}
			}

			//get session types
			$where = array(
				'subscriptions_lessons_types.subID' => $sub->subID,
				'subscriptions_lessons_types.accountID' => $this->accountID
			);


			/*$res = $this->CI->db->select('GROUP_CONCAT(' . $this->CI->db->dbprefix('lesson_types') .'.name SEPARATOR\', \') as types')
						->from('lesson_types')
						->join('subscriptions_lessons_types', 'lesson_types.typeID = subscriptions_lessons_types.typeID', 'inner')
						->where($where)
						->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $types) break;
			}*/

			$smart_tags['subscription_details'] =
				'Name: ' . $sub->subName . '<br>Frequency: ' . ucfirst($sub->frequency) . '<br>Rate: ' . currency_symbol($this->accountID) . $sub->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';

			$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

			// get email template
			$subject = $this->CI->settings_library->get('email_update_subscription_subject', $this->accountID);
			$email_html = $this->CI->settings_library->get('email_update_subscription', $this->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// send
			if ($this->CI->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->accountID)) {
				$byID = NULL;
				if (isset($this->auth->user->staffID)) {
					$byID = $this->auth->user->staffID;
				}

				// get html email and convert to plain text
				$this->CI->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				// save
				$data = array(
					'familyID' => $contact_info->familyID,
					'contactID' => $contact_info->contactID,
					'byID' => $byID,
					'type' => 'email',
					'destination' => $contact_info->email,
					'subject' => $subject,
					'contentHTML' => $email_html,
					'contentText' => $email_plain,
					'status' => 'sent',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $this->accountID
				);

				$this->CI->db->insert('family_notifications', $data);
			}
		}

		return TRUE;
	}

	public function new_payment_plan($planID, $email = TRUE) {

		// look up plan
		$where = array(
			'family_payments_plans.planID' => $planID,
			'family_payments_plans.accountID' => $this->accountID,
			'family_payments_plans.status' => 'inactive'
		);
		$res = $this->CI->db->select('family_payments_plans.*')
			->from('family_payments_plans')
			->where($where)
			->limit(1)
			->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $plan_info) {}

		// look up contact
		$where = array(
			'family_contacts.contactID' => $plan_info->contactID,
			'family_contacts.accountID' => $this->accountID
		);
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// if no mandate
		if (empty($contact_info->gc_mandate_id)) {
			// create random unique code for plan
			$plan_code = $this->generate_plan_code();

			// save code
			$where = array(
				'planID' => $planID,
				'accountID' => $this->accountID
			);
			$data = array(
				'gc_code' => $plan_code,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('family_payments_plans', $data, $where, 1);

			$mandate_link = site_url('gc/' . $plan_code);

			// if not emailing, return link
			if ($email !== TRUE) {
				return $mandate_link;
			}

			// else, send email to user
			$smart_tags = array(
				'contact_first' => $contact_info->first_name,
				'contact_last' => $contact_info->last_name,
				'mandate_link' => $mandate_link
			);

			// get email template
			$subject = $this->CI->settings_library->get('email_gocardless_mandate_subject', $this->accountID);
			$email_html = $this->CI->settings_library->get('email_gocardless_mandate', $this->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			unset($smart_tags['mandate_link']);
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send
			if ($this->CI->settings_library->get('send_gocardless_mandate', $this->accountID) == 1 && $this->CI->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->accountID)) {
				return 'mandate_sent';
			} else {
				return FALSE;
			}
		}

		// already has a mandate, start subscription
		return $this->start_payment_plan($planID);
	}

	/**
	 * Set up new subscrition from CRM
	 * @param $subID int
	 * @param $email boolean
	 * @return mixed
	 */
	public function new_subscription_crm($subID, $email = TRUE) {

		// look up subscription
		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->accountID,
			'participant_subscriptions.status' => 'inactive',
			'subscriptions.payment_provider' => 'gocardless'
		);

		$res = $this->CI->db->select('subscriptions.*')
			->from('subscriptions')
			->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID')
			->where($where)
			->limit(1)
			->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $sub_info) {}

		// look up contact
		$where = array(
			'family_contacts.familyID' => $sub_info->familyID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.main' => TRUE,
		);
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// if no mandate
		if (empty($contact_info->gc_mandate_id)) {
			// create random unique code for plan
			$sub_code = $this->generate_plan_code();

			// save code
			$where = array(
				'subID' => $subID,
				'accountID' => $this->accountID
			);
			$data = array(
				'gc_code' => $sub_code,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('participant_subscriptions', $data, $where, 1);

			$mandate_link = site_url('gc/' . $sub_code);

			// if not emailing, return link
			if ($email !== TRUE) {
				return $mandate_link;
			}

			// else, send email to user
			$smart_tags = array(
				'contact_first' => $contact_info->first_name,
				'contact_last' => $contact_info->last_name,
				'mandate_link' => $mandate_link
			);

			// get email template
			$subject = $this->CI->settings_library->get('email_gocardless_mandate_subject', $this->accountID);
			$email_html = $this->CI->settings_library->get('email_gocardless_mandate', $this->accountID);

			// replace smart tags in email
			foreach ($smart_tags as $key => $value) {
				$email_html = str_replace('{' . $key . '}', $value, $email_html);
			}

			// replace smart tags in subject
			unset($smart_tags['mandate_link']);
			foreach ($smart_tags as $key => $value) {
				$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
			}

			// send
			if ($this->CI->settings_library->get('send_gocardless_mandate', $this->accountID) == 1 && $this->CI->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->accountID)) {
				$byID = NULL;
				if (isset($this->CI->auth->user->staffID)) {
					$byID = $this->CI->auth->user->staffID;
				}

				// get html email and convert to plain text
				$this->CI->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				// save
				$data = array(
					'familyID' => $sub_info->familyID,
					'contactID' => $contact_info->contactID,
					'byID' => $byID,
					'type' => 'email',
					'destination' => $contact_info->email,
					'subject' => $subject,
					'contentHTML' => $email_html,
					'contentText' => $email_plain,
					'status' => 'sent',
					'added' => mdate('%Y-%m-%d %H:%i:%s'),
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'accountID' => $sub_info->accountID
				);

				$this->CI->db->insert('family_notifications', $data);

				return 'mandate_sent';
			} else {
				return FALSE;
			}
		}

		// already has a mandate, start subscription
		return $this->start_subscription_crm($subID);
	}

	/**
	 * Start the subscrtiption
	 * @param $subID
	 * @return boolean
	 */
	public function start_subscription_crm($subID) {
		// look up subscription
		$where = array(
			'subscriptions.subID' => $subID,
			'subscriptions.accountID' => $this->accountID,
			'participant_subscriptions.status' => 'inactive',
			'subscriptions.payment_provider' => 'gocardless'
		);

		$res = $this->CI->db->select('subscriptions.*')
			->from('subscriptions')
			->join('participant_subscriptions', 'subscriptions.subID = participant_subscriptions.subID')
			->where($where)
			->limit(1)
			->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $sub_info) {}

		// look up contact
		$where = array(
			'family_contacts.familyID' => $sub_info->familyID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.main' => TRUE,
		);
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		$count = '';

		if($sub_info->endDate !== NULL) {
			switch($sub_info->frequency) {
				case 'monthly':
					$start_date = date_create();
					$end_date = date_create($sub_info->endDate);
					$interval = date_diff($start_date, $end_date);
					$days_count = $interval->format('%a');
					$count = round($days_count / 30.417);
					break;
				case 'weekly':
					$start_date = date_create();
					$end_date = date_create($sub_info->endDate);
					$interval = date_diff($start_date, $end_date);
					$days_count = $interval->format('%a');
					$count = round($days_count / 7);
					break;
			}
		}

		if($count < 1) {
			$count = '';
		}

		try {
			$params = [
				"params" => [
					"amount" => round($sub_info->price * 100),
					"currency" => currency_code($this->accountID),
					"name" => $sub_info->subName,
					"interval_unit" => $sub_info->frequency,
					"count" => $count,
					"metadata" => [
						"subID" => trim($subID)
					],
					"links" => [
						"mandate" => $contact_info->gc_mandate_id
					]
				]
			];

			$subscription = $this->client->subscriptions()->create($params);

			// update contact with subscription id
			$where = array(
				'subID' => $subID,
				'accountID' => $this->accountID
			);
			$data = array(
				'status' => 'active',
				'gc_subscription_id' => $subscription->id,
				'gc_code' => NULL,
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
			);
			$this->CI->db->update('participant_subscriptions', $data, $where, 1);

			$affected_rows = $this->CI->db->affected_rows();

			//send email
			if(is_string($subscription->id) && $affected_rows == 1) {
				$this->send_confirm_email($sub_info->subID, $contact_info->contactID);
			}

		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}

		return TRUE;
	}

	public function start_payment_plan($planID) {

		// look up plan
		$where = array(
			'family_payments_plans.planID' => $planID,
			'family_payments_plans.accountID' => $this->accountID,
			'family_payments_plans.status' => 'inactive'
		);
		$res = $this->CI->db->select('family_payments_plans.*')
			->from('family_payments_plans')
			->where($where)
			->limit(1)
			->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $plan_info) {}

		// look up contact
		$where = array(
			'family_contacts.contactID' => $plan_info->contactID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.gc_mandate_id IS NOT NULL' => NULL
		);
		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		// work out interval amount, by dividing total amount / number of payments
		$interval_amount = round($plan_info->amount/$plan_info->interval_count, 2);

		try {
			$params = [
				"params" => [
					"amount" => round($interval_amount*100),
					"currency" => currency_code($this->accountID),
					"name" => 'Payment Plan',
					"interval_unit" => $plan_info->interval_unit . 'ly',
					"count" => $plan_info->interval_count,
					"metadata" => [
						"planID" => trim($planID)
					],
					"links" => [
						"mandate" => $contact_info->gc_mandate_id
					]
				]
			];

			$subscription = $this->client->subscriptions()->create($params);

			// update contact with subscription id
			$where = array(
				'planID' => $planID,
				'accountID' => $this->accountID
			);
			$data = array(
				'status' => 'active',
				'gc_subscription_id' => $subscription->id,
				'gc_code' => NULL,
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'authorised' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('family_payments_plans', $data, $where, 1);

			// if from cart
			if (!empty($plan_info->cartID)) {
				// all ok
				// convert to booking
				$data = array(
					'type' => 'booking',
					'modified' => mdate('%Y-%m-%d %H:%i:%s'),
					'booked' => mdate('%Y-%m-%d %H:%i:%s')
				);
				$where = array(
					'cartID' => $plan_info->cartID,
					'accountID' => $this->accountID
				);
				$res = $this->CI->db->update('bookings_cart', $data, $where, 1);

				// calc family balance
				$this->CI->crm_library->recalc_family_balance($plan_info->familyID);

				// send booking email
				$this->CI->crm_library->send_event_confirmation($plan_info->cartID);
			}

			// email user
			if ($this->CI->settings_library->get('send_gocardless_subscription', $plan_info->accountID) == 1) {
				$smart_tags = array(
					'contact_first' => $contact_info->first_name,
					'contact_last' => $contact_info->last_name,
					'details' => $plan_info->interval_count . ' ' . $plan_info->interval_unit . 'ly payments of ' . currency_symbol($this->accountID) . number_format($interval_amount, 2)
				);

				// get email template
				$subject = $this->CI->settings_library->get('email_gocardless_subscription_subject', $plan_info->accountID);
				$email_html = $this->CI->settings_library->get('email_gocardless_subscription', $plan_info->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				// replace smart tags in subject
				unset($smart_tags['details']);
				foreach ($smart_tags as $key => $value) {
					$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
				}

				// send
				if ($this->CI->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $plan_info->accountID)) {
					$byID = NULL;
					if (isset($this->CI->auth->user->staffID)) {
						$byID = $this->CI->auth->user->staffID;
					}

					// get html email and convert to plain text
					$this->CI->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					// save
					$data = array(
						'familyID' => $plan_info->familyID,
						'contactID' => $plan_info->contactID,
						'byID' => $byID,
						'type' => 'email',
						'destination' => $contact_info->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $plan_info->accountID
					);

					$this->CI->db->insert('family_notifications', $data);

					return TRUE;
				} else {
					return FALSE;
				}
			} else {
				return TRUE;
			}

		} catch (Exception $e) {
			//Return mandate not found error to user
			if (strtolower($e->getMessage())=="mandate not found") {
				return "mandate_not_found";
			}

			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}
	}

	public function cancel_subscription($subscription_id) {

		try {
			$res = $this->client->subscriptions()->cancel($subscription_id);

			if ($res->status == 'cancelled') {
				return TRUE;
			}
		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}

		return FALSE;
	}

	// helper functions

	public function generate_plan_code() {
		$code = random_string('alnum', 6);

		// check if exists
		$where = array(
			'gc_code' => $code
		);
		$res = $this->CI->db->from('family_payments_plans')->where($where)->limit(1)->get();
		if ($res->num_rows() == 0) {
			return $code;
		}

		// already exists, generate again
		return $this->generate_plan_code();
	}

	public function new_mandate($contactID, $subID = NULL) {

		// look up contact and ensure doesn't have existing mandate
		$where = array(
			'family_contacts.contactID' => $contactID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.gc_customer_id IS NULL' => NULL,
			'family_contacts.gc_mandate_id IS NULL' => NULL
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		$success_redirect_url = site_url('gc/confirm/' . $this->accountID);
		if($subID !== NULL) {
			$success_redirect_url .= '/' . $subID;
		}

		try {

			$params = [
				"params" => [
					// This will be shown on the payment pages
					"description" => 'Subscription',
					// Not the access token
					"session_token" => session_id(),
					"success_redirect_url" => $success_redirect_url,
					// Optionally, prefill customer details on the payment page
					"prefilled_customer" => [
						"given_name" => $contact_info->first_name,
						"family_name" => $contact_info->last_name,
						"email" => $contact_info->email,
						"address_line1" => $contact_info->address1,
						"city" => $contact_info->town,
						"postal_code" => $contact_info->postcode
					]
				]
			];

			$redirect_flow = $this->client->redirectFlows()->create($params);

			// update contact with redirect id
			$data = array(
				'gc_redirect_id' => $redirect_flow->id,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('family_contacts', $data, $where, 1);

			return $redirect_flow->redirect_url;

		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}
	}

	public function confirm_mandate($subID = NULL) {
		$redirect_flow_id = $this->CI->input->get('redirect_flow_id');

		if (empty($redirect_flow_id)) {
			return FALSE;
		}

		// look up
		$where = array(
			'family_contacts.gc_redirect_id' => $redirect_flow_id,
			'family_contacts.accountID' => $this->accountID
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		try {

			$redirect_flow = $this->client->redirectFlows()->complete(
				$redirect_flow_id, //The redirect flow ID from above.
				[
					"params" => [
						"session_token" => session_id()
					]
				]
			);

			// update contact with customer and mandate ids
			$data = array(
				'gc_mandate_id' => $redirect_flow->links->mandate,
				'gc_customer_id' => $redirect_flow->links->customer,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('family_contacts', $data, $where, 1);

			// track cart
			$cartID = NULL;

			// look up inactive payment plans and activate (within last month only)
			$where = array(
				'contactID' => $contact_info->contactID,
				'accountID' => $this->accountID,
				'status' => 'inactive',
				'added >' => date('Y-m-d', strtotime('-1 month'))
			);
			$res = $this->CI->db->from('family_payments_plans')->where($where)->order_by('added asc')->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$this->new_payment_plan($row->planID);
					if (!empty($row->cartID)) {
						$cartID = $row->cartID;
					}
				}

				// if from cart, return success url
				if (!empty($cartID)) {
					// tell user
					$cart_success_redirect = site_url('account/booking/' . $cartID . '#details');
					$cart_success_message = "Thank you for your booking. You can find your details below and we've also sent you a confirmation email with this information";
					$this->CI->session->set_flashdata('success', $cart_success_message);

					// redirect
					return $cart_success_redirect;
				}

				return $redirect_flow->confirmation_url;
			}else {

				if ($subID !== NULL) {
					// look up inactive payment plans and activate
					$this->start_subscription_crm($subID);
					// if ($res->num_rows() > 0) {
					// 	foreach ($res->result() as $row) {

					// 		if (!empty($row->cartID)) {
					// 			$cartID = $row->cartID;
					// 		}
					// 	}
					// }

					// if from cart, return success url
					if (!empty($cartID)) {
						// tell user
						$cart_success_redirect = site_url('account/booking/' . $cartID . '#details');
						$cart_success_message = "Thank you for your booking. You can find your details below and we've also sent you a confirmation email with this information";
						$this->CI->session->set_flashdata('success', $cart_success_message);

						// redirect
						return $cart_success_redirect;
					}

					return $redirect_flow->confirmation_url;
				} else {
					return TRUE;
				}
			}
		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}
	}

	public function cancel_mandate($contactID) {

		// look up contact and ensure has an existing mandate
		$where = array(
			'family_contacts.contactID' => $contactID,
			'family_contacts.accountID' => $this->accountID,
			'family_contacts.gc_mandate_id IS NOT NULL' => NULL
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->get();

		if ($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) {}

		try {

			$this->client->mandates()->cancel($contact_info->gc_mandate_id);

			// remove mandate and customer ids from contact
			$data = array(
				'gc_redirect_id' => NULL,
				'gc_customer_id' => NULL,
				'gc_mandate_id' => NULL,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->CI->db->update('family_contacts', $data, $where, 1);

			return TRUE;

		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}
	}

	public function get_payment($payment) {
		try {
			return $this->client->payments()->get($payment);
		} catch (Exception $e) {
			if ($this->debug) {
				show_error($e->getMessage(), 500);
			}
			return FALSE;
		}
	}

	public function handle_webhook() {
		$raw_payload = file_get_contents('php://input');
		$headers = getallheaders();
		if (!array_key_exists('Webhook-Signature', $headers)) {
			echo 'NOTOK';
			return FALSE;
		}

		try {
			// get events
			$events = GoCardlessPro\Webhook::parse($raw_payload, $headers["Webhook-Signature"], $this->CI->settings_library->get('gocardless_webhook_secret', $this->accountID));

			// Process the events...
			header("HTTP/1.1 200 OK");

			// Each webhook may contain multiple events to handle, batched together
			foreach ($events as $event) {
				print("Processing event " . $event->id . "\n");
				switch ($event->resource_type) {
					case "mandates":
						// handle cancelled/expired mandate

						// look up mandate
						$where = array(
							'gc_mandate_id' => $event->links->mandate
						);

						$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

						if ($res->num_rows() > 0) {
							foreach($res->result() as $contact_info) {
								if (in_array($event->action, array('cancelled', 'expired'))) {
									$db_data = array(
										'gc_redirect_id' => NULL,
										'gc_mandate_id' => NULL,
										'gc_customer_id' => NULL,
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);

									$res_update = $this->CI->db->update('family_contacts', $db_data, $where, 1);
								}
							}
						}
						break;
					case "subscriptions":
						// handle created, cancelled and completed subscriptions

						// look up plan
						$where = array(
							'gc_subscription_id' => $event->links->subscription
						);

						$res = $this->CI->db->from('participant_subscriptions')->where($where)->limit(1)->get();

						if ($res->num_rows() > 0) {
							foreach($res->result() as $sub_info) {
								// get status
								$status = NULL;
								switch ($event->action) {
									case 'created':
										$status = 'active';
										break;
									case 'cancelled':
										$status = 'cancelled';
										break;
									case 'finished':
										$status = 'completed';
										break;
								}
								// update
								if (!empty($status)) {
									$db_data = array(
										'status' => $status,
										'modified' => mdate('%Y-%m-%d %H:%i:%s')
									);

									$res_update = $this->CI->db->update('participant_subscriptions', $db_data, $where, 1);
								}
							}
						}else{
							// look up plan
							$where = array(
								'gc_subscription_id' => $event->links->subscription
							);

							$res = $this->CI->db->from('family_payments_plans')->where($where)->limit(1)->get();

							if ($res->num_rows() > 0) {
								foreach($res->result() as $plan_info) {
									// get status
									$status = NULL;
									switch ($event->action) {
										case 'created':
											$status = 'active';
											break;
										case 'cancelled':
											$status = 'cancelled';
											break;
										case 'finished':
											$status = 'completed';
											break;
									}
									// update
									if (!empty($status)) {
										$db_data = array(
											'status' => $status,
											'modified' => mdate('%Y-%m-%d %H:%i:%s')
										);

										$res_update = $this->CI->db->update('family_payments_plans', $db_data, $where, 1);
									}
								}
							}
						}
						break;
					case "payments":
						// handle payments

						// confirmed only
						if (!in_array($event->action, array('confirmed'))) {
							break;
						}

						// look up payment
						$payment_info = $this->get_payment($event->links->payment);

						if (!$payment_info || !isset($payment_info->links->subscription)) {
							break;
						}

						if(!isset($payment_info->links->mandate)) {
							echo 'no mandate';
							break;
						}

						$where = array(
							'gc_mandate_id' => $payment_info->links->mandate
						);

						$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

						foreach($res->result() as $contact_info) break;

						// look up subscription
						$where = array(
							'participant_subscriptions.gc_subscription_id' => $payment_info->links->subscription,
							'participant_subscriptions.status' => 'active',
						);

						$res = $this->CI->db
							->select('participant_subscriptions.*, subscriptions.*')
							->from('participant_subscriptions')
							->join('subscriptions', 'participant_subscriptions.subID = subscriptions.subID', 'inner')
							->where($where)
							->get();

						if ($res->num_rows() > 0) {
							foreach($res->result() as $sub_info) {}

							// build data
							$db_data = array(
								'contactID' => $contact_info->contactID,
								'amount' => $payment_info->amount/100,
								'method' => 'direct debit',
								'transaction_ref' => $event->links->payment,
								'modified' => mdate('%Y-%m-%d %H:%i:%s'),
								'note' => 'Direct Debit-'.$payment_info->links->subscription
							);

							$db_data['is_first_payment'] = '0';
							if(empty($sub_info->last_payment_date)){
								$db_data['is_first_payment'] = '2';
							}

							//update payment date
							$data = array(
								'last_payment_date' => mdate('%Y-%m-%d %H:%i:%s', strtotime($event->created_at))
							);

							$this->CI->db->update('participant_subscriptions', $data, $where, 1);

							// check if exists
							$where = array(
								'transaction_ref' => $event->links->payment
							);

							$res_check = $this->CI->db->from('family_payments')->where($where)->limit(1)->get();

							// doesnt exist
							if ($res_check->num_rows() == 0) {
								// create
								$db_data['familyID'] = $contact_info->familyID;
								$db_data['accountID'] = $sub_info->accountID;
								$db_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
								$this->CI->db->insert('family_payments', $db_data);
								$paymentID = $this->CI->db->insert_id();

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
									'bookings_cart_subscriptions.accountID' => $sub_info->accountID
								);
								$res = $this->CI->db
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
										'accountID' => $sub_info->accountID
									);
									$res = $this->CI->db
										->select('bookings_cart_sessions.sessionID')
										->from('bookings_cart_sessions')
										->where($where_session)
										->get();

									$session_data = array();
									if ($res->num_rows() > 0) {
										$cnt = 0;
										foreach($res->result() as $session_info){
											$session_data[] = array(
												'accountID' => $sub_info->accountID,
												'familyID' => $contact_info->familyID,
												'paymentID' => $paymentID,
												'sessionID' => $session_info->sessionID,
												'is_sub' => '1',
												'amount' => ($cnt == 0)?($payment_info->amount/100):0
											);
											$cnt++;
										}
										if(count($session_data) > 0){
											$this->CI->db->insert_batch('family_payments_sessions', $session_data);
										}
									}
								}

								// recalc balance
								$this->CI->crm_library->recalc_family_balance($contact_info->familyID);

								// send confirmation
								$this->CI->crm_library->send_payment_confirmation($paymentID);
							}
						}else{
							// look up plan
							$where = array(
								'family_payments_plans.gc_subscription_id' => $payment_info->links->subscription
							);

							$res = $this->CI->db->select('family_payments_plans.*, family_contacts.first_name as contact_first, family_contacts.last_name as contact_last, family_contacts.email as contact_email, family_contacts.address1 as contact_address1, family_contacts.address2 as contact_address2, family_contacts.town as contact_town, family_contacts.postcode as contact_postcode')
								->from('family_payments_plans')
								->join('family_contacts', 'family_payments_plans.contactID = family_contacts.contactID', 'inner')
								->where($where)
								->get();

							if ($res->num_rows() > 0) {
								foreach($res->result() as $plan_info) {}

								// build data
								$db_data = array(
									'contactID' => $plan_info->contactID,
									'amount' => $payment_info->amount/100,
									'method' => 'direct debit',
									'transaction_ref' => $event->links->payment,
									'modified' => mdate('%Y-%m-%d %H:%i:%s')
								);

								// check if exists
								$where = array(
									'transaction_ref' => $event->links->payment
								);

								$res_check = $this->CI->db->from('family_payments')->where($where)->limit(1)->get();

								// doesnt exist
								if ($res_check->num_rows() == 0) {
									// create
									$db_data['familyID'] = $plan_info->familyID;
									$db_data['accountID'] = $plan_info->accountID;
									$db_data['added'] = mdate('%Y-%m-%d %H:%i:%s');
									$this->CI->db->insert('family_payments', $db_data);
									$paymentID = $this->CI->db->insert_id();

									// recalc balance
									$this->CI->crm_library->recalc_family_balance($plan_info->familyID);

									// send confirmation
									$this->CI->crm_library->send_payment_confirmation($paymentID);
								}
							}
						}
						break;
					default:
						print("Don't know how to process an event with resource_type " . $event->resource_type . "\n");
						break;
				}
			}
			echo 'OK';
			return TRUE;
		} catch (GoCardlessPro\Core\Exception\InvalidSignatureException $e) {
			header("HTTP/1.1 498 Invalid Token");
			echo 'NOTOK';
			return FALSE;
		}
	}

	/**
	 * @return boolean
	 */
	public function send_confirm_email($subID, $contactID) {

		$where = array (
			'contactID' => $contactID,
			'accountID' => $this->accountID
		);

		$res = $this->CI->db->from('family_contacts')->where($where)->limit(1)->get();

		if($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $contact_info) break;

		$where = array(
			'subID' => $subID,
			'accountID' => $this->accountID
		);

		$res = $this->CI->db->from('subscriptions')->where($where)->limit(1)->get();

		if($res->num_rows() == 0) {
			return FALSE;
		}

		foreach ($res->result() as $sub) break;

		$smart_tags = array();
		$smart_tags['contact_first'] = $contact_info->first_name;

		//get company name
		$where = array(
			'accountID' => $this->accountID
		);
		$res = $this->CI->db->select('company')->from('accounts')->where($where)->limit(1)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// replace tags
				$smart_tags['company'] = $row->company;
			}
		}

		//get org name
		$where = array(
			'bookings.accountID' => $this->accountID,
			'bookings.bookingID' => $sub->bookingID
		);

		$res = $this->CI->db->select('orgs.name as org_name, bookings.name as bookings_name')
			->from('orgs')
			->join('bookings', 'orgs.orgID = bookings.orgID')
			->where($where)
			->limit(1)
			->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// replace tags
				$smart_tags['org_name'] = $row->org_name;
				$smart_tags['event_name'] = $row->bookings_name;
			}
		}

		//get session types
		$where = array(
			'subscriptions_lessons_types.subID' => $sub->subID,
			'subscriptions_lessons_types.accountID' => $this->accountID
		);


		/*$res = $this->CI->db->select('GROUP_CONCAT(' . $this->CI->db->dbprefix('lesson_types') .'.name SEPARATOR\', \') as types')
					->from('lesson_types')
					->join('subscriptions_lessons_types', 'lesson_types.typeID = subscriptions_lessons_types.typeID', 'inner')
					->where($where)
					->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $types) break;
		}*/

		//Get family balance
		$where_family = array(
			'familyID' => $contact_info->familyID,
			'accountID' => $this->accountID
		);
		$family_balance = $this->CI->db->from('family')->where($where_family)->limit(1)->get();
		$account_balance = 0;
		if ($family_balance->num_rows() > 0) {
			foreach ($family_balance->result() as $family_info) break;
			$account_balance = $family_info->account_balance;
		}

		$smart_tags['subscription_details'] =
			'Name: ' . $sub->subName . '<br>Frequency: ' . ucfirst($sub->frequency) . '<br>Rate: ' . currency_symbol($this->accountID) . $sub->price . '<br>No. of Sessions per Week: ' . $sub->no_of_sessions_per_week . '<br>';
		if($account_balance < 0) {
			$smart_tags['subscription_details'] .=
				'Total Outstanding Balance: ' . currency_symbol($this->accountID) . number_format($account_balance, 2) . '<br>';
		}

		$smart_tags['link'] = PROTOCOL . '://' . SUB_DOMAIN . '.' . ROOT_DOMAIN . '/account';

		// get email template
		$subject = $this->CI->settings_library->get('email_confirm_subscription_subject', $this->accountID);
		$email_html = $this->CI->settings_library->get('email_confirm_subscription', $this->accountID);

		// replace smart tags in email
		foreach ($smart_tags as $key => $value) {
			$email_html = str_replace('{' . $key . '}', $value, $email_html);
		}

		// replace smart tags in subject
		foreach ($smart_tags as $key => $value) {
			$subject = str_replace('{' . $key . '}', $this->CI->crm_library->htmlspecialchars_decode($value), $subject);
		}

		// send
		if ($this->CI->crm_library->send_email($contact_info->email, $subject, $email_html, array(), TRUE, $this->accountID)) {
			$byID = NULL;
			if (isset($this->CI->auth->user->staffID)) {
				$byID = $this->CI->auth->user->staffID;
			}

			// get html email and convert to plain text
			$this->CI->load->helper('html2text');
			$html2text = new \Html2Text\Html2Text($email_html);
			$email_plain = $html2text->get_text();

			// save
			$data = array(
				'familyID' => $contact_info->familyID,
				'contactID' => $contact_info->contactID,
				'byID' => $byID,
				'type' => 'email',
				'destination' => $contact_info->email,
				'subject' => $subject,
				'contentHTML' => $email_html,
				'contentText' => $email_plain,
				'status' => 'sent',
				'added' => mdate('%Y-%m-%d %H:%i:%s'),
				'modified' => mdate('%Y-%m-%d %H:%i:%s'),
				'accountID' => $this->accountID
			);
			$this->CI->db->insert('family_notifications', $data);

			if($this->CI->db->affected_rows() == 1) {
				return true;
			} else {
				return false;
			}
		}

		return TRUE;
	}

	public function valid_config() {

		// check fields are all set in settings
		if ($this->CI->settings_library->get('gocardless_access_token', $this->accountID) != '' && $this->CI->settings_library->get('gocardless_environment', $this->accountID) != '' && $this->CI->settings_library->get('gocardless_webhook_secret', $this->accountID) != '') {
			return TRUE;
		}

		return FALSE;
	}

}
