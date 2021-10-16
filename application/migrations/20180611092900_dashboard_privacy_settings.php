<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dashboard_privacy_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'dashboard_staff_birthdays',
                    'title' => 'Show Staff Birthdays on Dashboard',
                    'type' => 'checkbox',
                    'section' => 'dashboard',
                    'order' => 0,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );
            // bulk insert
            $this->db->insert_batch('settings', $data);

            // modify settings
            $data = array(
                'instruction' => 'If not set, employee of the month won\'t be shown',
                'section' => 'dashboard',
                'order' => -1
            );
            $where = array(
                'key' => 'employee_of_month'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'dashboard_staff_birthdays'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // modify settings
            $data = array(
                'instruction' => '',
                'section' => 'general'
            );
            $where = array(
                'key' => 'employee_of_month'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}