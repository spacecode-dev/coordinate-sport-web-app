<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Family_payments_sessions_table extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// create link table
			$fields = array(
				'linkID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'auto_increment' => TRUE
				),
				'accountID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'familyID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'paymentID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'sessionID' => array(
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
			$this->dbforge->add_key('linkID', TRUE);
			$this->dbforge->add_key('accountID');
			$this->dbforge->add_key('familyID');
			$this->dbforge->add_key('paymentID');
			$this->dbforge->add_key('sessionID');

			// set table attributes
			$attributes = array(
				'ENGINE' => 'InnoDB'
			);

			// create table
			$this->dbforge->create_table('family_payments_sessions', FALSE, $attributes);

			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_family_payments_sessions_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_family_payments_sessions_familyID` FOREIGN KEY (`familyID`) REFERENCES `' . $this->db->dbprefix('family') . '`(`familyID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_family_payments_sessions_paymentID` FOREIGN KEY (`paymentID`) REFERENCES `' . $this->db->dbprefix('family_payments') . '`(`paymentID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` ADD CONSTRAINT `fk_family_payments_sessions_sessionID` FOREIGN KEY (`sessionID`) REFERENCES `' . $this->db->dbprefix('bookings_cart_sessions') . '`(`sessionID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}

		public function down() {
			// remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` DROP FOREIGN KEY `fk_family_payments_sessions_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` DROP FOREIGN KEY `fk_family_payments_sessions_familyID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` DROP FOREIGN KEY `fk_family_payments_sessions_paymentID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_sessions') . '` DROP FOREIGN KEY `fk_family_payments_sessions_sessionID`');

			// remove tables, if exist
			$this->dbforge->drop_table('family_payments_sessions', TRUE);
		}
}
