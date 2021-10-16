<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_require_participant_email extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'require_participant_email',
                    'title' => 'Require email when adding participants',
                    'type' => 'checkbox',
                    'section' => 'general',
                    'order' => 351,
                    'value' => 0,
                    'instruction' => 'Always required when participant registers themselves for online booking',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'require_participant_email'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}
