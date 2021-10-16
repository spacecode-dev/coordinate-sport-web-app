<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sub_total_bookings_cart extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $fields = array(
            'subscription_total' => array(
				'type' => 'DECIMAL',
				'constraint' => '8,2',
				'default' => 0,
				'null' => FALSE,
                'after' => 'subtotal'
            )
        );

        $this->dbforge->add_column('bookings_cart', $fields);
    }

    public function down() {
        $this->dbforge->drop_column('bookings_cart', 'subscription_total');
    }
}
