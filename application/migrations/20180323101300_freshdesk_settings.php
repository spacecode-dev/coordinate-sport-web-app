<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Freshdesk_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'freshdesk_shared_secret',
                    'title' => 'Freshdesk Shared Secret',
                    'type' => 'text',
                    'section' => 'global',
                    'order' => 5,
                    'value' => '',
                    'instruction' => 'See instructions at <a href="https://support.freshdesk.com/support/solutions/articles/31166-single-sign-on-remote-authentication-in-freshdesk" target="_blank">Freshdesk</a>',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'freshdesk_base_url',
                    'title' => 'Freshdesk Base URL',
                    'type' => 'text',
                    'section' => 'global',
                    'order' => 6,
                    'value' => '',
                    'instruction' => 'With trailing slash',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'freshdesk_shared_secret',
                'freshdesk_base_url'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}