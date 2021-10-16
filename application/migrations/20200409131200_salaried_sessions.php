<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Salaried_sessions extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add field
		$fields = array(
			'salaried' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=> 0,
				'after' => 'checkout_email_sent'
			)
		);
		$this->dbforge->add_column('bookings_lessons_staff', $fields);

		// add field
		$fields = array(
			'required_features' => array(
				'type' => 'TEXT',
				'constraint' => 200,
				'null' => TRUE,
				'default'=> NULL,
				'after' => 'readonly'
			)
		);
		$this->dbforge->add_column('settings', $fields);

		// add setting
		$data = [
			'key' => 'salaried_sessions',
			'title' => 'Enable Salaried Sessions',
			'type' => 'checkbox',
			'section' => 'general',
			'subsection' => 'bookings_general',
			'order' => 2,
			'value' => 0,
			'required_features' => 'payroll',
			'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		];
		$this->db->insert('settings', $data);

	}

	public function down() {
		// remove setting
		$where = [
			'key' => 'salaried_sessions'
		];
		$this->db->delete('settings', $where, 1);

		// remove field
		$this->dbforge->drop_column('settings', 'required_features');

		// remove field
		$this->dbforge->drop_column('bookings_lessons_staff', 'salaried');
	}
}
