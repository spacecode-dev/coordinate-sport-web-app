<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Move_Booking_Confirmation_Setting extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// Move setting
		$this->db->where("key", "project_booking_confirmation_emailsms");
		$this->db->update("app_settings", array("tabpos" => '4'));
	}

	public function down() {
		// Revert setting move
		$this->db->where("key", "project_booking_confirmation_emailsms");
		$this->db->update("app_settings", array("tabpos" => '3'));
	}
}
