<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Null_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify fields
        $fields = array(
            'address3' => array(
                'name' => 'address3',
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => TRUE
            ),
            'town' => array(
                'name' => 'town',
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ),
            'workPhone' => array(
                'name' => 'workPhone',
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('family_contacts', $fields);
    }

    public function down() {
        // no downgrade as doing so may trim data
    }
}