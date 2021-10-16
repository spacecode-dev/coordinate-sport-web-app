<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_block_discounts extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify fields
		$fields = array(
			'discount_type' => array(
				'name' => 'discount_type',
				'type' => "ENUM('percentage','amount', 'block_amount')",
				'default' => 'percentage',
				'null' => FALSE,
				'after' => 'code'
			)
		);
		$this->dbforge->modify_column('vouchers', $fields);
		$this->dbforge->modify_column('bookings_vouchers', $fields);
	}

	public function down() {
		// modify fields
		$fields = array(
			'discount_type' => array(
				'name' => 'discount_type',
				'type' => "ENUM('percentage','amount')",
				'default' => 'percentage',
				'null' => FALSE,
				'after' => 'code'
			)
		);
		$this->dbforge->modify_column('vouchers', $fields);
		$this->dbforge->modify_column('bookings_vouchers', $fields);
	}
}
