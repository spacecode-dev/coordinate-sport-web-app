<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_set_first_payments extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			// increase timeout and memory limit
			set_time_limit(0);
			ini_set('memory_limit', '1024M');
		}

		public function up() {
			// update non-recurring payments to is_first_payment = 1 before date of deployment of this functionality
			$sql = "UPDATE
				`" . $this->db->dbprefix('family_payments') . "`
			SET
				`is_first_payment` = 1
			WHERE
				(
					`note` NOT LIKE '%Recurring Amount'
					OR `note` IS NULL
				)
				AND `is_first_payment` = 0
				AND `added` <= '2020-11-24 18:26:00'";

			$res = $this->db->query($sql);

			// recalc families with outstanding balances
			$where = [
				'account_balance <' => 0
			];
			$res = $this->db->select('familyID')
				->from('family')
				->where($where)
				->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$this->crm_library->recalc_family_balance($row->familyID);
				}
			}
		}

		public function down() {
			// no going back
		}
}
