<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_online_booking_header_footer extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // define new settings
            $data = array(
                array(
                    'key' => 'online_booking_header',
                    'title' => 'Online Booking Header HTML',
                    'type' => 'html',
                    'section' => 'styling',
                    'order' => 51,
                    'value' => '',
                    'instruction' => 'Logo will be hidden if this field has content',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'online_booking_footer',
                    'title' => 'Online Booking Footer HTML',
                    'type' => 'html',
                    'section' => 'styling',
                    'order' => 52,
                    'value' => '',
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'online_booking_meta',
                    'title' => 'Online Booking Meta HTML',
                    'type' => 'html',
                    'section' => 'styling',
                    'order' => 53,
                    'value' => '',
                    'instruction' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'online_booking_header',
                'online_booking_footer',
                'online_booking_meta'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}