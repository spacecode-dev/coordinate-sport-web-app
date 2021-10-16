<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_Dashboard_Trigger_Section extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		$fields = array(
			'section' => array(
				'type' => "ENUM('bookings', 'staff', 'participants', 'safety', 'equipment')"
			)
		);
		$this->dbforge->modify_column('settings_dashboard', $fields);

		$this->db->where("key", "families_outstanding");
		$this->db->update("settings_dashboard", array("section" => 'participants'));
	}

	public function down() {
		$fields = array(
			'section' => array(
				'type' => "ENUM('bookings', 'staff', 'families', 'safety', 'equipment')"
			)
		);
		$this->dbforge->modify_column('settings_dashboard', $fields);

		$this->db->where("key", "families_outstanding");
		$this->db->update("settings_dashboard", array("section" => 'families'));
	}
}
