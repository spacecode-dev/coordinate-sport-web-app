<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Online_booking_search_settings extends CI_Migration {

		private $fields = array();

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
			// define fields
			$this->fields = array(
				array(
					'key' => 'onlinebooking_search_location',
					'title' => 'Online Booking - Show Location Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 60,
					'value' => '1',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'onlinebooking_search_age',
					'title' => 'Online Booking - Show Age Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 61,
					'value' => '1',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'onlinebooking_search_activity',
					'title' => 'Online Booking - Show Activity Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 62,
					'value' => '1',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'onlinebooking_search_type',
					'title' => 'Online Booking - Show Type Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 63,
					'value' => '1',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'onlinebooking_search_brand',
					'title' => 'Online Booking - Show Department Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 64,
					'value' => '0',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
			);
		}

		public function up() {
			// bulk insert
			$this->db->insert_batch('settings', $this->fields);
		}

		public function down() {
			// remove new settings
			$where_in = array();
			foreach ($this->fields as $field) {
				$where_in[] = $field['key'];
			}
			$this->db->from('settings')->where_in('key', $where_in)->delete();
		}
}
