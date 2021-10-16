<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Null_invalid_dobs extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$where = [
				'dob' => '0000-00-00'
			];
			$data = [
				'dob' =>NULL
			];
			$this->db->update('staff', $data, $where);
		}

		public function down() {
			// no going back
		}
}
