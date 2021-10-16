<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_voucher_id_family_payments extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify fields
			$fields = array(
				'method' => array(
					'name' => 'method',
					'type' => "ENUM('card', 'cash', 'cheque', 'online', 'other', 'childcare voucher', 'direct debit', 'credit note', 'bacs')",
					'null' => FALSE
				)
			);
			$this->dbforge->modify_column('family_payments', $fields);

			// add fields
			$fields = array(
				'voucher_id' => array(
					'type' => 'INT',
					'default' => NULL,
					'after' => 'byID'
				)
			);
			$this->dbforge->add_column('family_payments', $fields);
		}

		public function down() {
			// modify fields
			$fields = array(
				'method' => array(
					'name' => 'method',
					'type' => "ENUM('card', 'cash', 'cheque', 'online', 'other', 'childcare voucher', 'direct debit', 'credit note')",
					'null' => FALSE
				)
			);
			$this->dbforge->modify_column('family_payments', $fields);

			// remove fields
			$this->dbforge->drop_column('family_payments', 'voucher_id');
		}
}
