<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Project_codes_active extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'active' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 1,
                'null' => FALSE,
                'after' => 'desc'
            )
        );
        $this->dbforge->add_column('project_codes', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('project_codes', 'active');
    }
}
