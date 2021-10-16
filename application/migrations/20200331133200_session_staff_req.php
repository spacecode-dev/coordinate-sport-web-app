<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_staff_req extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		// set defaults to 0
		$fields = array(
			'staff_required_head' => array(
				'name' => 'staff_required_head',
				'type' => "TINYINT",
				'constraint' => 3,
				'default' => 0,
				'null' => FALSE
			),
			'staff_required_lead' => array(
				'name' => 'staff_required_lead',
				'type' => "TINYINT",
				'constraint' => 3,
				'default' => 0,
				'null' => FALSE
			),
			'staff_required_participant' => array(
				'name' => 'staff_required_participant',
				'type' => "TINYINT",
				'constraint' => 3,
				'default' => 0,
				'null' => FALSE
			),
			'staff_required_observer' => array(
				'name' => 'staff_required_observer',
				'type' => "TINYINT",
				'constraint' => 3,
				'default' => 0,
				'null' => FALSE
			),
			'staff_required_assistant' => array(
				'name' => 'staff_required_assistant',
				'type' => "TINYINT",
				'constraint' => 3,
				'default' => 0,
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('bookings_lessons', $fields);
	}

	public function down()
	{
		// no going back
	}
}
