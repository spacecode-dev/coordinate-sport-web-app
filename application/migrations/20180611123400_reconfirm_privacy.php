<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Reconfirm_privacy extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify settings fields
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css', 'function')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define new settings
            $data = array(
                array(
                    'key' => 'reconfirm_participant_privacy',
                    'title' => 'Reprompt Participant Privacy Policy',
                    'type' => 'function',
                    'section' => 'privacy',
                    'order' => 1,
                    'value' => 'reconfirm_participant_privacy',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'reconfirm_staff_privacy',
                    'title' => 'Reprompt Staff Privacy Policy',
                    'type' => 'function',
                    'section' => 'privacy',
                    'order' => 101,
                    'value' => 'reconfirm_staff_privacy',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'reconfirm_company_privacy',
                    'title' => 'Reprompt Company Privacy Policy',
                    'type' => 'function',
                    'section' => 'global',
                    'order' => 11,
                    'value' => 'reconfirm_company_privacy',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'reconfirm_participant_privacy',
                'reconfirm_staff_privacy',
                'reconfirm_company_privacy'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

            // modify fields
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html', 'css')",
                    'null' => FALSE,
                )
            );
        }
}