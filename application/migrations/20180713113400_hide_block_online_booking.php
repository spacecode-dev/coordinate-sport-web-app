<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Hide_block_online_booking extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'public' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => FALSE,
                'after' => 'thanksemail_sent'
            )
        );
        $this->dbforge->add_column('bookings_blocks', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings_blocks', 'public');
    }
}