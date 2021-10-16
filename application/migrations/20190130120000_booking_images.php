<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booking_images extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // create table
            $fields = array(
                'imageID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
				'order' => array(
                    'type' => 'INT',
                    'constraint' => 3,
                    'null' => FALSE
                ),
                'path' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
                'type' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => FALSE
                ),
                'ext' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => FALSE
                ),
                'size' => array(
                    'type' => 'BIGINT',
                    'constraint' => 20,
                    'null' => FALSE
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
            $this->dbforge->add_key('imageID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('bookingID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_images', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` ADD CONSTRAINT `fk_bookings_images_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` ADD CONSTRAINT `fk_bookings_images_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` ADD CONSTRAINT `fk_bookings_images_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` DROP FOREIGN KEY `fk_bookings_images_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` DROP FOREIGN KEY `fk_bookings_images_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_images') . '` DROP FOREIGN KEY `fk_bookings_images_bookingID`');

            // remove tables, if exist
            $this->dbforge->drop_table('bookings_images', TRUE);
        }
}
