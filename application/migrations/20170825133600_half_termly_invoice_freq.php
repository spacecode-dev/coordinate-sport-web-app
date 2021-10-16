<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Half_termly_invoice_freq extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // update field
        $fields = array(
            'invoiceFrequency' => array(
                'name' => 'invoiceFrequency',
                'type' => "ENUM('weekly', 'monthly', 'half termly', 'termly', 'annually')",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('orgs', $fields);
    }

    public function down() {
        // update field
        $fields = array(
            'invoiceFrequency' => array(
                'name' => 'invoiceFrequency',
                'type' => "ENUM('weekly', 'monthly', 'termly', 'annually')",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('orgs', $fields);
    }
}