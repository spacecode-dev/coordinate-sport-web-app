<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bookings_password extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'online_booking_password' => array(
                'type' => "VARCHAR",
                'constraint' => 20,
                'null' => TRUE,
                'default' => NULL,
                'after' => 'limit_participants'
            )
        );
        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'online_booking_password');
    }
}