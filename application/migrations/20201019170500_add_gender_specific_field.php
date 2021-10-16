<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Gender_Specific_Field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		if (!$this->db->field_exists("gender_specify","family_contacts")) {
			$fields = array(
				'gender_specify' => array(
					'type' => "text",
					'constraint' => 100,
					'after' => 'gender',
					'default' => NULL,
				)
			);

			$this->dbforge->add_column('family_contacts', $fields);

			$fields = array(
				'gender' => array(
					'type' => "ENUM('male', 'female', 'other', 'please_specify')",
				)
			);
			$this->dbforge->modify_column('family_contacts', $fields);
		}

		if (!$this->db->field_exists("gender_specify","family_children")) {
			$fields = array(
				'gender_specify' => array(
					'type' => "text",
					'constraint' => 100,
					'after' => 'gender',
					'default' => NULL,
				)
			);

			$this->dbforge->add_column('family_children', $fields);

			$fields = array(
				'gender' => array(
					'type' => "ENUM('male', 'female', 'other', 'please_specify')",
				)
			);
			$this->dbforge->modify_column('family_children', $fields);
		}
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'gender_specify');
		$this->dbforge->drop_column('family_children', 'gender_specify');


		$fields = array(
			'gender' => array(
				'type' => "ENUM('male', 'female', 'other')",
			)
		);
		$this->dbforge->modify_column('family_contacts', $fields);
		$this->dbforge->modify_column('family_children', $fields);
	}
}
