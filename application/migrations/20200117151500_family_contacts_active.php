<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Family_contacts_active extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1,
				'null' => FALSE,
				'after' => 'byID'
			)
		);
		$this->dbforge->add_column('family_contacts', $fields);
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'active');
	}
}
