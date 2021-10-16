<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Account_booking_subdomain_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'booking_subdomain' => array(
                'type' => 'VARCHAR',
				'constraint' => 50,
                'null' => TRUE,
                'after' => 'organisation_size'
            )
        );
        $this->dbforge->add_column('accounts', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('accounts', 'booking_subdomain');
    }
}
