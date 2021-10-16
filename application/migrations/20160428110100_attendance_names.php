<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Attendance_names extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'participantID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'bookingID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'blockID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'monitoring1' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'monitoring2' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE
                ),
                'monitoring3' => array(
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
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('participantID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('bookingID');
            $this->dbforge->add_key('blockID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('bookings_attendance_names', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attendance_names') . '` ADD FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attendance_names') . '` ADD FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attendance_names') . '` ADD FOREIGN KEY (`blockID`) REFERENCES `' . $this->db->dbprefix('bookings_blocks') . '`(`blockID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_attendance_names') . '` ADD FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // remove table, if exists
            $this->dbforge->drop_table('bookings_attendance_names', TRUE);
        }
}