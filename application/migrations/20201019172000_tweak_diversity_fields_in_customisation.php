<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_tweak_diversity_fields_in_customisation extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		//Change default state of new diversity fields (hidden by default)
		$this->db->where_in("section", array("account_holder","participant"));
		$this->db->where_in("field", array("religion", "sexual_orientation","gender_since_birth"));
		$this->db->update("settings_fields", array("show" => 0));

		//Add instruction to terms and privacy fields
		$this->db->where_in("key", array("participant_data_protection_notice","participant_safeguarding"));
		$this->db->update("settings", array("instruction" => "If set, participants will be asked to read and agree to this on their next login"));
	}

	public function down() {
		$this->db->where_in("section", array("account_holder","participant"));
		$this->db->where_in("field", array("religion", "sexual_orientation","gender_since_birth"));
		$this->db->update("settings_fields", array("show" => 1));

		$this->db->where_in("key", array("participant_data_protection_notice","participant_safeguarding"));
		$this->db->update("settings", array("instruction" => ""));
	}
}
