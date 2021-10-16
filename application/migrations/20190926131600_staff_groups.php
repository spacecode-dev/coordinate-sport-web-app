<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_groups extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
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
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'offer_type' => array(
                    'type' => "ENUM('order', 'all', 'auto')",
                    'default' => 'order'
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

            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            $this->dbforge->create_table('groups', FALSE, $attributes);

            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('groups') . '` ADD CONSTRAINT `fk_group_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // create staff mandatory quals table
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
                'groupID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'staffID' => array(
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
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('qualID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_groups', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` ADD CONSTRAINT `fk_staff_groups_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` ADD CONSTRAINT `fk_staff_groups_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` ADD CONSTRAINT `fk_staff_groups_groupID` FOREIGN KEY (`groupID`) REFERENCES `' . $this->db->dbprefix('groups') . '`(`groupID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('groups') . '` DROP FOREIGN KEY `fk_group_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` DROP FOREIGN KEY `fk_staff_groups_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` DROP FOREIGN KEY `fk_staff_groups_staffID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_groups') . '` DROP FOREIGN KEY `fk_staff_groups_groupID`');

            // remove tables, if exist
            $this->dbforge->drop_table('groups', TRUE);
            $this->dbforge->drop_table('staff_groups', TRUE);
        }
}