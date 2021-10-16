<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_monitoring_fields extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'monitoring11' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring10'
			),
			'monitoring12' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring11'
			),
			'monitoring13' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring12'
			),
			'monitoring14' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring13'
			),
			'monitoring15' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring14'
			),
			'monitoring16' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring15'
			),
			'monitoring17' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring16'
			),
			'monitoring18' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring17'
			),
			'monitoring19' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring18'
			),
			'monitoring20' => array(
				'type' => 'VARCHAR',
				'constraint' => 100,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring19'
			),
		);
		$this->dbforge->add_column('bookings', $fields);

		// add fields
		$fields = array(
			'monitoring11' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring10'
			),
			'monitoring12' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring11'
			),
			'monitoring13' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring12'
			),
			'monitoring14' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring13'
			),
			'monitoring15' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring14'
			),
			'monitoring16' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring15'
			),
			'monitoring17' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring16'
			),
			'monitoring18' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring17'
			),
			'monitoring19' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring18'
			),
			'monitoring20' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'monitoring19'
			),
		);
		$this->dbforge->add_column('bookings_individuals_monitoring_old', $fields);
		$this->dbforge->add_column('bookings_attendance_names', $fields);

		//add to cart monitoring table
		$fields = array(
			'monitoring11' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring10'
			),
			'monitoring12' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring11'
			),
			'monitoring13' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring12'
			),
			'monitoring14' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring13'
			),
			'monitoring15' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring14'
			),
			'monitoring16' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring15'
			),
			'monitoring17' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring16'
			),
			'monitoring18' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring17'
			),
			'monitoring19' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring18'
			),
			'monitoring20' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'after' => 'monitoring19'
			)
		);
		$this->dbforge->add_column('bookings_cart_monitoring', $fields);

	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('bookings', 'monitoring11');
		$this->dbforge->drop_column('bookings', 'monitoring12');
		$this->dbforge->drop_column('bookings', 'monitoring13');
		$this->dbforge->drop_column('bookings', 'monitoring14');
		$this->dbforge->drop_column('bookings', 'monitoring15');
		$this->dbforge->drop_column('bookings', 'monitoring16');
		$this->dbforge->drop_column('bookings', 'monitoring17');
		$this->dbforge->drop_column('bookings', 'monitoring18');
		$this->dbforge->drop_column('bookings', 'monitoring19');
		$this->dbforge->drop_column('bookings', 'monitoring20');

		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring11');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring12');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring13');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring14');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring15');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring16');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring17');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring18');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring19');
		$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring20');

		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring11');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring12');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring13');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring14');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring15');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring16');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring17');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring18');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring19');
		$this->dbforge->drop_column('bookings_attendance_names', 'monitoring20');

		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring11');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring12');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring13');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring14');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring15');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring16');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring17');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring18');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring19');
		$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring20');
	}
}
