<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_incorrect_booking_balances extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// remove time limit
			set_time_limit(0);

			// there is an issue with older bookings where sometimes the sessions total doesn't match the cart total, perhaps due to price changes or discounts which no longer exist
			$sql = "SELECT
				c.cartID,
				c.familyID,
				c.total,
				SUM(s.total) AS sessions_total
			FROM
				" . $this->db->dbprefix('bookings_cart') . " AS c
				INNER JOIN " . $this->db->dbprefix('bookings_cart_sessions') . " AS s ON c.cartID = s.cartID
			WHERE
				c.type = 'booking'
			GROUP BY
				c.cartID
			HAVING
				c.total != sessions_total";

			$res = $this->db->query($sql);

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// get first session
					$where = [
						'cartID' => $row->cartID
					];
					$res_session = $this->db->from('bookings_cart_sessions')
						->where($where)
						->order_by('added asc')
						->limit(1)
						->get();

					// apply adjustment fo first session
					if ($res_session->num_rows() > 0) {
						foreach ($res_session->result() as $first_session) {
							$data = [];
							$data['discount'] = $first_session->discount + ($row->sessions_total - $row->total);
							$data['total'] = $first_session->price - $data['discount'];
							$this->db->update('bookings_cart_sessions', $data, $where, 1);
						}
					}

					// update all sessions to be block priced so don't show wrong price
					$data = [
						'block_priced' => 1
					];
					$where = [
						'cartID' => $row->cartID
					];
					$this->db->update('bookings_cart_sessions', $data, $where);

					// recalc family balance
					$this->crm_library->recalc_family_balance($row->familyID);
				}
			}
		}

		public function down() {
			// no going back
		}
}
