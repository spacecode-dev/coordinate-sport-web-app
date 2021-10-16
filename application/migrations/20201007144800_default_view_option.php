<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_default_view_option extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Add rows
			$data = array(
				'key' => 'default_view',
				'title' => 'Default View',
				'type' => 'select',
				'section' => 'general',
				'subsection' => 'bookings_site_general',
				'order' => 366,
				'options' => '1 : List
				2 : Calendar
				3 : Map',
				'value' => '1',
				'readonly' => '0',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->insert('settings', $data);

		}

		public function down() {
			// drop fields
			$where = array("key" => "default_view");
			
			$this->db->from('settings')->where($where)->delete();
		}
}
