<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_diversity_fields_to_settings_customisation extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		$this->db->where_in("section", array("account_holder","participant"));
		$this->db->where("field", "ethnic_origin");
		$this->db->update("settings_fields", array("label" => "Ethnicity"));

		$data = array(
			array(
				"section" => "account_holder",
				"field" => "disability",
				"label" => "Disability",
				"show" => 1,
				"required" => 0,
				"order" => 1028,
				"locked" => 0
			),
			array(
				"section" => "account_holder",
				"field" => "religion",
				"label" => "Religion",
				"show" => 1,
				"required" => 0,
				"order" => 1029,
				"locked" => 0
			),
			array(
				"section" => "account_holder",
				"field" => "sexual_orientation",
				"label" => "Sexual Orientation",
				"show" => 1,
				"required" => 0,
				"order" => 1030,
				"locked" => 0
			),
			array(
				"section" => "account_holder",
				"field" => "gender_since_birth",
				"label" => "Does your gender identity match your sex at birth?",
				"show" => 1,
				"required" => 0,
				"order" => 1031,
				"locked" => 0
			),
		);

		$this->db->insert_batch("settings_fields", $data);

		$data = array(
			array(
				"section" => "participant",
				"field" => "disability",
				"label" => "Disability",
				"show" => 1,
				"required" => 0,
				"order" => 1043,
				"locked" => 0
			),
			array(
				"section" => "participant",
				"field" => "religion",
				"label" => "Religion",
				"show" => 1,
				"required" => 0,
				"order" => 1044,
				"locked" => 0
			),
		);

		$this->db->insert_batch("settings_fields", $data);
	}

	public function down() {
		$this->db->where_in("section", array("account_holder","participant"));
		$this->db->where("field", "ethnic_origin");
		$this->db->update("settings_fields", array("label" => "Ethnic Origin"));

		$this->db->where_in("field", array("disability", "religion", "sexual_orientation", "gender_since_birth"));
		$this->db->where("section", "account_holder");
		$this->db->delete("settings_fields");

		$this->db->where_in("field", array("disability", "religion", "sexual_orientation", "gender_since_birth"));
		$this->db->where("section", "participant");
		$this->db->delete("settings_fields");
	}
}
