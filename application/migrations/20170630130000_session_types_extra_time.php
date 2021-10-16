<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_types_extra_time extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'extra_time_head' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
                'null' => FALSE,
                'after' => 'birthday_tab'
            ),
            'extra_time_lead' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
                'null' => FALSE,
                'after' => 'extra_time_head'
            ),
            'extra_time_assistant' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => 0,
                'null' => FALSE,
                'after' => 'extra_time_lead'
            ),
        );
        $this->dbforge->add_column('lesson_types', $fields);

        // remove fields
        $this->dbforge->drop_column('staffing_types', 'extra_time');
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('lesson_types', 'extra_time_head');
        $this->dbforge->drop_column('lesson_types', 'extra_time_lead');
        $this->dbforge->drop_column('lesson_types', 'extra_time_assistant');
    }
}