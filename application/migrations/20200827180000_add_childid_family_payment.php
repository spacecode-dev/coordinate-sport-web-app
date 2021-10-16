<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_childid_family_payment extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {

		//Add childID column
		$fields = array(
			'childID' => array(
				'type' => "INT",
				'constraint' => 11,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'contactID'
			)
		);
		$this->dbforge->add_column('family_payments', $fields);
	}

	public function down() {

		// remove column added above
		$this->dbforge->drop_column('family_payments', 'childID', TRUE);
	}
}
