<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_new_column_payment_receive extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'is_first_payment' => array(
				'type' => 'INT',
				'after' => 'note',
				'default' => 0
			)
		);
		$this->dbforge->add_column('family_payments', $fields);

		// add fields
		$fields = array(
			'cartID' => array(
				'type' => 'INT',
				'null' => TRUE,
				'after' => 'id'
			)
		);
		$this->dbforge->add_column('participant_subscriptions', $fields);

		// set foreign keys
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` ADD CONSTRAINT `fk_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE NO ACTION ON UPDATE CASCADE');

		// add fields
		$fields = array(
			'is_sub' => array(
				'type' => 'INT',
				'after' => 'sessionID',
				'default' => 0
			)
		);
		$this->dbforge->add_column('family_payments_sessions', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('family_payments', 'is_first_payment');

		//Remove foreign key
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` DROP FOREIGN KEY `fk_cartID`');
		$this->dbforge->drop_column('participant_subscriptions', 'cartID');

		// remove fields
		$this->dbforge->drop_column('family_payments_sessions', 'is_sub');
	}
}
