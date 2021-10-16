<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_refund_exception_cancel extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// define brand activities fields
			$fields = array(
				'ID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'auto_increment' => TRUE
				),
				'accountID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'exceptionID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'familyID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'amount' => array(
					'type' => 'DECIMAL(8,2)',
					'null' => FALSE
				)
			);
			$this->dbforge->add_field($fields);

			// add keys
			$this->dbforge->add_key('ID', TRUE);
			$this->dbforge->add_key('exceptionID');
			$this->dbforge->add_key('familyID');
			$this->dbforge->add_key('accountID');

			// set table attributes
			$attributes = array(
				'ENGINE' => 'InnoDB'
			);

			// create table
			$this->dbforge->create_table('bookings_lessons_exceptions_refund', FALSE, $attributes);

			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions_refund') . '` ADD CONSTRAINT `fk_bookings_lessons_exceptions_refund_exceptionID` FOREIGN KEY (`exceptionID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons_exceptions') . '` (`exceptionID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_bookings_lessons_exceptions_refund_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_bookings_lessons_exceptions_refund_familyID` FOREIGN KEY (`familyID`) REFERENCES `' . $this->db->dbprefix('family') . '`(`familyID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}

		public function down() {
			// remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions_refund') . '` DROP FOREIGN KEY `fk_bookings_lessons_exceptions_refund_exceptionID`');

			// remove columns added above
			$this->dbforge->drop_column('bookings_lessons_exceptions_refund', 'cartID', TRUE);
		}
}
