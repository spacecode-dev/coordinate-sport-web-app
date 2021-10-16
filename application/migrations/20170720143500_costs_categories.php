<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Costs_categories extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'category' => array(
                'type' => "ENUM('Venue Hire','Marketing','Prizes','Supplies','Misc.')",
                'default' => 'Misc.',
                'null' => FALSE,
                'after' => 'note'
            ),
        );
        $this->dbforge->add_column('bookings_costs', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings_costs', 'category');
    }
}