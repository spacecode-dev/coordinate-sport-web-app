<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Work_mobile extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add field
            $fields = array(
                'mobile_work' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'mobile'
                ),
            );
            $this->dbforge->add_column('staff_addresses', $fields);
        }

        public function down() {
            // remove field
            $this->dbforge->drop_column('staff_addresses', 'mobile_work');
        }
}