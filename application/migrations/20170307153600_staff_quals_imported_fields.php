<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_quals_imported_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'imported' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'byID'
            )
        );
        $this->dbforge->add_column('staff_quals', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff_quals', 'imported');
    }
}