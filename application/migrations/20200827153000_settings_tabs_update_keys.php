<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_tabs_update_keys extends CI_Migration {

		private $tabs = [];

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			$this->tabs = [
				1 => 'general',
				2 => 'staff',
				3 => 'customers',
				4 => 'participants'
			];
		}

		public function up() {
			// add tab field
			$fields = array(
				'tab' => array(
					'type' => "VARCHAR",
					'constraint' => 30,
					'default' => NULL,
					'after' => 'options'
				)
			);
			$this->dbforge->add_column('settings', $fields);

			// update tabpos to tabs for email tabs
			foreach ($this->tabs as $tabpos => $tab) {
				$data = [
					'tab' => $tab
				];
				$where = [
					'tabpos' => $tabpos
				];
				$this->db->update('settings', $data, $where);
			}

			// remove tabpos field
			$this->dbforge->drop_column('settings', 'tabpos', TRUE);

			// add tabs for intergrations
			$data = [
				'tab' => 'keys'
			];
			$where = [
				'section' => 'integrations'
			];
			$this->db->update('settings', $data, $where);
		}

		public function down() {
			// add tab field
			$fields = array(
				'tabpos' => array(
					'type' => "ENUM('1','2','3','4')",
					'default' => NULL,
					'after' => 'options'
				)
			);
			$this->dbforge->add_column('settings', $fields);

			// update tabs to tabpos for email tabs
			foreach ($this->tabs as $tabpos => $tab) {
				$data = [
					'tabpos' => $tabpos
				];
				$where = [
					'tab' => $tab
				];
				$this->db->update('settings', $data, $where);
			}

			// remove tab field
			$this->dbforge->drop_column('settings', 'tab', TRUE);
		}
}
