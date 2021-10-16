<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tagged_bookings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // create booking/tags link table
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
                'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'tagID' => array(
                    'type' => 'INT',
                    'constraint' => 11
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
            $this->dbforge->add_key('linkID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('booking');
            $this->dbforge->add_key('tagID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_tags', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_tags') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_tags') . '` ADD FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_tags') . '` ADD CONSTRAINT `fk_bookings_tags_tagID` FOREIGN KEY (`tagID`) REFERENCES `' . $this->db->dbprefix('settings_tags') . '`(`tagID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_tags') . '` DROP FOREIGN KEY `fk_bookings_tags_tagID`');

            // remove tables, if exist
            $this->dbforge->drop_table('bookings_tags', TRUE);
        }
}