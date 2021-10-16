<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_staff_password_fields extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // remove settings
            $where = array(
                'section' => 'staff'
            );
            $where_in = array(
                'password_confirm',
                'password'
            );
            $this->db->from('settings_fields')->where_in('field', $where_in)->where($where)->delete();
        }

        public function down() {
            // no going back
        }
}