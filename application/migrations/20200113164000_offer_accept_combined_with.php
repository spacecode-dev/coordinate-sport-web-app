<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offer_accept_combined_with extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {

        // add fields
        $fields = array(
            'combined_with' => array(
                'type' => "TEXT",
                'null' => TRUE,
                'after' => 'reason'
            ),
        );
        $this->dbforge->add_column('offer_accept', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('offer_accept', 'combined_with');
    }
}
