<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_feed_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add field
        $fields = array(
            'feed_enabled' => array(
                'type' => "TINYINT",
                'constraint' => 1,
                'default' => 0,
                'after' => 'driving_declaration'
            ),
            'feed_key' => array(
                'type' => "VARCHAR",
                'constraint' => 32,
                'default' => NULL,
                'after' => 'feed_enabled'
            ),
        );
        $this->dbforge->add_column('staff', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'feed_enabled');
        $this->dbforge->drop_column('staff', 'feed_key');
    }
}