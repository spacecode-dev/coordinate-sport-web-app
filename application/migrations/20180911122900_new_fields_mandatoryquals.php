<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_fields_mandatoryquals extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify settings fields
			$fields = array(
				'hourly_rate' => array(
					'type' => 'DECIMAL',
					'constraint' => '10,2',
					'default' => 0,
				),
				'length_increment' => array(
					'type' => 'INT',
					'default' => 0,
				),
				'incremental_rate' => array(
					'type' => 'DECIMAL',
					'constraint' => '10,2',
					'default' => 0,
				)
			);
			$this->dbforge->add_column('mandatory_quals', $fields);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('mandatory_quals', 'hourly_rate');
			$this->dbforge->drop_column('mandatory_quals', 'length_increment');
			$this->dbforge->drop_column('mandatory_quals', 'incremental_rate');
		}
}