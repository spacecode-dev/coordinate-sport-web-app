<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_refund_payment_option extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update field
			$fields = array(
				'method' => array(
					'type' => "enum('card','cash','cheque','online','other','childcare voucher','direct debit','credit note','bacs','refund')",
				)
			);
			$this->dbforge->modify_column('family_payments', $fields);
		}

		public function down() {
			$fields = array(
				'method' => array(
					'type' => "enum('card','cash','cheque','online','other','childcare voucher','direct debit','credit note','bacs')",
				)
			);
			$this->dbforge->modify_column('family_payments', $fields);
		}
}
