<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Prospect_default_value extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify fields
            $fields = array(
                'prospect' => array(
                    'name' => 'prospect',
                    'type' => "TINYINT",
					'constraint' => 1,
					'default' => 0,
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('orgs', $fields);
        }

        public function down() {
            // no going back
        }
}
