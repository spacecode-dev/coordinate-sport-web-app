<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_refund_exception_payment_id extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$fields = array(
				'paymentID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'after' => 'exceptionID'
				)
			);
			$this->dbforge->add_column('bookings_lessons_exceptions_refund', $fields);

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions_refund') . '` ADD CONSTRAINT `fk_bookings_lessons_exceptions_refund_paymentID` FOREIGN KEY (`paymentID`) REFERENCES `' . $this->db->dbprefix('family_payments') . '`(`paymentID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}

		public function down() {
			// remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions_refund') . '` DROP FOREIGN KEY `fk_bookings_lessons_exceptions_refund_paymentID`');

			// remove columns added above
			$this->dbforge->drop_column('bookings_lessons_exceptions_refund', 'paymentID', TRUE);
		}
}
