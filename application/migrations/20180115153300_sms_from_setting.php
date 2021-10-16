<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Sms_from_setting extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'sms_from',
                    'title' => 'Send SMS From Name',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 2,
                    'value' => '{company}',
                    'instruction' => 'Maximum 11 characters. Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'sms_from'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}