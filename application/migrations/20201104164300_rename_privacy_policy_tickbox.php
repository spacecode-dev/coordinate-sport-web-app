<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rename_privacy_policy_tickbox extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Rename Privacy policy tick box
			$data = array(
				'title' => 'Reprompt Participant Privacy Policy and Marketing Consent',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
				'key' => 'reconfirm_participant_privacy'
			])->update('settings', $data);
		}

		public function down() {
			$data = array(
				'title' => 'Reprompt Participant Terms & Privacy Policy',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
				'key' => 'reconfirm_participant_privacy'
			])->update('settings', $data);

		}
}
