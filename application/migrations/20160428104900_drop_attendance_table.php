<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Drop_attendance_table extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // drop table
            $this->dbforge->drop_table('bookings_attendance',TRUE);
        }

        public function down() {
            // do nothing
        }
}