<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Permission_levels extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
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
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'department' => array(
                    'type' => "ENUM('coaching','office','management','directors','headcoach','fulltimecoach')",
                    'null' => FALSE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
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
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('permission_levels', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('permission_levels') . '` ADD CONSTRAINT `fk_permission_levels_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('permission_levels') . '` ADD CONSTRAINT `fk_permission_levels_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');


        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('permission_levels') . '` DROP FOREIGN KEY `fk_permission_levels_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('permission_levels') . '` DROP FOREIGN KEY `fk_permission_levels_byID`');

            // remove table, if exists
            $this->dbforge->drop_table('permission_levels', TRUE);
        }
}