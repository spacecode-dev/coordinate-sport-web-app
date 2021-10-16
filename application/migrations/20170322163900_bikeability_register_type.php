<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bikeability_register_type extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers','names','bikeability')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // add field
            $fields = array(
                'bikeability_level' => array(
                    'type' => 'INT',
                    'constraint' => 1,
                    'default' => NULL,
                    'after' => 'monitoring3'
                )
            );
            $this->dbforge->add_column('bookings_attendance_names', $fields);

            // add field
            $fields = array(
                'bikeability_level' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 5,
                    'default' => NULL,
                    'after' => 'date'
                )
            );
            $this->dbforge->add_column('bookings_attendance_names_sessions', $fields);
        }

        public function down() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers','names')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // remove fields
            $this->dbforge->drop_column('bookings_attendance_names', 'bikeability_level');
            $this->dbforge->drop_column('bookings_attendance_names_sessions', 'bikeability_level');
        }
}