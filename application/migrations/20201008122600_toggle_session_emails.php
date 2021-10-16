<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_toggle_session_emails extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update setting
			$data = array(
				'toggle_fields' => 'send_staff_new_sessions,send_staff_cancelled_sessions,send_staff_changed_sessions'
			);
			$where = array(
				'key' => 'new_session_notifications_emailsms'
			);
			$this->db->update('settings', $data, $where, 1);

			// update setting
			$data = array(
				'subsection' => NULL
			);
			$where = array(
				'key' => 'send_staff_cancelled_sessions'
			);
			$this->db->update('settings', $data, $where, 1);

		}

		public function down() {
			// revert setting
			$data = array(
				'toggle_fields' => 'send_staff_new_sessions,send_staff_cancelled_sessions'
			);
			$where = array(
				'key' => 'new_session_notifications_emailsms'
			);
			$this->db->update('settings', $data, $where, 1);

			// revert setting
			$data = array(
				'subsection' => 'new_session_notifications_emailsms'
			);
			$where = array(
				'key' => 'send_staff_cancelled_sessions'
			);
			$this->db->update('settings', $data, $where, 1);
		}
}
