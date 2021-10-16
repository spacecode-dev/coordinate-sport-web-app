<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_show_customer_in_session_staff extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Add Row
			$data = array(
			'key' => 'show_customer_in_session_staff',
			'title' => 'Show Customer Contact to Session Staff',
			'type' => 'checkbox',
			'section' => 'general',
			'subsection' => 'bookings_general',
			'order' => 5,
			'value' => 1,
			'created_at' => mdate('%Y-%m-%d %H:%i:%s'));
			
			$this->db->insert('settings', $data);
		}

		public function down() {
			$where = array('key' => 'show_customer_in_session_staff');
			
			$this->db->delete('settings', $where, 1);
		}
}
