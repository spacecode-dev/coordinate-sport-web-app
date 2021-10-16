<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_online_booking_link extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// remove settings
            $where_in = array(
                'online_booking_link'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }

        public function down() {
			// define settings
            $data = array(
                array(
                    'key' => 'online_booking_link',
                    'title' => 'Online Booking Link',
                    'type' => 'url',
                    'section' => 'general',
                    'order' => 41,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }
}
