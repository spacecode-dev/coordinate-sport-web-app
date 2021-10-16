<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_default_currency extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// define new settings
			$data = array(
				array(
					'key' => 'default_currency',
					'title' => 'Default Currency',
					'type' => 'select',
					'section' => 'general',
					'subsection' => 'general_general',
					'order' => 80,
					'value' => 'GBP',
					'options' => "AUD : AUD
EUR : EUR
GBP : GBP
USD : USD",
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				)
			);

			// bulk insert
			$this->db->insert_batch('settings', $data);

		}

		public function down() {

			// remove new settings
			$where_in = array(
				'default_currency'
			);
			$this->db->from('settings')->where_in('key', $where_in)->delete();
		}
}
