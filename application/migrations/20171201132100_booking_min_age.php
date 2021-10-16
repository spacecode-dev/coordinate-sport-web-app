<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_booking_min_age extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'min_age' => array(
                'type' => "INT",
                'constraint' => 3,
                'default' => 0,
                'null' => TRUE,
                'after' => 'limit_participants'
            )
        );
        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'min_age');
    }
}