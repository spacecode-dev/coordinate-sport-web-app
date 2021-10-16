<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_freshdesk_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // remove settings
            $where_in = array(
                'freshdesk_shared_secret',
                'freshdesk_base_url'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }

        public function down() {
            // no going back
        }
}