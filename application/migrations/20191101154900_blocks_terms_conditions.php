<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Blocks_terms_conditions extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'terms_accepted' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'provisional'
            )
        );
        $this->dbforge->add_column('bookings_blocks', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings_blocks', 'terms_accepted');
    }
}
