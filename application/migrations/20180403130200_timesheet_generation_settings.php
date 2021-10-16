<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheet_generation_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'timesheets_create_day',
                    'title' => 'Create Timesheets On',
                    'type' => 'select',
                    'section' => 'general',
                    'order' => 298,
                    'options' => "1 : Monday
2 : Tuesday
3 : Wednesday
4 : Thursday
5 : Friday
6 : Saturday
7 : Sunday",
                    'value' => "5",
                    'instruction' => 'Day to create timesheets for the current week',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'timesheets_submit_day',
                    'title' => 'Submit Timesheets On',
                    'type' => 'select',
                    'section' => 'general',
                    'order' => 299,
                    'options' => "1 : Monday
2 : Tuesday
3 : Wednesday
4 : Thursday
5 : Friday
6 : Saturday
7 : Sunday",
                    'value' => "3",
                    'instruction' => 'Day to auto-submit timesheets for previous week',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );
            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'timesheets_create_day',
                'timesheets_submit_day'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}