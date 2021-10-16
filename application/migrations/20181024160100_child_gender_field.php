<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Child_gender_field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify fields
		$fields = array(
			'gender' => array(
				'type' => "ENUM('male', 'female', 'other')",
				'after' => 'last_name',
				'default' => NULL
			)
		);
		$this->dbforge->add_column('family_children', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('family_children', 'gender');
	}
}
