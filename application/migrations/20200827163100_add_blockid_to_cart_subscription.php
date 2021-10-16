<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_blockid_to_cart_subscription extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add new field
		$fields = array(
			'blockID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'after' => 'cartID'
			),
			'contactID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'after' => 'subID'
			),
			'bookingID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'after' => 'subID'
			)
		);
		$this->dbforge->add_column('bookings_cart_subscriptions', $fields);
	}

	public function down() {
		$this->dbforge->drop_column('bookings_cart_subscriptions', 'blockID');
		$this->dbforge->drop_column('bookings_cart_subscriptions', 'contactID');
		$this->dbforge->drop_column('bookings_cart_subscriptions', 'bookingID');
	}
}
