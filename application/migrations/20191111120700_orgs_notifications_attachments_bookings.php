<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Orgs_notifications_attachments_bookings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // createlink table
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
                'notificationID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'attachmentID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('linkID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('notificationID');
			$this->dbforge->add_key('attachmentID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('orgs_notifications_attachments_bookings', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_bookings_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_bookings_notificationID` FOREIGN KEY (`notificationID`) REFERENCES `' . $this->db->dbprefix('orgs_notifications') . '`(`notificationID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_bookings_attachmentID` FOREIGN KEY (`attachmentID`) REFERENCES `' . $this->db->dbprefix('bookings_attachments') . '`(`attachmentID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_bookings_accountID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_bookings_notificationID`');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments_bookings') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_bookings_attachmentID`');

            // remove tables, if exist
            $this->dbforge->drop_table('orgs_notifications_attachments_bookings', TRUE);
        }
}
