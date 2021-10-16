<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_checkouts extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // create table
            $fields = array(
                'logID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'lessonID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'date' => array(
                    'type' => 'DATE',
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
            $this->dbforge->add_key('logID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('lessonID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_lessons_checkouts', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` ADD CONSTRAINT `fk_bookings_lessons_checkouts_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` ADD CONSTRAINT `fk_bookings_lessons_checkouts_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` ADD CONSTRAINT `fk_bookings_lessons_checkouts_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` DROP FOREIGN KEY `fk_bookings_lessons_checkouts_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` DROP FOREIGN KEY `fk_bookings_lessons_checkouts_staffID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_checkouts') . '` DROP FOREIGN KEY `fk_bookings_lessons_checkouts_lessonID`');

            // remove tables, if exist
            $this->dbforge->drop_table('bookings_lessons_checkouts', TRUE);
        }
}