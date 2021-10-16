<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_timesheet_role_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add role field field to timesheet items
            $fields = array(
                'role' => array(
                    'type' => "ENUM('head','assistant','participant','observer','lead')",
                    'null' => TRUE,
                    'after' => 'total_time'
                )
            );

            $this->dbforge->add_column('timesheets_items', $fields);
        }

        public function down() {
            // remove role field from timesheet items
            $this->dbforge->drop_column('timesheets_items', 'role');
        }
}