<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Migrate_bookings extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();

		// increase timeout and memory limit
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
    }

    public function up() {
        // migrate
		$migrated_bookings = array();
		$batch_insert_data = array();

        // get old bookings
		$res = $this->db->select('bookings_individuals_old.*, bookings.register_type, bookings.type')
		->from('bookings_individuals_old')
		->join('bookings', 'bookings_individuals_old.bookingID = bookings.bookingID', 'inner')
		->get();

		if ($res->num_rows() > 0) {
			foreach ($res->result() as $row) {
				// if legacy booking, skip
				if (!empty($row->childID)) {
					continue;
				}
				$cart_data = array(
					'accountID' => $row->accountID,
					'familyID' => $row->familyID,
					'contactID' => $row->contactID,
					'childcarevoucher_providerID' => $row->childcarevoucher_providerID,
					'byID' => $row->byID,
					'migratedID' => $row->recordID,
					'type' => 'booking',
					'subtotal' => $row->subtotal,
					'discount' => $row->discount,
					'total' => $row->total,
					'balance' => $row->balance,
					'source' => $row->source,
					'source_other' => $row->source_other,
					'payment_reminder_before' => $row->payment_reminder_before,
					'payment_reminder_after' => $row->payment_reminder_after,
					'childcarevoucher_provider' => $row->childcarevoucher_provider,
					'childcarevoucher_ref' => $row->childcarevoucher_ref,
					'added' => $row->added,
					'booked' => $row->added,
					'modified' => $row->modified
				);
				$this->db->insert('bookings_cart', $cart_data);
				$cartID = $this->db->insert_id();
				$migrated_bookings[$row->recordID] = $cartID;

				// get register type
				$register_type = 'children';
				if (strpos($row->register_type, 'individuals') === 0) {
					$register_type = 'individuals';
				}

				// if has vouchers, insert into seperate table
				if (!empty($row->voucherID) || !empty($row->voucherID_global)) {
					$voucher_data = array(
						'accountID' => $row->accountID,
						'cartID' => $cartID,
						'voucherID' => $row->voucherID,
						'voucherID_global' => $row->voucherID_global,
						'byID' => $row->byID,
						'added' => $row->added,
						'modified' => $row->modified
					);
					$this->db->insert('bookings_cart_vouchers', $voucher_data);
				}

				// sessions
				$where = array(
					'bookings_individuals_sessions_old.recordID' => $row->recordID
				);
				$res_sessions = $this->db
				->select('bookings_individuals_sessions_old.*, bookings_lessons.blockID, bookings_lessons.day, bookings_blocks.startDate as block_start, bookings_blocks.endDate as block_end')
				->from('bookings_individuals_sessions_old')
				->join('bookings_lessons', 'bookings_individuals_sessions_old.lessonID = bookings_lessons.lessonID', 'inner')
				->join('bookings_blocks', 'bookings_lessons.blockID = bookings_blocks.blockID', 'inner')
				->where($where)
				->get();
				if ($res_sessions->num_rows() > 0) {
					foreach ($res_sessions->result_array() as $row_session) {
						$session_data = $row_session;
						unset($session_data['recordID']);
						$session_data['cartID'] = $cartID;
						$session_data['contactID'] = NULL;
						$session_data['balance'] = $session_data['total'];
						// if individual register, use contact ID from bookings table
						if ($register_type == 'individuals') {
							$session_data['contactID'] = $row->contactID;
						}
						// if no date, work out from block dates
						if (empty($session_data['date'])) {
							$date = $session_data['block_start'];
							$end_date = $session_data['block_end'];
							// loop through dates in block
							while (strtotime($date) <= strtotime($end_date)) {
								$day = strtolower(date('l', strtotime($date)));
								if ($day == $session_data['day']) {
									$session_data['date'] = $date;
									break;
								}
								$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
							}
						}
						unset($session_data['day']);
						unset($session_data['block_start']);
						unset($session_data['block_end']);
						$batch_insert_data[] = $session_data;
					}
				}
			}
		}

		if (count($migrated_bookings) > 0) {
			// insert sessions
			$this->db->insert_batch('bookings_cart_sessions', $batch_insert_data);

			// monitoring
			$batch_insert_data = array();
			$res = $this->db->from('bookings_individuals_monitoring_old')->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					if (!array_key_exists($row['recordID'], $migrated_bookings)) {
						continue;
					}
					$monitoring_data = $row;
					unset($monitoring_data['recordID']);
					$monitoring_data['cartID'] = $migrated_bookings[$row['recordID']];
					$batch_insert_data[] = $monitoring_data;
				}
				// bulk insert
				$this->db->insert_batch('bookings_cart_monitoring', $batch_insert_data);
			}

			// bikeability
			$batch_insert_data = array();
			$res = $this->db->from('bookings_individuals_bikeability_old')->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result_array() as $row) {
					if (!array_key_exists($row['recordID'], $migrated_bookings)) {
						continue;
					}
					$bikeability_data = $row;
					unset($bikeability_data['recordID']);
					$bikeability_data['cartID'] = $migrated_bookings[$row['recordID']];
					$batch_insert_data[] = $bikeability_data;
				}
				// bulk insert
				$this->db->insert_batch('bookings_cart_bikeability', $batch_insert_data);
			}
		}
    }

    public function down() {
		// delete migrated bookings
		$where = array(
			'migratedID !=' => NULL
		);
		$res = $this->db->delete('bookings_cart', $where);
    }
}
