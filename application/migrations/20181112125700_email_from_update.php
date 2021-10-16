<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_from_update extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // rename setting
            $data = array(
                'title' => 'Reply Email Address'
            );
            $where = array(
                'key' => 'email_from'
            );
            $this->db->update('settings', $data, $where, 1);

            // add setting
            $data = array(
                'key' => 'email_from_default',
                'title' => 'Send Emails From Address',
                'type' => 'email',
                'section' => 'global',
                'order' => 1,
                'value' => "notifications@coordinate.cloud",
                'instruction' => '',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );
            $this->db->insert('settings', $data);
        }

        public function down() {
            // rename setting
            $data = array(
                'title' => 'Send Emails From Address'
            );
            $where = array(
                'key' => 'email_from'
            );
            $this->db->update('settings', $data, $where, 1);

            // remove setting
            $where = array(
                'key' => 'email_from_default'
            );
            $this->db->delete('settings', $where, 1);
        }
}