<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Privacy_settings_renames extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$names = [
				'participant_privacy' => 'Participant Terms & Privacy Policy',
				'participant_privacy_phone_script' => 'Participant Terms & Privacy Phone Script',
				'staff_privacy' => 'Staff Terms & Privacy Policy',
				'reconfirm_participant_privacy' => 'Reprompt Participant Terms & Privacy Policy',
				'reconfirm_staff_privacy' => 'Reprompt Staff Terms & Privacy Policy',
				'reconfirm_company_privacy' => 'Reprompt Company Terms & Privacy Policy',
				'company_privacy' => 'Company Terms & Privacy Policy'
			];
			foreach ($names as $key => $new_name) {
				$where = [
					'key' => $key
				];
				$data = [
					'title' => $new_name
				];
				$this->db->update('settings', $data, $where);
			}
		}

		public function down() {
			$names = [
				'participant_privacy' => 'Participant Privacy Policy',
				'participant_privacy_phone_script' => 'Participant Privacy Phone Script',
				'staff_privacy' => 'Staff Privacy Policy',
				'reconfirm_participant_privacy' => 'Reprompt Participant Privacy Policy',
				'reconfirm_staff_privacy' => 'Reprompt Staff Privacy Policy',
				'reconfirm_company_privacy' => 'Reprompt Company Privacy Policy',
				'company_privacy' => 'Company Privacy Policy'
			];
			foreach ($names as $key => $new_name) {
				$where = [
					'key' => $key
				];
				$data = [
					'title' => $new_name
				];
				$this->db->update('settings', $data, $where);
			}
		}
}
