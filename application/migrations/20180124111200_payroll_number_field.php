<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_payroll_number_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'payroll_number' => array(
                'type' => "VARCHAR",
                'constraint' => 50,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'payments_accountNumber'
            )
        );
        $this->dbforge->add_column('staff', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'payroll_number');
    }
}