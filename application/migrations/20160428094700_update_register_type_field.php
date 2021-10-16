<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_register_type_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers','names')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }

        public function down() {
            // update field
            $fields = array(
                'register_type' => array(
                    'name' => 'register_type',
                    'type' => "ENUM('children','individuals','numbers')",
                    'default' => 'children',
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }
}