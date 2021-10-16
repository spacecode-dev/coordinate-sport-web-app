<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Orgs_imported_fields extends CI_Migration {

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
        $this->dbforge->add_column('orgs', $fields);
        $this->dbforge->add_column('orgs_addresses', $fields);
        $this->dbforge->add_column('orgs_contacts', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('orgs', 'imported');
        $this->dbforge->drop_column('orgs_addresses', 'imported');
        $this->dbforge->drop_column('orgs_contacts', 'imported');
    }
}