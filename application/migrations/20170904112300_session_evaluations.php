<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_evaluations extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'date' => array(
                    'type' => 'DATE',
                    'null' => TRUE,
                    'after' => 'content'
                ),
                'type' => array(
                    'type' => "ENUM('note', 'evaluation')",
                    'null' => FALSE,
                    'default' => 'note',
                    'after' => 'byID'
                ),
                'status' => array(
                    'type' => "ENUM('unsubmitted', 'submitted', 'approved', 'rejected')",
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'date'
                ),
                'approverID' => array(
                    'type' => 'INT',
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'status'
                ),
                'rejection_reason' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100,
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'approverID'
                ),
                'approved' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'rejection_reason'
                ),
                'rejected' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE,
                    'after' => 'approved'
                ),
            );
            $this->dbforge->add_column('bookings_lessons_notes', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_notes') . '` ADD INDEX (`approverID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_notes') . '` ADD CONSTRAINT `fk_bookings_lessons_notes_approverID` FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE SET NULL ON UPDATE CASCADE');

            // modify fields
            $fields = array(
                'summary' => array(
                    'name' => 'summary',
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => TRUE,
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('bookings_lessons_notes', $fields);
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_notes') . '` DROP FOREIGN KEY `fk_bookings_lessons_notes_approverID`');

            // remove fields
            $this->dbforge->drop_column('bookings_lessons_notes', 'date');
            $this->dbforge->drop_column('bookings_lessons_notes', 'type');
            $this->dbforge->drop_column('bookings_lessons_notes', 'status');
            $this->dbforge->drop_column('bookings_lessons_notes', 'approverID');
            $this->dbforge->drop_column('bookings_lessons_notes', 'rejection_reason');
            $this->dbforge->drop_column('bookings_lessons_notes', 'approved_at');

            // modify fields
            $fields = array(
                'summary' => array(
                    'name' => 'summary',
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings_lessons_notes', $fields);
        }
}