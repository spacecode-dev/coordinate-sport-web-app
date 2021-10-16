<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_booking_id_family_notifications extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'bookingID' => array(
				'type' => 'INT',
				'null' => TRUE,
				'after' => 'byID'
			)
		);
		$this->dbforge->add_column('family_notifications', $fields);

		// set foreign keys
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_notifications') . '` ADD CONSTRAINT `fk_family_notifications_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');

	}

	public function down() {
		// remove foreign keys
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_notifications') . '` DROP FOREIGN KEY `fk_family_notifications_bookingID`');
		// remove fields
		$this->dbforge->drop_column('family_notifications', 'bookingID');
	}
}
