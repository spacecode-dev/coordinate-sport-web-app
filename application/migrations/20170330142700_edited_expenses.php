<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Edited_expenses extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'edited' => array(
                'type' => "TINYINT",
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'lessonID'
            )
        );
        $this->dbforge->add_column('timesheets_expenses', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('timesheets_expenses', 'edited');
    }
}