<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_cart_subscriptions extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {

		// create table
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'accountID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'cartID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE
			),
			'subID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE
			),
			'childID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => FALSE,
			),
			'added' => array(
				'type' => 'DATETIME'
			),
			'modified' => array(
				'type' => 'DATETIME',
				'null' => TRUE
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('accountID');
		$this->dbforge->add_key('cartID');
		$this->dbforge->add_key('subID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('bookings_cart_subscriptions', FALSE, $attributes);

		// set foreign keys
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` ADD CONSTRAINT `fk_bookings_cart_subscriptions_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` ADD CONSTRAINT `fk_bookings_cart_subscriptions_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` ADD CONSTRAINT `fk_bookings_cart_subscriptions_subID` FOREIGN KEY (`subID`) REFERENCES `' . $this->db->dbprefix('subscriptions') . '`(`subID`) ON DELETE CASCADE ON UPDATE CASCADE');
	}

	public function down () {

		// remove foreign keys
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` DROP FOREIGN KEY `fk_bookings_cart_subscriptions_accountID`');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` DROP FOREIGN KEY `fk_bookings_cart_subscriptions_cartID`');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_subscriptions') . '` DROP FOREIGN KEY `fk_bookings_cart_subscriptions_subID`');

		// remove tables, if exist
		$this->dbforge->drop_table('bookings_cart_subscriptions', TRUE);
	}
}
