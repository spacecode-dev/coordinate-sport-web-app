<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_rejected_to_declined extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // rename rejected to declined
            $fields = array(
                'status' => array(
                    'name' => 'status',
                    'type' => "ENUM('unsubmitted','submitted','approved','declined')",
                    'default' => 'unsubmitted'
                ),
                'rejected' => array(
                    'name' => 'declined',
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('timesheets_items', $fields);
            $this->dbforge->modify_column('timesheets_expenses', $fields);
        }

        public function down() {
            // rename declined to rejected
            $fields = array(
                'status' => array(
                    'name' => 'status',
                    'type' => "ENUM('unsubmitted','submitted','approved','rejected')",
                    'default' => 'unsubmitted'
                ),
                'declined' => array(
                    'name' => 'rejected',
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('timesheets_items', $fields);
            $this->dbforge->modify_column('timesheets_expenses', $fields);
        }
}