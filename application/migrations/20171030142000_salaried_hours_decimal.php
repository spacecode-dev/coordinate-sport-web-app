<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Salaried_hours_decimal extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {

        // modfify field
        $fields = array(
            'target_hours' => array(
                'name' => 'target_hours',
                'type' => "DECIMAL(6,1)",
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('staff', $fields);

    }

    public function down() {

        // modfify field
        $fields = array(
            'target_hours' => array(
                'name' => 'target_hours',
                'type' => "INT",
                'constraint' => 5,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('staff', $fields);

    }
}