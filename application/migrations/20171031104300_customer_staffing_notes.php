<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Customer_staffing_notes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'staffing_notes' => array(
                'type' => 'TEXT',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'rate'
            )
        );
        $this->dbforge->add_column('orgs', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('orgs', 'staffing_notes');
    }
}