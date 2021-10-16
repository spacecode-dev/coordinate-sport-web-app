<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_payment_fees_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add field
        $fields = array(
            'fee' => array(
                'type' => "DECIMAL(10,2)",
                'after' => 'amount'
            )
        );

        $this->dbforge->add_column('family_payments', $fields);
    }

    public function down() {
        // remove role field from timesheet items
        $this->dbforge->drop_column('family_payments', 'fee');
    }
}