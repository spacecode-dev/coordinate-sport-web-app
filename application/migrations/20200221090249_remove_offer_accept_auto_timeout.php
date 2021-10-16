<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_offer_accept_auto_timeout extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Accepted'],
				['key' => 'email_offer_accept_accepted_manual']
			);

			$this->db->delete('settings', [
				'key' => 'offer_accept_timeout'
			], 1);

			$this->db->update(
				'settings',
				['title' => 'Offer & Accept Timeout'],
				['key' => 'offer_accept_timeout_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer Session Subject'],
				['key' => 'email_offer_accept_offer_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer Session'],
				['key' => 'email_offer_accept_offer_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Send Notifications To'],
				['key' => 'email_offer_accept_notifications_to_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer Accepted Subject'],
				['key' => 'email_offer_accept_accepted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer Declined Subject'],
				['key' => 'email_offer_accept_declined_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offer Declined'],
				['key' => 'email_offer_accept_declined_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offers Declined By All Subject'],
				['key' => 'email_offer_accept_exhausted_subject_manual']
			);
			$this->db->update(
				'settings',
				['title' => 'Offers Declined By All'],
				['key' => 'email_offer_accept_exhausted_manual']
			);
		}

		public function down() {

			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) - Offer Accepted'],
				['key' => 'email_offer_accept_accepted_manual']
			);
			// add main section fields with empty subsection and toggle other checkbox
			$data = [
				'key' => 'offer_accept_timeout',
				'title' => 'Offer & Accept (Auto) Timeout',
				'type' => 'number',
				'section' => 'general',
				'subsection' => 'general_general',
				'order' => 71,
				'value' => 24,
				'instruction' => 'The hours of the session offer time out.',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
			];

			$this->db->insert('settings', $data);

			$this->db->update(
				'settings',
				['title' => 'Offer & Accept (Manual) Timeout'],
				['key' => 'offer_accept_timeout_manual']
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
		}
}
