<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Expenses extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define timesheets expenses fields
            $fields = array(
                'expenseID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'timesheetID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'lessonID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'date' => array(
                    'type' => 'DATE'
                ),
                'item' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
                ),
                'status' => array(
                    'type' => "ENUM('unsubmitted','submitted','approved','rejected')",
                    'default' => 'unsubmitted'
                ),
                'note' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE
                ),
                'receipt_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'receipt_path' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'receipt_type' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'receipt_ext' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'null' => TRUE,
                ),
                'receipt_size' => array(
                    'type' => 'BIGINT',
                    'constraint' => 100,
                    'null' => TRUE,
                ),
                'approved' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'rejected' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'approverID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'created' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME'
                ),
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('expenseID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('timesheetID');
            $this->dbforge->add_key('lessonID');
            $this->dbforge->add_key('approverID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('timesheets_expenses', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // remove timesheets expenses table, if exists
            $this->dbforge->drop_table('timesheets_expenses', TRUE);
        }
}