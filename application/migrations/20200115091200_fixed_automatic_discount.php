<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fixed_automatic_discount extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update field
			$fields = array(
				'autodiscount' => array(
					'name' => 'autodiscount',
					'type' => "ENUM('off','percentage','amount','fixed')",
					'default' => 'off',
					'null' => FALSE
				)
			);
			$this->dbforge->modify_column('bookings', $fields);
		}

		public function down() {
			// update field
			$fields = array(
				'autodiscount' => array(
					'name' => 'autodiscount',
					'type' => "ENUM('off','percentage','amount')",
					'default' => 'off',
					'null' => FALSE
				)
			);
			$this->dbforge->modify_column('bookings', $fields);
		}
}
