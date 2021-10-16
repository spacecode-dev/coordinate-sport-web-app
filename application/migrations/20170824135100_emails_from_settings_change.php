<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Emails_from_settings_change extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update setting
            $data = array(
                'title' => 'Send Emails From Name'
            );
            $where = array(
                'key' => 'email_from_name'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // do nothing
        }
}