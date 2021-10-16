<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_online_search_name extends CI_Migration {

		private $fields = array();

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
			// define fields
			$this->fields = array(
				array(
					'key' => 'onlinebooking_search_name',
					'title' => 'Online Booking - Show Name Search',
					'type' => 'checkbox',
					'section' => 'styling',
					'order' => 65,
					'value' => '1',
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
