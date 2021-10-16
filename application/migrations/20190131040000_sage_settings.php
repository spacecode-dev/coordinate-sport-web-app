<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sage_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
				array(
                    'key' => 'sagepay_environment',
                    'title' => 'SagePay Environment',
                    'type' => 'select',
                    'section' => 'integrations',
                    'order' => 140,
                    'options' => "production : Production
test : Test",
                    'value' => 'production',
                    'instruction' => 'For SagePay Form Integration v3',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'sagepay_vendor',
                    'title' => 'SagePay Vendor Name',
                    'type' => 'text',
                    'section' => 'integrations',
                    'order' => 141,
                    'options' => NULL,
                    'value' => NULL,
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'sagepay_encryption_password',
                    'title' => 'SagePay Encryption Password',
                    'type' => 'text',
                    'section' => 'integrations',
                    'order' => 142,
                    'options' => NULL,
                    'value' => NULL,
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
                'sagepay_environment',
                'sagepay_vendor',
				'sagepay_encryption_password'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}