<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Tandc_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'terms_individual',
                    'title' => 'Online Booking Terms and Conditions',
                    'type' => 'wysiwyg',
                    'section' => 'general',
                    'order' => 150,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => 'Participants need to read and agree to these to book',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'terms_customer',
                    'title' => 'Customer Booking Terms and Conditions',
                    'type' => 'wysiwyg',
                    'section' => 'general',
                    'order' => 151,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => 'Customers need to read and agree to these to book',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'terms_individual',
                'terms_customer',
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}