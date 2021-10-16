<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_staff_non_delivery extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'non_delivery' => array(
                'type' => "TINYINT",
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'feed_key'
            )
        );
        $this->dbforge->add_column('staff', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'non_delivery');
    }
}