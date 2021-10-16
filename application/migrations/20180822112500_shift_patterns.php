<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Shift_patterns extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify settings fields
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css', 'function', 'date', 'date-monday')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define new settings
            $data = array(
                array(
                    'key' => 'shift_pattern_weeks',
                    'title' => 'Number of weeks in shift pattern',
                    'type' => 'select',
                    'section' => 'general',
                    'order' => 420,
                    'options' => "1 : 1\n2 : 2\n3 : 3\n4 : 4",
                    'value' => '1',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'shift_pattern_start',
                    'title' => 'Date to start shift pattern',
                    'type' => 'date-monday',
                    'section' => 'general',
                    'order' => 421,
                    'options' => '',
                    'value' => '2018-01-01',
                    'instruction' => 'Mondays only - not required if shift patttern is 1 week',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);

            // add fields
            $fields = array(
                'week' => array(
                    'type' => "INT",
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE,
                    'after' => 'byID'
                )
            );
            $this->dbforge->add_column('staff_availability', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('staff_availability', 'week');

            // remove new settings
            $where_in = array(
                'shift_pattern_weeks',
                'shift_pattern_start'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // modify fields
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css', 'function')",
                    'null' => FALSE,
                )
            );
        }
}