<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Participant_Orientation_Field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		if (!$this->db->field_exists("sexual_orientation","family_contacts")) {
			$fields = array(
				'sexual_orientation' => array(
					'type' => "ENUM('bisexual', 'gay_man', 'gay_woman_lesbian', 'heterosexual', 'prefer_not_to_say', 'please_specify')",
					'after' => 'religion_specify',
					'default' => NULL,
					'null' => TRUE
				),
				'sexual_orientation_specify' => array(
					'type' => "text",
					'constraint' => 100,
					'after' => 'sexual_orientation',
					'default' => NULL,
				),
				'gender_since_birth' => array(
					'type' => "ENUM('yes', 'no', 'prefer_not_to_say')",
					'after' => 'gender_specify',
					'default' => NULL,
					'null' => TRUE
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'sexual_orientation');
		$this->dbforge->drop_column('family_contacts', 'gender_since_birth');
	}
}
