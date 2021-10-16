<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_staff_required_roles extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'staff_required_assistant' => array(
                    'type' => "INT",
                    'constraint' => 3,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'staff_required'
                ),
                'staff_required_lead' => array(
                    'type' => "INT",
                    'constraint' => 3,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'staff_required'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);

            // rename fields
            $fields = array(
                'staff_required' => array(
                    'name' => 'staff_required_head',
                    'type' => "INT",
                    'constraint' => 3,
                    'null' => FALSE,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'staff_required_assistant');
            $this->dbforge->drop_column('bookings_lessons', 'staff_required_lead');

            // rename fields
            $fields = array(
                'staff_required_head' => array(
                    'name' => 'staff_required',
                    'type' => "INT",
                    'constraint' => 3,
                    'null' => FALSE,
                    'default' => 0
                )
            );
            $this->dbforge->modify_column('bookings_lessons', $fields);
        }
}