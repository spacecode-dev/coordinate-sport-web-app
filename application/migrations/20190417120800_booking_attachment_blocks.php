<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booking_attachment_blocks extends CI_Migration {

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
                    'constraint' => 11
                ),
                'attachmentID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'blockID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('attachmentID');
            $this->dbforge->add_key('blockID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_attachments_blocks', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attachments_blocks') . '` ADD CONSTRAINT `fk_bookings_attachments_blocks_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attachments_blocks') . '` ADD CONSTRAINT `fk_bookings_attachments_blocks_attachmentID` FOREIGN KEY (`attachmentID`) REFERENCES `' . $this->db->dbprefix('bookings_attachments') . '`(`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attachments_blocks') . '` ADD CONSTRAINT `fk_bookings_attachments_blocks_blockID` FOREIGN KEY (`blockID`) REFERENCES `' . $this->db->dbprefix('bookings_blocks') . '`(`blockID`) ON DELETE CASCADE ON UPDATE CASCADE');

        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attachments_blocks') . '` DROP FOREIGN KEY `fk_bookings_attachments_blocks_attachmentID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attachments_blocks') . '` DROP FOREIGN KEY `fk_bookings_attachments_blocks_blockID`');

            // remove tables, if exist
            $this->dbforge->drop_table('bookings_attachments_blocks', TRUE);
        }
}
