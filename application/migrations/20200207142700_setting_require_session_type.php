<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_require_session_type extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// add new section
			$data = [
				'key' => 'bookings_general',
				'title' => 'Bookings',
				'type' => 'checkbox',
				'section' => 'general-main',
				'order' => 2,
				'value' => 0,
				'toggle_fields' => '',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$this->db->insert('settings', $data);

			// add setting
			$data = [
				'key' => 'require_session_type',
				'title' => 'Session Type Required',
				'type' => 'checkbox',
				'section' => 'general',
				'subsection' => 'bookings_general',
				'order' => 1,
				'value' => 1,
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
			];
			$this->db->insert('settings', $data);
		}

		public function down() {
			$this->db->delete('settings', [
				'key' => 'require_session_type'
			], 1);
			$this->db->delete('settings', [
				'key' => 'bookings_general'
			], 1);
		}
}
