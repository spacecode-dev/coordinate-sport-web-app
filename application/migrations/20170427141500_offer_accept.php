<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offer_accept extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'offer_accept_status' => array(
                    'type' => "ENUM('off','offering','assigned','exhausted','expired')",
                    'null' => FALSE,
                    'default' => 'off',
                    'after' => 'staff_required_head'
                ),
                'offer_accept_groupID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'offer_accept_status'
                ),
                'offer_accept_reason' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'offer_accept_groupID'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);

            // define fields - offer/accept groups
            $fields = array(
                'groupID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
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
            $this->dbforge->add_key('groupID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('offer_accept_groups', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept_groups') . '` ADD CONSTRAINT `fk_offer_accept_groups_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept_groups') . '` ADD CONSTRAINT `fk_offer_accept_groups_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // set foreign keys for bookings_lessons table
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` ADD CONSTRAINT `fk_bookings_lessons_offer_accept_groupID` FOREIGN KEY (`offer_accept_groupID`) REFERENCES `' . $this->db->dbprefix('offer_accept_groups') . '`(`groupID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // define fields - offer/accept staff
            $fields = array(
                'offerID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'lessonID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'type' => array(
                    'type' => "ENUM('head','lead','assistant')",
                    'null' => FALSE,
                ),
                'status' => array(
                    'type' => "ENUM('offered','accepted','declined','expired')",
                    'null' => FALSE,
                    'default' => 'offered'
                ),
                'reason' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                    'default' => NULL
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
            $this->dbforge->add_key('offerID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('lessonID');
            $this->dbforge->add_key('staffID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('offer_accept', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD CONSTRAINT `fk_offer_accept_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD CONSTRAINT `fk_offer_accept_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD CONSTRAINT `fk_offer_accept_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons') . '` DROP FOREIGN KEY `fk_bookings_lessons_offer_accept_groupID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept_groups') . '` DROP FOREIGN KEY `fk_offer_accept_groups_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept_groups') . '` DROP FOREIGN KEY `fk_offer_accept_groups_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` DROP FOREIGN KEY `fk_offer_accept_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` DROP FOREIGN KEY `fk_offer_accept_lessonID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` DROP FOREIGN KEY `fk_offer_accept_staffID`');

            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'offer_accept_status');
            $this->dbforge->drop_column('bookings_lessons', 'offer_accept_groupID');
            $this->dbforge->drop_column('bookings_lessons', 'offer_accept_reason');

            // remove tables, if exist
            $this->dbforge->drop_table('offer_accept_groups', TRUE);
            $this->dbforge->drop_table('offer_accept', TRUE);
        }
}