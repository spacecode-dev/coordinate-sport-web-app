<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Participant_emergency_contacts_children extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'emergency_contact_1_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'ethnic_origin'
            ),
			'emergency_contact_1_phone' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'emergency_contact_1_name'
            ),
			'emergency_contact_2_name' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'emergency_contact_1_phone'
            ),
			'emergency_contact_2_phone' => array(
                'type' => 'VARCHAR',
                'constraint' => 20,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'emergency_contact_2_name'
            )
        );
        $this->dbforge->add_column('family_children', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('family_children', 'emergency_contact_1_name');
		$this->dbforge->drop_column('family_children', 'emergency_contact_1_phone');
		$this->dbforge->drop_column('family_children', 'emergency_contact_2_name');
		$this->dbforge->drop_column('family_children', 'emergency_contact_2_phone');
    }
}
