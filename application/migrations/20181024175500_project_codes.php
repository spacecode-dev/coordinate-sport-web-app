<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Project_codes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'codeID' => array(
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
                'code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => FALSE
                ),
                'desc' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
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
            $this->dbforge->add_key('codeID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('project_codes', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_codes') . '` ADD CONSTRAINT `fk_project_codes_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_codes') . '` ADD CONSTRAINT `fk_project_codes_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add project code ID to bookings
            $fields = array(
                'project_codeID' => array(
                    'type' => "INT",
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'project_typeID'
                )
            );
            $this->dbforge->add_column('bookings', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` ADD INDEX (`project_codeID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` ADD CONSTRAINT `fk_bookings_project_codeID` FOREIGN KEY (`project_codeID`) REFERENCES `' . $this->db->dbprefix('project_codes') . '`(`codeID`) ON DELETE NO ACTION ON UPDATE CASCADE');

        }

        public function down() {

            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_codes') . '` DROP FOREIGN KEY `fk_project_codes_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('project_codes') . '` DROP FOREIGN KEY `fk_project_codes_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings') . '` DROP FOREIGN KEY `fk_bookings_project_codeID`');

            // remove fields
            $this->dbforge->drop_column('bookings', 'project_codeID');

            // remove table, if exists
            $this->dbforge->drop_table('project_codes', TRUE);
        }
}
