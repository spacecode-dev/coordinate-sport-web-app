<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_blocks_min_age extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'min_age' => array(
                    'type' => "INT",
                    'constraint' => 3,
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'staffing_notes'
                )
            );
            $this->dbforge->add_column('bookings_blocks', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('bookings_blocks', 'min_age');
        }
}