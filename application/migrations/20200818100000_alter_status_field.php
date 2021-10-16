<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_status_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add 'Demo' Values from status field
            $fields = array(
                'status' => array(
                    'name' => 'status',
                    'type' => "ENUM('trial', 'paid', 'demo', 'admin', 'support', 'internal')",
                    'default' => 'trial',
                    'null' => FALSE,
                )
            );

            $this->dbforge->modify_column('accounts', $fields);
        }

        public function down() {
            // remove 'Demo' Values from status field
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
}