<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Events_to_projects extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // mark all events as projects
            $data = array(
                'project' => 1
            );
            $where = array(
                'type' => 'event',
                'project !=' => 1
            );
            $this->db->update('bookings', $data, $where);
        }

        public function down() {
            // can't revert
        }
}