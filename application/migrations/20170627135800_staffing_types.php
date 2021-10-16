<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staffing_types extends CI_Migration {

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
                'type' => array(
                    'type' => "ENUM('head','assistant','participant','observer','lead')",
                    'null' => FALSE
                ),
                'extra_time' => array(
                    'type' => 'INT',
                    'constraint' => 3,
                    'default' => 0,
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
            $this->dbforge->create_table('staffing_types', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staffing_types') . '` ADD CONSTRAINT `fk_staffing_types_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staffing_types') . '` ADD CONSTRAINT `fk_staffing_types_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');


        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staffing_types') . '` DROP FOREIGN KEY `fk_staffing_types_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staffing_types') . '` DROP FOREIGN KEY `fk_staffing_types_byID`');

            // remove table, if exists
            $this->dbforge->drop_table('staffing_types', TRUE);
        }
}