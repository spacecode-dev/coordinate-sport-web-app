<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Change_payments_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add direct debit payment type and increase field length
        $fields = array(
            'method' => array(
                'name' => 'method',
                'type' => "ENUM('card','cash','cheque','online','other','childcare voucher','direct debit')",
                'null' => FALSE
            ),
            'transaction_ref' => array(
                'name' => 'transaction_ref',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('family_payments', $fields);
    }

    public function down() {
        // removed direct debit type and decrease field length
        $fields = array(
            'method' => array(
                'name' => 'method',
                'type' => "ENUM('card','cash','cheque','online','other','childcare voucher')",
                'null' => FALSE
            ),
            'transaction_ref' => array(
                'name' => 'transaction_ref',
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('family_payments', $fields);
    }
}