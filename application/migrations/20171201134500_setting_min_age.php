<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_min_age extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'min_age',
                    'title' => 'Minimum Age for Online Booking',
                    'type' => 'number',
                    'section' => 'general',
                    'order' => 365,
                    'value' => 3,
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
                'min_age'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}