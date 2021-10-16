<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_sessions_max_age extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'max_age' => array(
                    'type' => "INT",
                    'constraint' => 3,
                    'default' => NULL,
                    'null' => TRUE,
                    'after' => 'min_age'
                )
            );
            $this->dbforge->add_column('bookings_lessons', $fields);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('bookings_lessons', 'max_age');
        }
}