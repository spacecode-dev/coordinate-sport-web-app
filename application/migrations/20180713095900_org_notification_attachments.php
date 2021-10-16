<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Org_notification_attachments extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // create table
            $fields = array(
                'attachmentID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE
                ),
                'notificationID' => array(
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
            $this->dbforge->add_key('attachmentID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('notificationID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('orgs_notifications_attachments', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_notificationID` FOREIGN KEY (`notificationID`) REFERENCES `' . $this->db->dbprefix('orgs_notifications') . '`(`notificationID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` ADD CONSTRAINT `fk_orgs_notifications_attachments_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_notifications_attachments') . '` DROP FOREIGN KEY `fk_orgs_notifications_attachments_notificationID`');

            // remove tables, if exist
            $this->dbforge->drop_table('orgs_notifications_attachments', TRUE);
        }
}