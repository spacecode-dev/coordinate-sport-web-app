<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_all_subscription_payment_notes extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		$fields = array(
			'is_first_payment' => array(
				'type' => 'INT',
				'default' => 1
			)
		);
		$this->dbforge->modify_column('family_payments', $fields);

		// Get all payments of subscription events
		$where = '(note LIKE "%Direct Debit%" or note LIKE "%Online%")';
		$query = $this->db->from("family_payments")->where($where)->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$data = array("is_first_payment" => '1');
				if($result->note === "Direct Debit - Recurring Amount" || $result->note === "Online - Recurring Amount"){
					$data["is_first_payment"] = '0';
				}
				if($result->note === "Direct Debit - First Payment" || $result->note === "Online - First Payment"){
					$data["is_first_payment"] = '2';
				}
				$where = array("paymentID" => $result->paymentID);
				$this->db->update("family_payments", $data, $where, 1);
			}
		}
	}

	public function down() {
		// No need to revert because note data is still intact if required
	}
}
