<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_orgs_contacts_password extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'password' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE,
                'after' => 'email'
            )
        );
        $this->dbforge->add_column('orgs_contacts', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('orgs_contacts', 'password');
    }
}