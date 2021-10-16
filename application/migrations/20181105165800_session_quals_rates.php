<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_quals_rates extends CI_Migration {

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
                'lessionTypeID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'qualTypeID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'pay_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0
                ),
                'increased_pay_rate' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'default' => 0
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('lessionTypeID');
            $this->dbforge->add_key('qualTypeID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('session_qual_rates', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` ADD CONSTRAINT `fk_session_qual_rate_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` ADD CONSTRAINT `fk_session_qual_rate_lessionTypeID` FOREIGN KEY (`lessionTypeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` ADD CONSTRAINT `fk_session_qual_rate_qualTypeID` FOREIGN KEY (`qualTypeID`) REFERENCES `' . $this->db->dbprefix('mandatory_quals') . '`(`qualID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {

            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` DROP FOREIGN KEY `fk_session_qual_rate_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` DROP FOREIGN KEY `fk_session_qual_rate_lessionTypeID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('session_qual_rates') . '` DROP FOREIGN KEY `fk_session_qual_rate_qualTypeID`');

            // remove table, if exists
            $this->dbforge->drop_table('session_qual_rates', TRUE);
        }
}
