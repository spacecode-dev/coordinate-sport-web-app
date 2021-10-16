<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_register_intro extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'register_intro',
                    'title' => 'Registration Introduction',
                    'type' => 'textarea',
                    'section' => 'general',
                    'order' => 380,
                    'value' => 'If you have booked with us before, but not online, you can {retrieve_password_link}. If you already have an account, please login instead.',
                    'instruction' => 'Shown on online booking. Available Tags: {retrieve_password_link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'register_intro'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}