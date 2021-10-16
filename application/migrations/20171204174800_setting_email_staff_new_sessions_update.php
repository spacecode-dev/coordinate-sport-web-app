<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_staff_new_sessions_update extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update subject
            $where = array(
                'key' => 'email_staff_new_sessions_subject'
            );
            $data = array(
                'instruction' => 'Available tags: {staff_first}',
            );
            $this->db->update('settings', $data, $where);
        }

        public function down() {
            // do nothing
        }
}