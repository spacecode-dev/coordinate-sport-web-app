<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bookings_limit_participants extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'limit_participants' => array(
                'type' => "TINYINT",
                'constraint' => 1,
                'null' => FALSE,
                'default' => 0,
                'after' => 'public'
            )
        );
        $this->dbforge->add_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'limit_participants');
    }
}