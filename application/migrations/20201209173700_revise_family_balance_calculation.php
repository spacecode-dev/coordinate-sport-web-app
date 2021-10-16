<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_revise_family_balance_calculation extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();

		// increase timeout and memory limit
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
	}

	public function up() {

		// rename completed to expired
		$fields = array(
			'is_first_payment' => array(
				'type' => 'INT',
				'default' => 1,
				'comment' => ' 0 - Recurring subscription payments (excluding first payment of subscription), 1 - all non-subscription payment, 2 - Subscription First Payment '
			)
		);
		$this->dbforge->modify_column('family_payments', $fields);
	}

	public function down() {
		// No need to revert because note data is still intact if required
	}
}
