<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_enable_credit_limits extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
				array(
                    'key' => 'enable_credit_limits',
                    'title' => 'Enable Credit Limits',
                    'type' => 'checkbox',
                    'section' => 'general',
                    'order' => 429,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'default_credit_limit',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);

			// update setting
            $data = array(
                'title' => 'Default Credit Limit',
				'instruction' => "Participants can't exceed this amount of debt when booking"
            );
            $where = array(
                'key' => 'default_credit_limit'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // remove new settings
            $where_in = array(
				'enable_credit_limits',
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

			// update setting
            $data = array(
                'title' => 'Default Cash Credit Limit',
				'instruction' => "Participants can't exceed this amount of cash debt when booking"
            );
            $where = array(
                'key' => 'default_credit_limit'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}
