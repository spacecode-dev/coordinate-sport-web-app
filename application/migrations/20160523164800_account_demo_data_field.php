<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Account_demo_data_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'demo_data_imported' => array(
                'type' => 'TINYINT(1)',
                'default' => 0,
                'after' => 'admin'
            )
        );
        $this->dbforge->add_column('accounts', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('accounts', 'demo_data_imported');
    }
}