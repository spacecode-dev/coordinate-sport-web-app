<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Participant_contact_source extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'source' => array(
                    'type' => "ENUM('Twitter', 'Facebook', 'Website', 'Email', 'SMS', 'Flyer', 'Newspaper', 'Poster', 'Referral', 'Existing Customer', 'Other')",
                    'null' => TRUE,
					'after' => 'relationship'
                ),
                'source_other' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
					'after' => 'source'
                ),
			);
			$this->dbforge->add_column('family_contacts', $fields);

			// copy existing source fields from bookings to contacts
			$processed = [];
			$where = [
				'type' => 'booking',
				'source IS NOT NULL' => NULL,
				'source !=' => ''
			];
			$res = $this->db
				->select('contactID, source, source_other')
				->from('bookings_cart')
				->where($where)
				->order_by('booked asc')
				->get();
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $booking) {
					// skip any already processed
					if (in_array($booking->contactID, $processed)) {
						continue;
					}
					$data = [
						'source' => $booking->source,
						'source_other' => $booking->source_other
					];
					$where = [
						'contactID' => $booking->contactID
					];
					$this->db->update('family_contacts', $data, $where, 1);
					// mark as processed
					$processed[] = $booking->contactID;
				}
			}
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('family_contacts', 'source');
			$this->dbforge->drop_column('family_contacts', 'source_other');
		}
}
