<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_payments_recordID extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // rename field
            $fields = array(
                'recordID' => array(
                    'name' => 'recordID_old',
                    'type' => 'INT',
					'constraint' => 11,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('family_payments', $fields);
			$this->dbforge->modify_column('family_payments_plans', $fields);
        }

        public function down() {
			// rename field
            $fields = array(
                'recordID_old' => array(
                    'name' => 'recordID',
                    'type' => 'INT',
					'constraint' => 11,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('family_payments', $fields);
			$this->dbforge->modify_column('family_payments_plans', $fields);
        }
}