<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Additional_account_statuses extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify fields
            $fields = array(
                'status' => array(
                    'name' => 'status',
                    'type' => "ENUM('trial', 'paid', 'admin', 'support', 'internal')",
                    'default' => 'trial',
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('accounts', $fields);
        }

        public function down() {
            // modify fields
            $fields = array(
                'status' => array(
                    'name' => 'status',
                    'type' => "ENUM('trial', 'paid')",
                    'default' => 'trial',
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('accounts', $fields);
        }
}