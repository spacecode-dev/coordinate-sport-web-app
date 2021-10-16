<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_staff_fields extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify settings fields
			$fields = array(
				'system_pay_rates' => array(
					'type' => 'BOOLEAN',
					'after' => 'payments_scale_salary',
					'default' => 0
				),
				'hourly_rate' => array(
					'type' => 'DECIMAL',
					'after' => 'system_pay_rates',
					'constraint' => '10,2',
					'default' => 0
				)
			);
			$this->dbforge->add_column('staff', $fields);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('staff', 'system_pay_rates');
			$this->dbforge->drop_column('staff', 'hourly_rate');
		}
}