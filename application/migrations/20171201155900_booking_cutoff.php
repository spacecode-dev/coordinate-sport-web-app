<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_booking_cutoff extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'booking_cutoff',
                    'title' => 'Online Booking Cut Off',
                    'type' => 'number',
                    'section' => 'general',
                    'order' => 370,
                    'value' => 24,
                    'instruction' => 'In hours. Applies to online booking only',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);

            // add new booking requirement
            $fields = array(
                'booking_requirement' => array(
                    'name' => 'booking_requirement',
                    'type' => "ENUM('all', 'select', 'remaining')",
                    'null' => FALSE,
                    'default' => 'all'
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // add fields
            $fields = array(
                'booking_cutoff' => array(
                    'type' => "INT",
                    'constraint' => 3,
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'staff_required_assistant'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'booking_cutoff'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // remove new booking requirement
            $fields = array(
                'booking_requirement' => array(
                    'name' => 'booking_requirement',
                    'type' => "ENUM('all', 'select')",
                    'null' => FALSE,
                    'default' => 'all'
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'booking_cutoff');
        }
}