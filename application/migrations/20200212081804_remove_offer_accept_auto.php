<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_offer_accept_auto extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept Emails'],
				['key' => 'offer_accept_manual_emails_emailsms']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Session Subject'],
				['key' => 'email_offer_accept_offer_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Session'],
				['key' => 'email_offer_accept_offer_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Send Notifications To'],
				['key' => 'email_offer_accept_notifications_to_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Accepted Subject'],
				['key' => 'email_offer_accept_accepted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Declined Subject'],
				['key' => 'email_offer_accept_declined_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Declined'],
				['key' => 'email_offer_accept_declined_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offers Declined By All Subject'],
				['key' => 'email_offer_accept_exhausted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offers Declined By All'],
				['key' => 'email_offer_accept_exhausted_manual']
			);

			$this->db->delete('settings', ['key' => 'offer_accept_emails_emailsms'], 1);
		}

		public function down() {

			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) Emails'],
				['key' => 'offer_accept_manual_emails_emailsms']
			);

			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Session Subject'],
				['key' => 'email_offer_accept_offer_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Session'],
				['key' => 'email_offer_accept_offer_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Send Notifications To'],
				['key' => 'email_offer_accept_notifications_to_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Accepted Subject'],
				['key' => 'email_offer_accept_accepted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Declined Subject'],
				['key' => 'email_offer_accept_declined_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Declined'],
				['key' => 'email_offer_accept_declined_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offers Declined By All Subject'],
				['key' => 'email_offer_accept_exhausted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offers Declined By All'],
				['key' => 'email_offer_accept_exhausted_manual']
			);
			// define new settings
			$data = [
				[
					'key' => 'offer_accept_emails_emailsms',
					'title' => 'Offer & Accept (Auto) Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 15,
					'toggle_fields' => 'send_offer_accept_emails',
					'value' => 0,
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				]
			];
			$this->db->insert_batch('settings', $data);
		}
}
