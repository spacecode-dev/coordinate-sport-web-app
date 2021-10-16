<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Cart_vouchers_tables extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// create table
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'cartID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
				'voucherID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'voucherID_global' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
				'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('cartID');
			$this->dbforge->add_key('voucherID');
			$this->dbforge->add_key('voucherID_global');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_cart_vouchers', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_vouchers') . '` ADD CONSTRAINT `fk_bookings_cart_vouchers_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_vouchers') . '` ADD CONSTRAINT `fk_bookings_cart_vouchers_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_vouchers') . '` ADD CONSTRAINT `fk_bookings_cart_vouchers_voucherID` FOREIGN KEY (`voucherID`) REFERENCES `' . $this->db->dbprefix('bookings_vouchers') . '`(`voucherID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_vouchers') . '` ADD CONSTRAINT `fk_bookings_cart_vouchers_voucherID_global` FOREIGN KEY (`voucherID_global`) REFERENCES `' . $this->db->dbprefix('vouchers') . '`(`voucherID`) ON DELETE NO ACTION ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_vouchers') . '` ADD CONSTRAINT `fk_bookings_cart_vouchers_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_cartID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_voucherID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_voucherID_global`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart_bikeability') . '` DROP FOREIGN KEY `fk_bookings_cart_bikeability_byID`');

            // remove tables, if exist
			$this->dbforge->drop_table('bookings_cart_vouchers', TRUE);
        }
}
