<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_thanks_settings_block extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'thanksemail_sent' => array(
					'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'thanksemail_text'
				)
			);
			$this->dbforge->add_column('bookings', $fields);

			// add fields
			$fields = array(
				'thanksemail' => array(
					'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'max_age'
				),
				'thanksemail_text' => array(
					'type' => 'TEXT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'thanksemail'
				)
			);
			$this->dbforge->add_column('bookings_blocks', $fields);

			// move existing thanks email from bookings to blocks
			$res = $this->db->get('bookings');
			if ($res->num_rows() > 0) {
				foreach ($res->result() as $booking) {
					$data = [
						'thanksemail' => $booking->thanksemail,
						'thanksemail_text' => $booking->thanksemail_text
					];
					$where = [
						'bookingID' => $booking->bookingID
					];
					$this->db->update('bookings_blocks', $data, $where);
				}
			}

			// clear bookings settings
			$data = [
				'thanksemail' => 0,
				'thanksemail_text' => NULL
			];
			$this->db->update('bookings', $data);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('bookings', 'thanksemail_sent');
			$this->dbforge->drop_column('bookings_blocks', 'thanksemail');
			$this->dbforge->drop_column('bookings_blocks', 'thanksemail_text');
		}
}
