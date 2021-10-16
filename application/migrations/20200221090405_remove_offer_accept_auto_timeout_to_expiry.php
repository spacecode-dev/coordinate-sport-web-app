<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_offer_accept_auto_timeout_to_expiry extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$this->db->update(
				'settings',
				[
					'title' => 'Offer & Accept Expiry Time',
					'instruction' => 'This is the period of time before the offer expires.'
				],
				['key' => 'offer_accept_timeout_manual']
			);
		}

		public function down() {
			$this->db->update(
				'settings',
				[
					'title' => 'Offer & Accept Timeout',
					'instruction' => 'The hours of the session manual offer time out.'
				],
				['key' => 'offer_accept_timeout_manual']
			);
		}
}
