<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheets_reason_admin extends CI_Migration {

    public $integration_fields;

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // update field
        $fields = array(
            'reason' => array(
                'name' => 'reason',
                'type' => "ENUM('travel', 'training', 'marketing', 'admin', 'other')",
                'null' => TRUE,
                'default' => NULL
            )
        );
        $this->dbforge->modify_column('timesheets_items', $fields);
        $this->dbforge->modify_column('timesheets_expenses', $fields);
    }

    public function down() {
        // update field
        $fields = array(
            'reason' => array(
                'name' => 'reason',
                'type' => "ENUM('travel', 'training', 'marketing', 'other')",
                'null' => TRUE,
                'default' => NULL
            )
        );
        $this->dbforge->modify_column('timesheets_items', $fields);
        $this->dbforge->modify_column('timesheets_expenses', $fields);
    }
}