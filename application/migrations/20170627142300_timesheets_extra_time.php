<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheets_extra_time extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'extra_time' => array(
                'type' => "TIME",
                'default' => '00:00:00',
                'after' => 'end_time'
            )
        );
        $this->dbforge->add_column('timesheets_items', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('timesheets_items', 'extra_time');
    }
}