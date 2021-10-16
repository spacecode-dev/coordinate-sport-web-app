<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Change_gocardless_status_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // rename expired to completed
        $fields = array(
            'status' => array(
                'name' => 'status',
                'type' => "ENUM('inactive','active','cancelled','completed')",
                'default' => 'inactive'
            )
        );
        $this->dbforge->modify_column('family_payments_plans', $fields);
    }

    public function down() {
        // rename completed to expired
        $fields = array(
            'status' => array(
                'name' => 'status',
                'type' => "ENUM('inactive','active','cancelled','expired')",
                'default' => 'inactive'
            )
        );
        $this->dbforge->modify_column('family_payments_plans', $fields);
    }
}