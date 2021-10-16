<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cron extends MY_Controller {

	public function __construct() {
		// allow public access
		parent::__construct(TRUE);

		// if CRON_KEY specified (and not empty) in environmental vars, check agaisnt request header
		if (!empty(getenv('CRON_KEY')) && getenv('CRON_KEY') !== $this->input->get_request_header('Cron-Key')) {
			show_403();
		}

		$this->load->library('offer_accept_library');
	}

	public function index() {
		// system functions
		$this->customer_status();
		$this->create_timesheets();
		$this->submit_timesheets();
		$this->generate_session_evaluations();

		// these send emails/sms
		$this->send_thanks_email('block');
		$this->send_thanks_email('event');
		$this->payment_reminder_before();
		$this->payment_reminder_after();
		$this->renewal_reminders();
		$this->birthday_emails();
		$this->send_renewal_email();

		echo 'OK';
		return TRUE;
	}

	public function minute() {
		echo "Sending checkin and checkout notifications: \n";

		$this->crm_library->send_checkin_staff_alerts();

		$this->crm_library->send_checkout_staff_alerts();

		echo "Checking session offers timeout: \n";

		$processed_offers = $this->offer_accept_library->process_offers_timeout();

		echo "Processed Offers: " . $processed_offers . "\n";

		$processed_offers = $this->offer_accept_library->process_manual_offers_timeout();

		echo "Processed Manual Offers: " . $processed_offers . "\n";

		echo "Done";
	}

	private function send_renewal_email() {
		echo "Sending Renewal Email Alerts: ";
		$sent = 0;

		if ($this->settings_library->get('send_renewal_alert') != 1) {
			return false;
		}

		$accounts = $this->db->select()->from('accounts')->get();
		foreach ($accounts->result() as $account) {
			$datediff = strtotime($account->paid_until) - time();

			$days = round($datediff / (60 * 60 * 24));

			if ($days == 30 || $days == 15) {
				if ($this->crm_library->send_expiring_alert($account)) {
					$sent++;
				}
			}
		}
		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}
	}

	/**
	 * convert customers to prospects and vice versa depending on activity
	 * @return void
	 */
	private function customer_status() {

		echo "Convert customers with no bookings or block bookings in last 3 months (or ever) to prospects: ";

		$sql = "UPDATE `" . $this->db->dbprefix('orgs') . "` AS o INNER JOIN (
				SELECT o.`orgID`, GREATEST(COALESCE(MAX(b.`endDate`), 0),  COALESCE(MAX(bl.`endDate`), 0)) AS `endDate`
				FROM `" . $this->db->dbprefix('orgs') . "` AS o
				LEFT JOIN `" . $this->db->dbprefix('bookings') . "` AS b ON o.`orgID` = b.`orgID`
				LEFT JOIN `" . $this->db->dbprefix('bookings_blocks') . "` AS bl ON o.`orgID` = bl.`orgID`
				LEFT JOIN `" . $this->db->dbprefix('accounts_settings') . "` AS s ON o.`accountID` = s.`accountID` AND s.`key` = 'disable_prospects_automation'
				WHERE o.`prospect` = '0' AND (s.`key` IS NULL OR s.`value` != 1)
				GROUP BY o.`orgID`
				HAVING `endDate` = 0 OR `endDate` < DATE_SUB(NOW(), INTERVAL 3 MONTH)
			) AS s ON o.`orgID` = s.`orgID` SET `prospect` = '1'";

		$res = $this->db->query($sql);

		// if found
		if ($this->db->affected_rows() > 0) {
			echo $this->db->affected_rows() . "<br />";
		} else {
			echo "None<br />";
		}

		echo "Convert prospects with bookings or block bookings in last 3 months (or future) to customers: ";

		$sql = "UPDATE `" . $this->db->dbprefix('orgs') . "` AS o INNER JOIN (
				SELECT o.`orgID`, GREATEST(COALESCE(MAX(b.`endDate`), 0),  COALESCE(MAX(bl.`endDate`), 0)) AS `endDate`
				FROM `" . $this->db->dbprefix('orgs') . "` AS o
				LEFT JOIN `" . $this->db->dbprefix('bookings') . "` AS b ON o.`orgID` = b.`orgID`
				LEFT JOIN `" . $this->db->dbprefix('bookings_blocks') . "` AS bl ON o.`orgID` = bl.`orgID`
				LEFT JOIN `" . $this->db->dbprefix('accounts_settings') . "` AS s ON o.`accountID` = s.`accountID` AND s.`key` = 'disable_prospects_automation'
				WHERE o.`prospect` = '1' AND (s.`key` IS NULL OR s.`value` != 1)
				GROUP BY o.`orgID`
				HAVING `endDate` >= DATE_SUB(NOW(), INTERVAL 3 MONTH)
			) AS s ON o.`orgID` = s.`orgID` SET `prospect` = '0'";

		$res = $this->db->query($sql);

		// if found
		if ($this->db->affected_rows() > 0) {
			echo $this->db->affected_rows() . "<br />";
		} else {
			echo "None<br />";
		}
	}

	/**
	 * send thanks email for past events
	 *
	 * @param string $type
	 * @return void
	 */
	private function send_thanks_email($type = 'block') {

		echo "Send thanks emails for " . $type . "s: ";
		$sent = 0;

		$table = 'bookings_blocks';
		$id_field = 'blockID';
		if ($type == 'event') {
			$table = 'bookings';
			$id_field = 'bookingID';
		}

		// look up events
		$where = array(
			'bookings.project' => 1,
			$table . '.thanksemail' => 1,
			$table . '.thanksemail_sent !=' => 1,
			$table . '.endDate >=' => mdate('%Y-%m-%d', strtotime('-1 week')),
			$table . '.endDate <' => mdate('%Y-%m-%d'),
			'accounts.active' => 1
		);

		$res = $this->db->select($table . '.' . $id_field . ', bookings.accountID')
			->from('bookings')
			->join('bookings_blocks', 'bookings.bookingID = bookings_blocks.bookingID', 'inner')
			->join('accounts', 'bookings_blocks.accountID = accounts.accountID', 'inner')
			->where($where)
			->group_by($table . '.' . $id_field)
			->get();

		// if found
		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				if ($this->settings_library->get('send_' . $type . '_thanks', $row->accountID) != 1) {
					continue;
				}
				$this->crm_library->send_thanks_email($type, $row->$id_field);
				$sent++;
			}
		}

		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}

	}

	/**
	 * send payment reminder 1 week before if have outstanding balance
	 * @return void
	 */
	private function payment_reminder_before() {

		echo "Send payment reminders 1 week before if have outstanding balance: ";
		$sent = 0;

		// look up
		$where = array(
			'bookings_cart.balance >' => 0,
			'bookings_cart.payment_reminder_before !=' => 1,
			'bookings_cart.type' => 'booking',
			'accounts.active' => 1
		);
		$having = array(
			'MIN('. $this->db->dbprefix('bookings_cart_sessions') . '.date) >' => mdate('%Y-%m-%d'),
			'MIN('. $this->db->dbprefix('bookings_cart_sessions') . '.date) <=' => mdate('%Y-%m-%d', strtotime('+1 week'))
		);

		$bookings = $this->db->select('bookings_cart.*, bookings.brandID, bookings.name as event, bookings.startDate, family_contacts.first_name, family_contacts.email')
		->from('bookings_cart')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->join('accounts', 'bookings.accountID = accounts.accountID', 'inner')
		->where($where)
		->group_by('bookings_cart.cartID')
		->having($having)
		->get();

		// if found
		if ($bookings->num_rows() > 0) {
			foreach ($bookings->result() as $booking) {

				if ($this->settings_library->get('send_payment_reminder_before', $booking->accountID) != 1) {
					continue;
				}

				// mark as sent
				$data = array(
					'payment_reminder_before' => 1
				);
				$where = array(
					'cartID' => $booking->cartID,
					'accountID' => $booking->accountID
				);
				$res = $this->db->update('bookings_cart', $data, $where, 1);

				// check email not empty and valid
				if (empty($booking->email) || !filter_var($booking->email, FILTER_VALIDATE_EMAIL)) {
					// skip
					continue;
				}

				// smart tags
				$smart_tags = array(
					'contact_first' => $booking->first_name,
					'amount' => $booking->balance,
					'event_name' => $booking->event,
					'start_date' => mysql_to_uk_date($booking->startDate),
					'childrens_names' => 'you',
					'sessions' => $this->crm_library->get_booked_sessions_html($booking->cartID)
				);

				// get childrens names
				$where = array(
					'bookings_cart_sessions.cartID' => $booking->cartID,
					'bookings_cart_sessions.accountID' => $this->auth->user->accountID
				);

				$res_children = $this->db->select('GROUP_CONCAT(DISTINCT '. $this->db->dbprefix('family_children') . '.first_name) as child_names, GROUP_CONCAT(DISTINCT '. $this->db->dbprefix('family_contacts') . '.first_name) as contact_names')
				->from('bookings_cart_sessions')
				->join('family_children', 'bookings_cart_sessions.childID = family_children.childID', 'inner')
				->join('family_contacts', 'bookings_cart_sessions.contactID = family_contacts.contactID', 'inner')
				->where($where)
				->group_by('bookings_cart_sessions.cartID')
				->get();

				if ($res_children->num_rows() > 0) {
					$names = array();
					foreach ($res_children->result() as $child) {
						$names = (array)explode(",", $child->child_names) + (array)explode(",", $child->contact_names);
					}
					$names = array_filter($names);
					$x = 0;
					$smart_tags['childrens_names'] = NULL;
					foreach ($names as $child) {
						if (count($names) > 1) {
							if ($x == (count($names) - 1)) {
								$smart_tags['childrens_names'] .= ' and ';
							} else if ($x > 0) {
								$smart_tags['childrens_names'] .= ', ';
							}
						}
						$smart_tags['childrens_names'] .= $child;
						$x++;
					}
				}

				// get email template
				$subject = $this->settings_library->get('email_payment_reminder_before_subject', $booking->accountID);
				$email_html = $this->settings_library->get('email_payment_reminder_before', $booking->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				// replace smart tags in subject
				foreach ($smart_tags as $key => $value) {
					$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
				}

				// get html email and convert to plain text
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				if ($this->crm_library->send_email($booking->email, $subject, $email_html, array(), TRUE, $booking->accountID, $booking->brandID)) {

					// save
					$data = array(
						'familyID' => $booking->familyID,
						'contactID' => $booking->contactID,
						'byID' => NULL,
						'type' => 'email',
						'destination' => $booking->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $booking->accountID
					);

					$this->db->insert('family_notifications', $data);

					$sent++;
				}
			}
		}

		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}

	}

	/**
	 * send payment reminder 1 week after if have outstanding balance
	 * @return void
	 */
	private function payment_reminder_after() {

		echo "Send payment reminders 1 week after if have outstanding balance: ";
		$sent = 0;

		// look up
		$where = array(
			'bookings_cart.balance >' => 0,
			'bookings_cart.payment_reminder_after !=' => 1,
			'bookings_cart.type' => 'booking',
			'accounts.active' => 1
		);
		$having = array(
			'MAX('. $this->db->dbprefix('bookings_cart_sessions') . '.date) <=' => mdate('%Y-%m-%d', strtotime('-1 week'))
		);

		$bookings = $this->db->select('bookings_cart.*, bookings.name as event, bookings.brandID, family_contacts.first_name, family_contacts.email, family_contacts.mobile')
		->from('bookings_cart')
		->join('bookings_cart_sessions', 'bookings_cart.cartID = bookings_cart_sessions.cartID', 'inner')
		->join('bookings', 'bookings_cart_sessions.bookingID = bookings.bookingID', 'inner')
		->join('family_contacts', 'bookings_cart.contactID = family_contacts.contactID', 'inner')
		->join('accounts', 'bookings.accountID = accounts.accountID', 'inner')
		->where($where)
		->group_by('bookings_cart.cartID')
		->having($having)
		->get();

		// if found
		if ($bookings->num_rows() > 0) {
			foreach ($bookings->result() as $booking) {

				// mark as sent
				$data = array(
					'payment_reminder_after' => 1
				);
				$where = array(
					'cartID' => $booking->cartID,
					'accountID' => $booking->accountID
				);
				$res = $this->db->update('bookings_cart', $data, $where, 1);

				// smart tags
				$smart_tags = array(
					'contact_first' => $booking->first_name,
					'amount' => $booking->balance,
					'event_name' => $booking->event
				);

				// check email not empty and valid
				if ($this->settings_library->get('send_payment_reminder_after', $booking->accountID) == 1 && !empty($booking->email) && filter_var($booking->email, FILTER_VALIDATE_EMAIL)) {

					// get email template
					$subject = $this->settings_library->get('email_payment_reminder_after_subject', $booking->accountID);
					$email_html = $this->settings_library->get('email_payment_reminder_after', $booking->accountID);

					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
						$email_html = str_replace('{' . $key . '}', $value, $email_html);
					}

					// replace smart tags in subject
					foreach ($smart_tags as $key => $value) {
						$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
					}

					// get html email and convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					if ($this->crm_library->send_email($booking->email, $subject, $email_html, array(), TRUE, $booking->accountID, $booking->brandID)) {

						// save
						$data = array(
							'familyID' => $booking->familyID,
							'contactID' => $booking->contactID,
							'byID' => NULL,
							'type' => 'email',
							'destination' => $booking->email,
							'subject' => $subject,
							'contentHTML' => $email_html,
							'contentText' => $email_plain,
							'status' => 'sent',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $booking->accountID
						);

						$this->db->insert('family_notifications', $data);

						$sent++;
					}

				}

				// get sms template
				$sms = $this->settings_library->get('sms_payment_reminder_after', $booking->accountID);

				// check mobile and template not empty
				if ($this->settings_library->get('send_payment_reminder_sms', $booking->accountID) == 1 && !empty($booking->mobile) && !empty($sms)) {

					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$sms = str_replace('{' . $key . '}', $value, $sms);
					}

					// normalise mobile
					$booking->mobile = $this->crm_library->normalise_mobile($booking->mobile, $booking->accountID);

					if ($this->crm_library->check_mobile($booking->mobile, $booking->accountID)) {

						// save
						$data = array(
							'familyID' => $booking->familyID,
							'contactID' => $booking->contactID,
							'byID' => NULL,
							'type' => 'sms',
							'destination' => $booking->mobile,
							'contentText' => $sms,
							'status' => 'pending',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $booking->accountID
						);

						$this->db->insert('family_notifications', $data);

						$sent++;
					}

				}

			}
		}

		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}

	}

	/**
	 * customer contract renewal reminders
	 * @return void
	 */
	private function renewal_reminders() {

		echo "Send customer contract renewals: ";

		$sent = 0;

		// look up
		$where = array(
			'bookings.contract_renewal' => 1,
			'bookings.renewalDate IS NOT NULL' => NULL,
			'bookings.renewalMeetingDate IS NULL' => NULL,
			'bookings.contract_renewed IS NULL' => NULL,
			'bookings.type' => 'booking',
			'accounts.active' => 1
		);

		$bookings = $this->db->select('bookings.*, orgs_contacts.email, orgs_contacts.name as contact, orgs.name as org')->from('bookings')->join('orgs', 'bookings.orgID = orgs.orgID', 'inner')->join('orgs_contacts', 'bookings.contactID = orgs_contacts.contactID', 'inner')->join('accounts', 'bookings.accountID = accounts.accountID', 'inner')->where($where)->group_by('bookings.bookingID')->get();

		// if found
		if ($bookings->num_rows() > 0) {
			foreach ($bookings->result() as $booking) {

				if ($this->settings_library->get('send_renewal_reminders', $booking->accountID) != 1) {
					continue;
				}

				// track possible dates
				$possible_dates = [];

				// work out first date
				$first_reminder = $this->settings_library->get('email_renewal_reminder_first', $booking->accountID);
				$first_date = date('Y-m-d', strtotime($booking->renewalDate) - ($first_reminder*24*60*60));
				$possible_dates[] = $first_date;

				// work out additional dates
				$additional_interval = $this->settings_library->get('email_renewal_reminder_additional', $booking->accountID);
				if (!empty($additional_interval) && $additional_interval > 0) {
					// max 10 emails
					for ($i = 1; $i <= 10; $i++) {
						$possible_dates[] = date('Y-m-d', strtotime($first_date) + ($additional_interval*$i*24*60*60));
					}
				}

				// if dates are on weekend, move to monday
				foreach ($possible_dates as $key => $possible_date) {
					$possible_date = strtotime($possible_date);
					if (date('N', $possible_date) == 6) {
						// if saturday, delay 2 days
						$possible_date += 60*60*24*2;
					} else if (date('N', $possible_date) == 7) {
						// if sunday, delay 1 day
						$possible_date += 60*60*24;
					}
					$possible_dates[$key] = date('Y-m-d', $possible_date);
				}

				// check for todays date
				if (!in_array(date('Y-m-d'), $possible_dates)) {
					continue;
				}

				// check not already sent
				$dates_sent = explode(',', $booking->contract_reminders_sent);
				if (!is_array($dates_sent)) {
					$dates_sent = [];
				}
				if (in_array(date('Y-m-d'), $dates_sent)) {
					continue;
				}

				// store new date so dont send again
				$dates_sent[] = date('Y-m-d');
				$data = array(
					'contract_reminders_sent' => implode(',', $dates_sent)
				);
				$where = array(
					'bookingID' => $booking->bookingID,
					'accountID' => $booking->accountID
				);
				$res = $this->db->update('bookings', $data, $where, 1);

				// check email not empty and valid
				if (empty($booking->email) || !filter_var($booking->email, FILTER_VALIDATE_EMAIL)) {
					// skip
					continue;
				}

				// work out period until renewal date
				$period = (strtotime($booking->renewalDate) - strtotime(date('Y-m-d')))/(24*60*60) . ' days';

				// smart tags
				$smart_tags = array(
					'main_contact' => $booking->contact,
					'org_name' => $booking->org,
					'date_description' => ' between ' . mysql_to_uk_date($booking->startDate) . ' and ' . mysql_to_uk_date($booking->endDate),
					'renewal_date' => mysql_to_uk_date($booking->renewalDate),
					'reminder_period' => $period
				);

				// if one day only, change text
				if ($booking->startDate == $booking->endDate) {
					$smart_tags['date_description'] = ' on ' . mysql_to_uk_date($booking->startDate);
				}

				// get email template
				$subject = $this->settings_library->get('email_renewal_reminder_subject', $booking->accountID);
				$email_html = $this->settings_library->get('email_renewal_reminder', $booking->accountID);

				// replace smart tags in email
				foreach ($smart_tags as $key => $value) {
					$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
					$email_html = str_replace('{' . $key . '}', $value, $email_html);
				}

				// replace smart tags in subject
				foreach ($smart_tags as $key => $value) {
					$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
				}

				// get html email and convert to plain text
				$this->load->helper('html2text');
				$html2text = new \Html2Text\Html2Text($email_html);
				$email_plain = $html2text->get_text();

				if ($this->crm_library->send_email($booking->email, $subject, $email_html, array(), TRUE, $booking->accountID, $booking->brandID)) {

					// save
					$data = array(
						'orgID' => $booking->orgID,
						'contactID' => $booking->contactID,
						'byID' => NULL,
						'type' => 'email',
						'destination' => $booking->email,
						'subject' => $subject,
						'contentHTML' => $email_html,
						'contentText' => $email_plain,
						'status' => 'sent',
						'added' => mdate('%Y-%m-%d %H:%i:%s'),
						'modified' => mdate('%Y-%m-%d %H:%i:%s'),
						'accountID' => $booking->accountID
					);

					$this->db->insert('orgs_notifications', $data);

					$sent++;
				}
			}
		}

		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}

	}

	/**
	 * send queued sms
	 * @return mixed
	 */
	public function sms() {
		// get active accounts
		$where = array(
			'active' => 1
		);

		$res = $this->db->from('accounts')->where($where)->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				$from_name = $this->settings_library->get('sms_from', $row->accountID);

				$smart_tags = array(
					'company' => $row->company
				);

				foreach ($smart_tags as $key => $value) {
					$from_name = str_replace('{' . $key . '}', $value, $from_name);
				}

				$this->textlocal($row->accountID, $from_name);
			}
		}

		echo 'OK';
		return TRUE;
	}

	/**
	 * send sms per accounts
	 * @param  int $accountID
	 * @return mixed
	 */
	private function textlocal($accountID = NULL, $from_name = NULL) {

		if (empty($accountID)) {
			return FALSE;
		}

		// load config
		$this->config->load('textlocal', TRUE);

		// get pending notifications
		$where = array(
			'type' => 'sms',
			'status' => 'pending',
			'accountID' => $accountID
		);

		$res = $this->db->from('family_notifications')->where($where)->order_by('family_notifications.added asc')->limit($this->config->item('cron_limit', 'textlocal'))->get();

		// if found
		if ($res->num_rows() > 0) {

			// set default from name, if empty
			if (empty($from_name)) {
				$from_name = $this->config->item('from', 'textlocal');
			}

			// from name can only be 11 characters
			$from_name = substr($from_name, 0, 11);

			// load helper
			$this->load->helper('xml_helper');

			// build xml
			$xml = new SimpleXMLExtended("<SMS></SMS>");

			// Account node
			$xmlAccount = $xml->addChild('Account');
			$xmlAccount->addAttribute('apikey', $this->config->item('apikey', 'textlocal'));

			// if not production, send in test mode
			if (substr(ENVIRONMENT, 0, 10) != 'production') {
				$testValue = 1;
			} else {
				$testValue = 0;
			}
			$xmlAccount->addAttribute('Test', $testValue);

			$xmlAccount->addAttribute('Info', 1);
			$xmlAccount->addAttribute('JSON', 0);

			// Sender node
			$xmlSender = $xmlAccount->addChild('Sender');
			$xmlSender->addAttribute('From', $from_name);
			$xmlSender->addAttribute('rcpurl', $this->config->item('report_url', 'textlocal'));

			// Messages node
			$xmlMessages = $xmlSender->addChild('Messages');

			// keep sent list
			$sentTo = array();

			// loop through messages
			foreach ($res->result() as $row) {

				// check valid mobile
				if ($this->crm_library->check_mobile($row->destination, $row->accountID)) {
					$xmlMessage = $xmlMessages->addChild('Msg');
					$xmlMessage->addAttribute('ID', $row->notificationID);
					$xmlMessage->addAttribute('Number', $row->destination);
					$xmlMessageText = $xmlMessage->addChild('Text')->addCData(str_replace(" & ", " and ", $row->contentText));
					$sentTo[] = $row->notificationID;
				} else {

					// set as invalid
					$where = array(
						'notificationID' => $row->notificationID,
						'status' => 'pending'
					);
					$data = array(
						'status' => 'invalid',
						'modified' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$res_update = $this->db->update('family_notifications', $data, $where, 1);
				}
			}

			/*Header('Content-type: text/xml');
			echo $xml->asXML();
			exit();*/

			// attempt send
			$post = 'data='. urlencode($xml->asXML());
			$url = "https://www.txtlocal.com/xmlapi.php";
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$return_data = curl_exec($ch);
			curl_close($ch);

			if (strpos(strtolower($return_data), "error") === 0) {
				echo "ERROR";
				return FALSE;
			} else {
				// update status to sent
				if (count($sentTo) > 0) {
					foreach ($sentTo as $notificationID) {
						$where = array(
							'notificationID' => $notificationID,
							'status' => 'pending'
						);
						$data = array(
							'status' => 'sent',
							'modified' => mdate('%Y-%m-%d %H:%i:%s')
						);

						$res_update = $this->db->update('family_notifications', $data, $where, 1);
					}
				}

				// all ok
				echo $accountID . "-OK<br />";
				return TRUE;
			}

		} else {
			echo $accountID . "-NONEPENDING<br />";
			return FALSE;
		}
	}

	/**
	 * sync mailchimp lists
	 * @return boolean
	 */
	public function mailchimp() {

		// set limits for each time runs
		$limit = 5;
		if (substr(ENVIRONMENT, 0, 10) == 'production') {
			$limit = 100;
		}

		// get active accounts
		$where= array(
			'active' => 1
		);
		$accounts = $this->db->from('accounts')->where($where)->get();

		foreach ($accounts->result() as $account) {

			// check for API key
			$api_key = $this->settings_library->get('mailchimp_key', $account->accountID);

			// if non or same as default, skip
			if (empty($api_key) || $api_key == $this->settings_library->get('mailchimp_key', 'default')) {
				echo $account->accountID . "-NOKEY<br />";
				continue;
			}

			// get brands
			$list_ids = array();
			$where = array(
				'accountID' => $account->accountID,
				'active' => 1,
				'mailchimp_id !=' => '',
				'mailchimp_id IS NOT NULL' => NULL
			);
			$brands = $this->db->from('brands')->where($where)->order_by('name asc')->get();

			if ($brands->num_rows() > 0) {
				foreach ($brands->result() as $brand) {
					$list_ids[$brand->brandID] = $brand->mailchimp_id;
				}
			}

			// check for main list
			$main_audience = $this->settings_library->get('mailchimp_audience_id', $account->accountID);
			if (!empty($main_audience)) {
				$list_ids['main'] = $main_audience;
			}

			// if no lists, skip
			if (count($list_ids) == 0) {
				echo $account->accountID . "-NOLISTS<br />";
				continue;
			}

			// create lists
			$subscribed = array();
			$unsubscribed = array();

			// get family pending syncs
			$where = array(
				'family_contacts.email !=' => '',
				'family_contacts.email IS NOT NULL' => NULL,
				'family_contacts.mc_synced <' => mdate('%Y-%m-%d %H:%i:%s', strtotime('-1 day')),
				'family_contacts.accountID' => $account->accountID
			);

			$res = $this->db->select('family_contacts.contactID, family_contacts.first_name, family_contacts.last_name, family_contacts.email, family_contacts.marketing_consent, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('family_contacts_newsletters') . '.brandID SEPARATOR \',\') AS newsletters')->join('family_contacts_newsletters', 'family_contacts.contactID = family_contacts_newsletters.contactID', 'left')->from('family_contacts')->where($where)->group_by('family_contacts.contactID')->limit($limit)->order_by('family_contacts.mc_synced asc')->get();

			// if found
			if ($res->num_rows() > 0) {

				foreach ($res->result() as $row) {
					foreach ($list_ids as $brand_id => $list_id) {
						$newsletters = explode(",", $row->newsletters);
						if (!is_array($newsletters)) {
							$newsletters = array();
						}
						// check if subscribed to newsletter
						if ($row->marketing_consent == 1 && (in_array($brand_id, $newsletters) || $brand_id === 'main')) {
							// subscribed - update info
							$subscribed[$list_id][] = array(
								'email_address' => $row->email,
								'status' => 'subscribed',
								'merge_fields' => array(
									'FNAME' => $row->first_name,
									'LNAME' => $row->last_name
								)
							);
						} else {
							// not subscribed
							$unsubscribed[$list_id][] = $row->email;
						}
					}

					// update last synced
					$where = array(
						'contactID' => $row->contactID
					);
					$data = array(
						'mc_synced' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$res_update = $this->db->update('family_contacts', $data, $where, 1);
				}
			}

			// get orgs pending syncs
			$where = array(
				'orgs_contacts.email !=' => '',
				'orgs_contacts.email IS NOT NULL' => NULL,
				'orgs_contacts.mc_synced <' => mdate('%Y-%m-%d %H:%i:%s', strtotime('-1 day')),
				'orgs_contacts.accountID' => $account->accountID,
				'orgs_contacts.active' => 1
			);

			$res = $this->db->select('orgs_contacts.contactID, orgs_contacts.name, orgs_contacts.email, GROUP_CONCAT(DISTINCT ' . $this->db->dbprefix('orgs_contacts_newsletters') . '.brandID SEPARATOR \',\') AS newsletters')->join('orgs_contacts_newsletters', 'orgs_contacts.contactID = orgs_contacts_newsletters.contactID', 'left')->from('orgs_contacts')->where($where)->group_by('orgs_contacts.contactID')->limit($limit)->order_by('orgs_contacts.mc_synced asc')->get();

			// if found
			if ($res->num_rows() > 0) {

				foreach ($res->result() as $row) {
					foreach ($list_ids as $brand_id => $list_id) {
						// main newsletter not applicable to org customers
						if ($brand_id === 'main') {
							continue;
						}
						$newsletters = explode(",", $row->newsletters);
						if (!is_array($newsletters)) {
							$newsletters = array();
						}
						// check if subscribed to newsletter
						if (in_array($brand_id, $newsletters)) {
							// subscribed - update info
							$subscribed[$list_id][] = array(
								'email_address' => $row->email,
								'status' => 'subscribed',
								'merge_fields' => array(
									'FNAME' => $row->name
								)
							);
						} else {
							// not subscribed
							$unsubscribed[$list_id][] = $row->email;
						}
					}

					// update last synced
					$where = array(
						'contactID' => $row->contactID
					);
					$data = array(
						'mc_synced' => mdate('%Y-%m-%d %H:%i:%s')
					);

					$res_update = $this->db->update('orgs_contacts', $data, $where, 1);
				}
			}

			if (count($subscribed) > 0 || count($unsubscribed) > 0) {

				try {

					// load library
					$mailchimp = new \DrewM\MailChimp\MailChimp($api_key);

					// start new batch
					$batch = $mailchimp->new_batch();
					$operation_id_base = 'op_' . time() . '_';
					$operation_id = 1;

					// if some people subscribed, sync
					if (count($subscribed) > 0) {

						foreach ($subscribed as $list_id => $subscribers) {
							foreach ($subscribers as $subscriber) {
								$batch->put($operation_id_base . $operation_id, "lists/$list_id/members/" . $mailchimp->subscriberHash($subscriber['email_address']), $subscriber);
								$operation_id++;
							}
						}

					}

					// if some people unsubscribed, sync
					if (count($unsubscribed) > 0) {

						foreach ($unsubscribed as $list_id => $unsubscribers) {
							foreach ($unsubscribers as $unsubscriber_email) {
								$unsubscriber = array(
									'status' => 'unsubscribed'
								);
								$batch->patch($operation_id_base . $operation_id, "lists/$list_id/members/" . $mailchimp->subscriberHash($unsubscriber_email), $unsubscriber);
								$operation_id++;
							}
						}

					}

					$result = $batch->execute();

					// all ok
					echo $account->accountID . "-OK<br />";

				} catch (Exception $e) {

					// load config
					$this->config->load('email', TRUE);

					// if error, send email to tech
					$this->crm_library->send_email($this->settings_library->get('tech_email', 'default'), "Mailchimp Error", "<p>".$e->getMessage()."</p>");

					echo $account->accountID . "-ERROR<br />";
				}
			} else {
				echo $account->accountID . "-NONEPENDING<br />";
			}
		}

		echo 'OK';
		return TRUE;
	}

	/**
	 * send birthday emails
	 * @return void
	 */
	private function birthday_emails() {

		echo "Send birthday emails: ";
		$sent = 0;

		// look up children between 5 and 12 (inclusive) with birthday today
		$where = array(
			'DAY(' . $this->db->dbprefix('family_children') . '.`dob`)' => date('d'),
			'MONTH(' . $this->db->dbprefix('family_children') . '.`dob`)' => date('m'),
			'(`last_ecard_year` < ' . date('Y') . ' OR `last_ecard_year` IS NULL)' => NULL,
			'YEAR(' . $this->db->dbprefix('family_children') . '.`dob`) <=' => date('Y', strtotime('-5 years')),
			'YEAR(' . $this->db->dbprefix('family_children') . '.`dob`) >=' => date('Y', strtotime('-12 years')),
			'family_contacts.main' => 1,
			'family_contacts.email !=' => '',
			'family_contacts.email IS NOT NULL' => NULL,
			'accounts.active' => 1
		);

		$res = $this->db->select('family_children.accountID, family_children.familyID, family_children.childID, family_children.first_name, family_children.dob, family_contacts.email, family_contacts.contactID')->from('family_children')->join('family_contacts', 'family_children.familyID = family_contacts.familyID', 'inner')->join('accounts', 'family_children.accountID = accounts.accountID', 'inner')->where($where)->group_by('family_contacts.contactID, family_children.childID')->get();

		// if found
		if ($res->num_rows() > 0) {

			// load config
			$this->config->load('email', TRUE);

			foreach ($res->result() as $row) {
				// if account set to send birthday email and email is valid
				if ($this->settings_library->get('send_birthday_emails', $row->accountID) && !empty($row->email) && filter_var($row->email, FILTER_VALIDATE_EMAIL)) {

					// smart tags
					$smart_tags = array(
						'first_name' => $row->first_name,
						'age' => calculate_age($row->dob)
					);

					// get email template
					$subject = $this->settings_library->get('email_birthday_email_subject', $row->accountID);
					$email_html = $this->settings_library->get('email_birthday_email', $row->accountID);

					// replace smart tags in email
					foreach ($smart_tags as $key => $value) {
						$email_html = str_replace('<p>{' . $key . '}</p>', $value, $email_html);
						$email_html = str_replace('{' . $key . '}', $value, $email_html);
					}

					// replace smart tags in subject
					foreach ($smart_tags as $key => $value) {
						$subject = str_replace('{' . $key . '}', $this->crm_library->htmlspecialchars_decode($value), $subject);
					}

					// get html email and convert to plain text
					$this->load->helper('html2text');
					$html2text = new \Html2Text\Html2Text($email_html);
					$email_plain = $html2text->get_text();

					// attachment
					$attachments = array();

					// attach birthday image if set
					$attachment_data = @unserialize($this->settings_library->get('email_birthday_email_image', $row->accountID));
					if ($attachment_data !== FALSE) {
						$uploadpath = UPLOADPATH;
						// if using AWS, switch bucket depending on account ID
						if (AWS === TRUE) {
							$s3_bucket = $this->aws_library->init_s3();
							$uploadpath = 's3://' . $s3_bucket . '/' . $row->accountID . '/';
						}
						$attachments[$uploadpath . $attachment_data['path']] = 'birthday-email.' . $attachment_data['ext'];
					}

					if ($this->crm_library->send_email($row->email, $subject, $email_html, $attachments, TRUE, $row->accountID, $this->settings_library->get('email_birthday_email_brand', $row->accountID))) {
						// update last year ecard sent
						$where = array(
							'childID' => $row->childID,
							'accountID' => $row->accountID
						);
						$data = array(
							'last_ecard_year' => date('Y')
						);
						$res_update = $this->db->update('family_children', $data, $where, 1);

						// save
						$data = array(
							'familyID' => $row->familyID,
							'contactID' => $row->contactID,
							'byID' => NULL,
							'type' => 'email',
							'destination' => $row->email,
							'subject' => $subject,
							'contentHTML' => $email_html,
							'contentText' => $email_plain,
							'status' => 'sent',
							'added' => mdate('%Y-%m-%d %H:%i:%s'),
							'modified' => mdate('%Y-%m-%d %H:%i:%s'),
							'accountID' => $row->accountID
						);

						$this->db->insert('family_notifications', $data);

						$sent++;
					}
				}
			}
		}

		if ($sent > 0) {
			echo $sent . "<br />";
		} else {
			echo "None<br />";
		}

	}

	/**
	 * create timesheets for this week, if don't exist
	 * @return boolean
	 */
	private function create_timesheets() {
		echo 'Create timesheets: ';

		$timesheets_created = 0;

		// get active accounts
		$where= array(
			'active' => 1
		);
		$accounts = $this->db->from('accounts')->where($where)->get();

		foreach ($accounts->result() as $account) {
			// check date
			if (date('N') == $this->settings_library->get('timesheets_create_day', $account->accountID)) {
				$timesheets_created = $this->crm_library->generate_timesheets(NULL, $account->accountID);
			}
		}

		echo $timesheets_created . '<br />';
	}

	/**
	 * submit timesheets for this week, if not submitted
	 * @return boolean
	 */
	private function submit_timesheets() {
		echo 'Submit timesheets: ';

		$timesheets_submitted = 0;

		// get active accounts
		$where= array(
			'active' => 1
		);
		$accounts = $this->db->from('accounts')->where($where)->get();

		foreach ($accounts->result() as $account) {
			// check date
			if (date('N') == $this->settings_library->get('timesheets_submit_day', $account->accountID)) {
				// get unsubmitted timesheets for previous weeks
				$where = array(
					'timesheets.status' => 'unsubmitted',
					'timesheets.date <' => date('Y-m-d', strtotime(date('Y') . 'W' . str_pad(date('W'), 2, '0', STR_PAD_LEFT))),
					'timesheets.accountID' => $account->accountID
				);
				$res = $this->db->from('timesheets')->where($where)->get();

				if ($res->num_rows() > 0) {
					foreach ($res->result() as $row) {
						// mark items as approved
						$data = array(
							'status' => 'approved',
							'approved' => mdate('%Y-%m-%d %H:%i:%s')
						);
						$where = array(
							'timesheetID' => $row->timesheetID,
							'accountID' => $account->accountID
						);
						$res_update = $this->db->update('timesheets_items', $data, $where);
						// mark expenses as approved
						$res_update = $this->db->update('timesheets_expenses', $data, $where);
						// mark timesheet as approved
						$res_update = $this->db->update('timesheets', $data, $where);
						$timesheets_submitted++;
					}
				}
			}
		}

		echo $timesheets_submitted . '<br />';
	}

	/**
	 * generate session evaluations
	 * @return void
	 */
	private function generate_session_evaluations() {

		echo "Generate session evaluations: ";
		$generated = 0;
		$interval = '-1 week';
		$existing_evaluations = array();
		$lesson_exceptions = array();

		// look up coaches generated for last interval
		$where = array(
			'bookings_lessons_notes.date >=' => mdate('%Y-%m-%d', strtotime($interval)), // exclude evaluations created more than an interval ago
			'bookings_lessons_notes.date <=' => mdate('%Y-%m-%d'), // exclude evaluations not yet started (shouldn't happen)
			'bookings_lessons_notes.type' => 'evaluation',
			'accounts.active' => 1
		);

		$evaluations = $this->db->select('bookings_lessons_notes.*')->from('bookings_lessons_notes')->join('accounts', 'bookings_lessons_notes.accountID = accounts.accountID', 'inner')->where($where)->get();

		if ($evaluations->num_rows() > 0) {
			foreach ($evaluations->result() as $row) {
				$existing_evaluations[$row->lessonID][$row->date][$row->byID] = $row->noteID;
			}
		}

		// look up exceptions for last interval
		$where = array(
			'bookings_lessons_exceptions.date >=' => mdate('%Y-%m-%d', strtotime($interval)), // exclude exceptions created more than an interval ago
			'bookings_lessons_exceptions.date <=' => mdate('%Y-%m-%d'), // exclude exceptions not yet started (shouldn't happen)
			'accounts.active' => 1
		);

		$exceptions = $this->db->select('bookings_lessons_exceptions.*')->from('bookings_lessons_exceptions')->join('accounts', 'bookings_lessons_exceptions.accountID = accounts.accountID', 'inner')->where($where)->get();

		if ($exceptions->num_rows() > 0) {
			foreach ($exceptions->result() as $row) {
				$lesson_exceptions[$row->lessonID][$row->date][] = array(
					'type' => $row->type,
					'fromID' => $row->fromID,
					'toID' => $row->staffID
				);
			}
		}

		// look up lessons
		$where = array(
			'bookings_blocks.endDate >=' => mdate('%Y-%m-%d', strtotime($interval)), // exclude blocked end more than an interval ago
			'bookings_blocks.startDate <=' => mdate('%Y-%m-%d'), // exclude blocks not yet started
			'bookings_lessons_staff.type' => 'head', // head coaches only
			'lesson_types.session_evaluations' => 1, // session type has evaluations turned on
			'accounts.active' => 1,
		);

		// check account or plan allows
		$where_or = '(`' . $this->db->dbprefix('accounts') . '`.`addon_session_evaluations` = 1 OR `' . $this->db->dbprefix('accounts_plans') . '`.`addons_all` = 1)';

		$sessions = $this->db->select('bookings_lessons.lessonID, bookings_lessons.bookingID, bookings_lessons.accountID, bookings_lessons.day, bookings_lessons.startTime, bookings_lessons.startDate as lesson_start, bookings_lessons.endDate as lesson_end, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end, bookings_lessons_staff.staffID, bookings_lessons_staff.startDate as staff_start, bookings_lessons_staff.endDate as staff_end')->from('bookings_lessons')->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')->join('bookings_lessons_staff', 'bookings_lessons.lessonID = bookings_lessons_staff.lessonID', 'inner')->join('lesson_types', 'bookings_lessons.typeID = lesson_types.typeID', 'inner')->join('accounts', 'bookings_lessons.accountID = accounts.accountID', 'inner')->join('accounts_plans', 'accounts.planID = accounts_plans.planID', 'inner')->where($where)->where($where_or, NULL, FALSE)->group_by('bookings_lessons_staff.recordID')->get();

		// if found
		if ($sessions->num_rows() > 0) {
			foreach ($sessions->result() as $row) {
				// use dates from block
				$start_date = $row->block_start;
				$end_date = $row->block_end;
				// if session date set and differs, use instead
				if (!empty($row->lesson_start) && strtotime($row->lesson_start) > strtotime($start_date)) {
					$start_date = $row->lesson_start;
				}
				if (!empty($row->lesson_end) && strtotime($row->lesson_end) < strtotime($end_date)) {
					$end_date = $row->lesson_end;
				}
				// if staff date set and differs, use instead
				if (!empty($row->staff_start) && strtotime($row->staff_start) > strtotime($start_date)) {
					$start_date = $row->staff_start;
				}
				if (!empty($row->staff_end) && strtotime($row->staff_end) < strtotime($end_date)) {
					$end_date = $row->staff_end;
				}
				// if start date more than interval ago, set to interval ago
				if (strtotime($start_date) < strtotime($interval)) {
					$start_date = date('Y-m-d', strtotime($interval));
				}
				// if end date more than now, set to now
				if (strtotime($end_date) > time()) {
					$end_date = date('Y-m-d');
				}

				// loop dates
				$begin = new DateTime($start_date);
				$end = new DateTime($end_date);
				for ($i = $begin; $i <= $end; $i->modify('+1 day')){
					$date = $i->format("Y-m-d");
					if (strtolower($i->format('l')) == $row->day) {
						// check time if today
						$process = TRUE;

						// check for exception
						if (isset($lesson_exceptions[$row->lessonID][$date])) {
							foreach ($lesson_exceptions[$row->lessonID][$date] as $exception) {
								switch ($exception['type']) {
									case 'cancellation':
										$process = FALSE;
										break;
									case 'staffchange':
										if ($row->staffID == $exception['fromID']) {
											$row->staffID = $exception['toID'];
										}
										break;
								}
							}
						}

						// check if already created
						if (isset($existing_evaluations[$row->lessonID][$date][$row->staffID])) {
							$process = FALSE;
						}

						// if today, check if session time in future
						if (strtotime($date . ' ' . $row->startTime) > time()) {
							$process = FALSE;
						}

						if ($process === TRUE) {
							// prepare data
							$data = array(
								'lessonID' => $row->lessonID,
								'bookingID' => $row->bookingID,
								'byID' => $row->staffID,
								'accountID' => $row->accountID,
								'date' => $date,
								'type' => 'evaluation',
								'content' => '',
								'status' => 'unsubmitted',
								'added' => mdate('%Y-%m-%d %H:%i:%s'),
								'modified' => mdate('%Y-%m-%d %H:%i:%s')
							);

							// insert
							$res = $this->db->insert('bookings_lessons_notes', $data);
							$generated++;
						}
					}
				}
			}
		}

		if ($generated > 0) {
			echo $generated . "<br />";
		} else {
			echo "None<br />";
		}

	}
}

/* End of file cron.php */
/* Location: ./application/controllers/cron.php */
