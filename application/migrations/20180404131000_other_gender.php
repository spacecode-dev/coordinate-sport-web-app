<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Other_gender extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify fields
        $fields = array(
            'gender' => array(
                'name' => 'gender',
                'type' => "ENUM('male', 'female', 'other')",
                'null' => TRUE,
                'default' => NULL
            )
        );
        $this->dbforge->modify_column('family_contacts', $fields);
    }

    public function down() {
        // modify fields
        $fields = array(
            'gender' => array(
                'name' => 'gender',
                'type' => "ENUM('male', 'female')",
                'null' => TRUE,
                'default' => NULL
            )
        );
        $this->dbforge->modify_column('family_contacts', $fields);
    }
}