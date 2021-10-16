<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Additional_bikeability_register_types extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers','names','bikeability', 'children_bikeability', 'individuals_bikeability')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // add field
            $fields = array(
                'bikeability_level' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 5,
                    'default' => NULL,
                    'after' => 'attended'
                )
            );
            $this->dbforge->add_column('bookings_individuals_sessions', $fields);

            // define fields - bikeability level
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'recordID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'childID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'bikeability_level' => array(
                    'type' => 'INT',
                    'constraint' => 1,
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
            $this->dbforge->add_key('recordID');
            $this->dbforge->add_key('bookingID');
            $this->dbforge->add_key('contactID');
            $this->dbforge->add_key('childID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_individuals_bikeability', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_recordID` FOREIGN KEY (`recordID`) REFERENCES `' . $this->db->dbprefix('bookings_individuals') . '`(`recordID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` ADD CONSTRAINT `fk_bookings_individuals_bikeability_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_recordID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_bookingID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_contactID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_childID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_individuals_bikeability') . '` DROP FOREIGN KEY `fk_bookings_individuals_bikeability_byID`');

            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers','names', 'bikeability')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // remove fields
            $this->dbforge->drop_column('bookings_individuals_sessions', 'bikeability_level');

            // remove tables, if exist
            $this->dbforge->drop_table('bookings_individuals_bikeability', TRUE);
        }
}