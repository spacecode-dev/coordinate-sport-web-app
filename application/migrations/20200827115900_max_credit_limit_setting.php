<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Max_credit_limit_setting extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// new setting
			$data = array(
				'key' => 'max_credit_limit',
				'title' => 'Maximum Credit Limit',
				'type' => 'number',
				'section' => 'general',
				'subsection' => 'credit_limits_general',
				'order' => 440,
				'value' => '',
				'instruction' => 'Staff users will not be able to set a credit limit for any participant customer over this amount',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->insert('settings', $data);
		}

		public function down() {
			// remove setting
			$this->db->delete('settings', array('key' => 'max_credit_limit'));
		}
}
