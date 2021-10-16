<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_fixed_discounts extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'discount_type' => array(
                'type' => "ENUM('percentage','amount')",
                'default' => 'percentage',
                'null' => FALSE,
                'after' => 'code'
            )
        );
        $this->dbforge->add_column('vouchers', $fields);
        $this->dbforge->add_column('bookings_vouchers', $fields);

        // modify fields
        $fields = array(
            'discount' => array(
                'name' => 'discount',
                'type' => "DECIMAL",
                'constraint' => '6,2',
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('vouchers', $fields);
        $this->dbforge->modify_column('bookings_vouchers', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('vouchers', 'discount_type');
        $this->dbforge->drop_column('bookings_vouchers', 'discount_type');

        // modify fields
        $fields = array(
            'discount' => array(
                'name' => 'discount',
                'type' => "INT",
                'constraint' => 3,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('vouchers', $fields);
        $this->dbforge->modify_column('bookings_vouchers', $fields);
    }
}