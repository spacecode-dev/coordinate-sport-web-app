<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Shapeup_register_type extends CI_Migration {

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
                    'type' => "ENUM('children', 'individuals', 'numbers', 'names', 'bikeability', 'children_bikeability', 'individuals_bikeability', 'shapeup', 'children_shapeup', 'individuals_shapeup')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // add field
            $fields = array(
                'shapeup_weight' => array(
                    'type' => 'DECIMAL(5,2)',
                    'default' => NULL,
                    'after' => 'bikeability_level'
                )
            );
            $this->dbforge->add_column('bookings_attendance_names_sessions', $fields);
            $this->dbforge->add_column('bookings_individuals_sessions', $fields);
        }

        public function down() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children', 'individuals', 'numbers', 'names', 'bikeability', 'children_bikeability', 'individuals_bikeability')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // remove fields
            $this->dbforge->drop_column('bookings_attendance_names_sessions', 'shapeup_weight');
            $this->dbforge->drop_column('bookings_individuals_sessions', 'shapeup_weight');
        }
}