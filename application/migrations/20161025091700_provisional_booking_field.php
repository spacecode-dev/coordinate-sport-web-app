<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Provisional_booking_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'provisional' => array(
                'type' => 'TINYINT',
                'default' => 0,
                'null' => FALSE,
                'after' => 'renewed'
            )
        );
        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'provisional');
    }
}