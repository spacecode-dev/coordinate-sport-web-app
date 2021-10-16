<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_credit_limit extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Change Grey Text
			
			$data = array(
				'instruction' => 'This is the default credit limit per participant customer. This can be altered by changing the credit limit in each participant profile.',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			
			$where = array(
				'key' => 'default_credit_limit'
			);
			
			$this->db->update("settings", $data, $where);
		}

		public function down() {
			// revert
			
			$data = array(
				'instruction' => 'Participants can\'t exceed this amount of debt when booking',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			
			$where = array(
				'key' => 'default_credit_limit'
			);
			
			$this->db->update("settings", $data, $where);
			
		}
}
