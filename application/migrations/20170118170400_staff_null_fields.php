<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_null_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify fields
        $fields = array(
            'medical' => array(
                'name' => 'medical',
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'qual_fsscrb_ref' => array(
                'name' => 'qual_fsscrb_ref',
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ),
            'qual_othercrb_ref' => array(
                'name' => 'qual_othercrb_ref',
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ),
            'proofid_passport_ref' => array(
                'name' => 'proofid_passport_ref',
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => TRUE
            ),
            'proofid_nicard_ref' => array(
                'name' => 'proofid_nicard_ref',
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => TRUE
            ),
            'proofid_driving_ref' => array(
                'name' => 'proofid_driving_ref',
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => TRUE
            ),
            'proofid_birth_ref' => array(
                'name' => 'proofid_birth_ref',
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => TRUE
            ),
            'proofid_other_specify' => array(
                'name' => 'proofid_other_specify',
                'type' => 'VARCHAR',
                'constraint' => 30,
                'null' => TRUE
            ),
        );
        $this->dbforge->modify_column('staff', $fields);

        $fields = array(
            'town' => array(
                'name' => 'town',
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ),
        );
        $this->dbforge->modify_column('staff_addresses', $fields);
    }

    public function down() {
        // no downgrade as doing so may trim data
    }
}