<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Recalc_family_payments_first_payment_error extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			// increase timeout and memory limit
			set_time_limit(0);
			ini_set('memory_limit', '1024M');
		}

		public function up() {
			// recalc families with outstanding balances editied after issue deployed
			$sql = "SELECT * FROM
			`" . $this->db->dbprefix('family') . "`
		INNER JOIN `" . $this->db->dbprefix('bookings_cart') . "` ON `" . $this->db->dbprefix('family') . "`.`familyID` = `" . $this->db->dbprefix('bookings_cart') . "`.`familyID`
		INNER JOIN `" . $this->db->dbprefix('family_payments') . "` ON `" . $this->db->dbprefix('family') . "`.`familyID` = `" . $this->db->dbprefix('family_payments') . "`.`familyID`
		WHERE
		   (`" . $this->db->dbprefix('family_payments') . "`.`added` >= '2020-11-24 18:26:00'
		   OR `" . $this->db->dbprefix('family_payments') . "`.`modified` >= '2020-11-24 18:26:00'
		   OR `" . $this->db->dbprefix('bookings_cart') . "`.`added` >= '2020-11-24 18:26:00'
		   OR `" . $this->db->dbprefix('bookings_cart') . "`.`modified` >= '2020-11-24 18:26:00'
			OR `" . $this->db->dbprefix('bookings_cart') . "`.`booked` >= '2020-11-24 18:26:00')
			AND `" . $this->db->dbprefix('family') . "`.`account_balance` < 0
		   GROUP BY `" . $this->db->dbprefix('family') . "`.`familyID`";

			$res = $this->db->query($sql);

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
