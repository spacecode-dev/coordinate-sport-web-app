<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sign_in_page_title extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add setting
			$data = array(
				'key' => 'sign_in_page_title',
				'title' => 'Sign In Page Title',
				'type' => 'text',
				'section' => 'global',
				'order' => 1,
				'value' => "",
				'instruction' => 'Shown at the end of the page title on the sign in page. If not set, Company name will be shown',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->db->insert('settings', $data);
		}

		public function down() {

			// remove setting
			$where = array(
				'key' => 'sign_in_page_title'
			);
			$this->db->delete('settings', $where, 1);
		}
}
