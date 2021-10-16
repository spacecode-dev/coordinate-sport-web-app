<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_require_mobile extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'require_mobile',
                    'title' => 'Require mobile number on registration',
                    'type' => 'checkbox',
                    'section' => 'general',
                    'order' => 350,
                    'value' => 0,
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'require_mobile'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}