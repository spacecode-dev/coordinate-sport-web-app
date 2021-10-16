<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Participant_Religion_Field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		if (!$this->db->field_exists("religion_specify","family_contacts")) {
			$fields = array(
				'religion' => array(
					'type' => "ENUM('christianity', 'hinduism', 'jewish_judaism', 'islam', 'sikhism', 'no_religion', 'prefer_not_to_say', 'please_specify')",
					'after' => 'gender_specify',
					'default' => NULL,
					'null' => TRUE
				),
				'religion_specify' => array(
					'type' => "text",
					'constraint' => 100,
					'after' => 'religion',
					'default' => NULL,
				)
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}

		if (!$this->db->field_exists("religion_specify","family_children")) {
			$fields = array(
				'religion' => array(
					'type' => "ENUM('christianity', 'hinduism', 'jewish_judaism', 'islam', 'sikhism', 'no_religion', 'prefer_not_to_say', 'please_specify')",
					'after' => 'gender_specify',
					'default' => NULL,
					'null' => TRUE
				),
				'religion_specify' => array(
					'type' => "text",
					'constraint' => 100,
					'after' => 'religion',
					'default' => NULL,
				)
			);

			$this->dbforge->add_column('family_children', $fields);
		}
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'religion');
		$this->dbforge->drop_column('family_contacts', 'religion_specify');

		$this->dbforge->drop_column('family_children', 'religion');
		$this->dbforge->drop_column('family_children', 'religion_specify');
	}
}
