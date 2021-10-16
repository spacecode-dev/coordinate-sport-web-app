<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booking_postcodes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'booking_postcodes' => array(
                'type' => "VARCHAR",
                'constraint' => 250,
                'null' => TRUE,
                'after' => 'booking_requirement'
            )
        );
        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'booking_requirement');
    }
}