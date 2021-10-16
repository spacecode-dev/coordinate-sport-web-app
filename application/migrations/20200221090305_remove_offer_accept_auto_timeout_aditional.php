<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_offer_accept_auto_timeout_aditional extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$this->db->update(
				'settings',
				['title' => 'Offer Accepted'],
				['key' => 'email_offer_accept_accepted_manual']
			);
		}

		public function down() {
			$this->db->update(
				'settings',
				['title' => 'Offer & Accept - Offer Accepted'],
				['key' => 'email_offer_accept_accepted_manual']
			);
		}
}
