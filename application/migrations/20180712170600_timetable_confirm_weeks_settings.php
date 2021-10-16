<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timetable_confirm_weeks_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'timetable_confirm_weeks',
                    'title' => 'Timetable Weeks Available to Confirm in Advance',
                    'type' => 'number',
                    'section' => 'general',
                    'order' => 400,
                    'value' => 1,
                    'instruction' => 'Value should be 1 or more',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'timetable_confirm_weeks'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}