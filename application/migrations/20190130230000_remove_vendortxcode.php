<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_vendortxcode extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // rename field
            $fields = array(
                'vendortxcode' => array(
                    'name' => 'vendortxcode_old',
                    'type' => 'VARCHAR',
					'constraint' => 13,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }

        public function down() {
			// rename field
            $fields = array(
                'vendortxcode_old' => array(
                    'name' => 'vendortxcode',
                    'type' => 'VARCHAR',
					'constraint' => 13,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }
}