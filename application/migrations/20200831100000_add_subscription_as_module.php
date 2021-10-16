<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_subscription_as_module extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add field to accounts
		$fields = array(
			'addon_online_booking_subscription_module' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => FALSE,
				'after' => 'addon_online_booking'
			)
		);
		$this->dbforge->add_column('accounts', $fields);

	}

	public function down() {
		// remove field from accounts, if exists
		if ($this->db->field_exists('addon_online_booking_subscription_module', 'accounts')) {
			$this->dbforge->drop_column('accounts', 'addon_online_booking_subscription_module');
		}
	}
}
