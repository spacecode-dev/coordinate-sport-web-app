<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Booking_For_Information extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		if (!$this->db->field_exists("booking_for","family_contacts")) {
			$fields = array(
				'booking_for' => array(
					'type' => "ENUM('child', 'contact', 'child_and_contact')",
					'after' => 'relationship',
					'default' => 'child_and_contact',
					'null' => TRUE
				),
			);

			$this->dbforge->add_column('family_contacts', $fields);
		}
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'booking_for');
	}
}
