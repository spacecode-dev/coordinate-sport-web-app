<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_provisional_own_timetable extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'provisional_own_timetable',
                    'title' => 'Show Provisional Sessions on Own Timetable',
                    'type' => 'checkbox',
                    'section' => 'general',
                    'order' => 160,
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
                'provisional_own_timetable'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}