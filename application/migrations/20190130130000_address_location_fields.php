<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Address_location_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// add fields
        $fields = array(
			'location' => array(
                'type' => 'POINT',
                'null' => TRUE,
                'after' => 'postcode'
            )
        );
        $this->dbforge->add_column('orgs_addresses', $fields);
		$this->dbforge->add_column('family_contacts', $fields);
    }

    public function down() {
        // remove fields
		$this->dbforge->drop_column('orgs_addresses', 'location');
		$this->dbforge->drop_column('family_contacts', 'location');
    }
}
