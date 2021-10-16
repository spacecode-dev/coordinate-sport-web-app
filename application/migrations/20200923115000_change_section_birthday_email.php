<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_section_birthday_email extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update settings
			$data = array(
				'tab' => 'participants',
				'description' => 'Edit the content of the email sent to participants on their birthday'
			);
			$where = array(
				'key' => 'birthday_email_emailsms'
			);
			$this->db->update('settings', $data, $where, 1);

		}

		public function down() {
			// update settings
			$data = array(
				'tab' => 'staff',
				'description' => 'Edit the content of the email sent to members of staff on their birthday'
			);
			$where = array(
				'key' => 'birthday_email_emailsms'
			);
			$this->db->update('settings', $data, $where, 1);
		}
}
