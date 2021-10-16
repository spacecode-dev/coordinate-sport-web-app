<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_dashboard_config extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'dashboard_config' => array(
                'type' => 'TEXT',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'driving_declaration'
            ),
        );
        $this->dbforge->add_column('staff', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'dashboard_config');
    }
}