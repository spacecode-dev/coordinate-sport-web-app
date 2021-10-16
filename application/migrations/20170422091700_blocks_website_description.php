<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Blocks_website_description extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'website_description' => array(
                'type' => "TEXT",
                'null' => TRUE,
                'after' => 'org_bookable'
            )
        );
        $this->dbforge->add_column('bookings_blocks', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings_blocks', 'website_description');
    }
}