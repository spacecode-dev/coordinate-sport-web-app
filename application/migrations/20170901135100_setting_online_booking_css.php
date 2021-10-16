<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_online_booking_css extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css')",
                    'null' => FALSE,
                    'default' => 'text'
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define new settings
            $data = array(
                array(
                    'key' => 'online_booking_css',
                    'title' => 'Online Booking CSS Overrides',
                    'type' => 'css',
                    'section' => 'styling',
                    'order' => 50,
                    'value' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'online_booking_css'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html')",
                    'null' => FALSE,
                    'default' => 'text'
                )
            );
            $this->dbforge->modify_column('settings', $fields);
        }
}