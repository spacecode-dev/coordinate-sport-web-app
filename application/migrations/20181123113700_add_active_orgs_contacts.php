<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_active_orgs_contacts extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add column
            $fields = array(
                'active' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'null' => FALSE,
                    'after' => 'byID'
                )
            );
            $this->dbforge->add_column('orgs_contacts', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('orgs_contacts', 'active');
        }
}