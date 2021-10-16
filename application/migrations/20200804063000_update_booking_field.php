<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_booking_field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify fields
		$fields = array(
			'register_type' => array(
				'name' => 'register_type',
				'type' => "ENUM('children', 'individuals', 'numbers', 'names', 'bikeability', 'children_bikeability', 'individuals_bikeability', 'shapeup', 'children_shapeup', 'individuals_shapeup', 'adults_children')",
				'default' => 'children',
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('bookings', $fields);

		//Add Field
		$fields = array(
			'profile_pic' => array(
				'type' => 'TEXT',
				'default' => NULL,
				'null' => TRUE,
				'after' => 'last_name'
			)
		);
		$this->dbforge->add_column('family_children', $fields);
		$this->dbforge->add_column('family_contacts', $fields);
	}

	public function down() {
		// modify fields
		$fields = array(
			'register_type' => array(
				'name' => 'register_type',
				'type' => "ENUM('children', 'individuals', 'numbers', 'names', 'bikeability', 'children_bikeability', 'individuals_bikeability', 'shapeup', 'children_shapeup', 'individuals_shapeup')",
				'default' => 'children',
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('bookings', $fields);

		// remove fields
		$this->dbforge->drop_column('family_children', 'profile_pic');
		$this->dbforge->drop_column('family_contacts', 'profile_pic');
	}
}
