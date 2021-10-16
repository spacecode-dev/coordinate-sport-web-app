<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_cleanup_multiple_carts extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$res = $this->db
				->select('cartID, contactID')
				->from('bookings_cart')
				->where(['type' => 'cart'])
				->group_by('accountID, contactID, familyID')
				->having('COUNT(cartID) > 1')
				->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					// delete any empty carts for this contact
					$where = [
						'type' => 'cart',
						'contactID' => $row->contactID,
						'subtotal' => 0
					];
					$this->db->delete('bookings_cart', $where);

					// check how many carts left in case multiple carts with items
					unset($where['subtotal']);
					$res_check = $this->db->select('cartID')
						->from('bookings_cart')
						->where($where)
						->get();

					// if more than 1, delete others
					if ($res_check->num_rows() > 1) {
						$to_delete = $res_check->num_rows() - 1;
						$this->db->delete('bookings_cart', $where, $to_delete);
					}
				}
			}
			return false;

		}

		public function down() {
			// no going back
		}
}
