<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Payment_plans extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define timesheets expenses fields
            $fields = array(
                'planID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'familyID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'contactID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'recordID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
                ),
                'interval_count' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'interval_length' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'interval_unit' => array(
                    'type' => "ENUM('day','week','month')",
                    'default' => 'month'
                ),
                'status' => array(
                    'type' => "ENUM('inactive','active','cancelled','expired')",
                    'default' => 'inactive'
                ),
                'gocardless_subscription_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'note' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'authorised' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('planID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('familyID');
            $this->dbforge->add_key('contactID');
            $this->dbforge->add_key('recordID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('family_payments_plans', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD FOREIGN KEY (`familyID`) REFERENCES `' . $this->db->dbprefix('family') . '`(`familyID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD FOREIGN KEY (`recordID`) REFERENCES `' . $this->db->dbprefix('bookings_individuals') . '`(`recordID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // remove table, if exists
            $this->dbforge->drop_table('family_payments_plans', TRUE);
        }
}