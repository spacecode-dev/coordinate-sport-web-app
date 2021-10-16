<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_change_settings_field_label extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update settings
			$data = array(
				'label' => 'Target Salaried Hours'
			);
			$where = array(
				'field' => 'salaried_hours'
			);
			$this->db->update('settings_fields', $data, $where, 1);

		}

		public function down() {
			// update settings
			$data = array(
				'label' => 'Salaried Hours'
			);
			$where = array(
				'field' => 'salaried_hours'
			);
			$this->db->update('settings_fields', $data, $where, 1);
		}
}
