<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_additional_monitoring_field_options extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array();

		for ($i=1;$i<=20;$i++) {
			$fields["monitoring".$i."_entry_type"] = array(
					'type' => 'INT',
					'default' => 0,
					'null' => FALSE,
					'after' => 'monitoring'.$i
			);
			$fields['monitoring'.$i.'_mandatory'] = array(
				'type' => 'INT',
				'default' => 0,
				'null' => FALSE,
				'after' => 'monitoring'.$i.'_entry_type'
			);
		}

		$this->dbforge->add_column('bookings', $fields);
		//$this->dbforge->add_column('bookings_individuals_monitoring_old', $fields);
		//$this->dbforge->add_column('bookings_attendance_names', $fields);
		//$this->dbforge->add_column('bookings_cart_monitoring', $fields);
	}

	public function down() {
		// remove fields
		for ($i=1; $i<=20; $i++) {
			$this->dbforge->drop_column('bookings', 'monitoring'.$i.'_entry_type');
			$this->dbforge->drop_column('bookings', 'monitoring'.$i.'_mandatory');

			//$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring'.$i.'_entry_type');
			//$this->dbforge->drop_column('bookings_individuals_monitoring_old', 'monitoring'.$i.'_mandatory');

			//$this->dbforge->drop_column('bookings_attendance_names', 'monitoring'.$i.'_entry_type');
			//$this->dbforge->drop_column('bookings_attendance_names', 'monitoring'.$i.'_mandatory');

			//$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring'.$i.'_entry_type');
			//$this->dbforge->drop_column('bookings_cart_monitoring', 'monitoring'.$i.'_mandatory');
		}
	}
}
