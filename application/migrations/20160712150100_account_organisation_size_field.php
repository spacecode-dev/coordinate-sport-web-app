<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Account_organisation_size_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'organisation_size' => array(
                'type' => 'INT',
                'default' => 0,
                'null' => FALSE,
                'after' => 'phone'
            )
        );
        $this->dbforge->add_column('accounts', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('accounts', 'organisation_size');
    }
}