<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheets extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define timesheet fields
            $fields = array(
                'timesheetID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'date' => array( // day timesheet starts, e.g. monday
                    'type' => 'DATE'
                ),
                'status' => array(
                    'type' => "ENUM('unsubmitted','submitted','approved')",
                    'default' => 'unsubmitted'
                ),
                'total_time' => array(
                    'type' => 'TIME',
                    'default' => 0
                ),
                'total_expenses' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2',
                    'default' => 0
                ),
                'submitted' => array(
                    'type' => 'DATE',
                    'null' => TRUE
                ),
                'submitterID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'approved' => array(
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
            $this->dbforge->add_key('timesheetID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('submitterID');
            $this->dbforge->add_key('approverID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('timesheets', FALSE, $attributes);

            // set unique and foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets') . '` ADD UNIQUE INDEX (`staffID`, `date`)');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets') . '` ADD FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets') . '` ADD FOREIGN KEY (`submitterID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets') . '` ADD FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // define timesheet items fields
            $fields = array(
                'itemID' => array(
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
                'edited' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'date' => array(
                    'type' => 'DATE'
                ),
                'start_time' => array(
                    'type' => "TIME"
                ),
                'end_time' => array(
                    'type' => "TIME"
                ),
                'original_start_time' => array(
                    'type' => "TIME",
                    'null' => TRUE
                ),
                'original_end_time' => array(
                    'type' => "TIME",
                    'null' => TRUE
                ),
                'total_time' => array(
                    'type' => "TIME"
                ),
                'status' => array(
                    'type' => "ENUM('unsubmitted','submitted','approved','rejected')",
                    'default' => 'unsubmitted'
                ),
                'note' => array(
                    'type' => "VARCHAR",
                    'constraint' => 200,
                    'null' => TRUE
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
            $this->dbforge->add_key('itemID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('timesheetID');
            $this->dbforge->add_key('lessonID');
            $this->dbforge->add_key('approverID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('timesheets_items', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add addon_timesheets field to accounts
            $fields = array(
                'addon_timesheets' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'addon_bookings_timetable_confirmation'
                )
            );

            $this->dbforge->add_column('accounts', $fields);
        }

        public function down() {
            // remove addon_timesheets field from accounts, if exists
            if ($this->db->field_exists('addon_timesheets', 'accounts')) {
                $this->dbforge->drop_column('accounts', 'addon_timesheets');
            }

            // remove timesheets items table, if exists
            $this->dbforge->drop_table('timesheets_items', TRUE);

            // remove timesheets table, if exists
            $this->dbforge->drop_table('timesheets', TRUE);
        }
}