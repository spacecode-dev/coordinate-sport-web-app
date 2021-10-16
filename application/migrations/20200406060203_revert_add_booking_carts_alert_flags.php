<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Revert_add_booking_carts_alert_flags extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function down()
	{
		// add fields
		$fields = ['email_alert' => [
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => TRUE,
		],
			'reminder_alert' => [
				'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
				'null' => TRUE,
			],
		];
		$this->dbforge->add_column('bookings_cart', $fields);


	}

	public function up()
	{
		// remove fields
		$this->dbforge->drop_column('bookings_cart', 'email_alert');
		$this->dbforge->drop_column('bookings_cart', 'reminder_alert');
	}
}
