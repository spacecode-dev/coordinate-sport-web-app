<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_staff_availability_exception_fields extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// rename reason field to note
			$fields = array(
				'reason' => array(
					'name' => 'note',
					'type' => 'VARCHAR(255)',
					'null' => TRUE,
					'default' => NULL
				)
			);
			$this->dbforge->modify_column('staff_availability_exceptions', $fields);

			// add new fields reason and attachment
			$fields = array(
				'reason' => array(
					'type' => "ENUM('holiday', 'appointment', 'sick leave', 'special leave', 'unavailable', 'other')",
					'default' => NULL,
					'null' => TRUE,
					'after' => 'type'
				),
				'attachment_name' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE,
					'default' => NULL,
					'after' => 'note'
				),
				'path' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE,
					'default' => NULL,
					'after' => 'note'
				),
				'file_type' => array(
					'type' => 'VARCHAR',
					'constraint' => 100,
					'null' => TRUE,
					'default' => NULL,
					'after' => 'note'
				),
				'ext' => array(
					'type' => 'VARCHAR',
					'constraint' => 10,
					'null' => TRUE,
					'default' => NULL,
					'after' => 'note'
				),
				'size' => array(
					'type' => 'BIGINT',
					'constraint' => 20,
					'null' => TRUE,
					'default' => NULL,
					'after' => 'note'
				),
			);
			$this->dbforge->add_column('staff_availability_exceptions', $fields);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('staff_availability_exceptions', 'reason');
			$this->dbforge->drop_column('staff_availability_exceptions', 'attachment_name');
			$this->dbforge->drop_column('staff_availability_exceptions', 'path');
			$this->dbforge->drop_column('staff_availability_exceptions', 'type');
			$this->dbforge->drop_column('staff_availability_exceptions', 'ext');
			$this->dbforge->drop_column('staff_availability_exceptions', 'size');

			// rename note field to reason
			$fields = array(
				'note' => array(
					'name' => 'reason'
				)
			);
			$this->dbforge->modify_column('staff_availability_exceptions', $fields);
		}
}
