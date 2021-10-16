<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sibling_discount extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'siblingdiscount' => array(
				'type' => "TINYINT",
				'default' => 0,
				'null' => FALSE,
				'after' => 'comment'
			)
		);
		$this->dbforge->add_column('vouchers', $fields);
		$this->dbforge->add_column('bookings_vouchers', $fields);

		$fields = array(
			'siblingdiscount' => array(
				'type' => "ENUM('off','percentage','amount','fixed')",
				'default' => 'off',
				'null' => FALSE,
				'after' => 'autodiscount_amount'
			),
			'siblingdiscount_amount' => array(
				'type' => "DECIMAL(8,2)",
				'default' => NULL,
				'null' => TRUE,
				'after' => 'siblingdiscount'
			),
		);
		$this->dbforge->add_column('bookings', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('vouchers', 'siblingdiscount');
		$this->dbforge->drop_column('bookings_vouchers', 'siblingdiscount');
		$this->dbforge->drop_column('bookings', 'siblingdiscount');
		$this->dbforge->drop_column('bookings', 'siblingdiscount_amount');
	}
}
