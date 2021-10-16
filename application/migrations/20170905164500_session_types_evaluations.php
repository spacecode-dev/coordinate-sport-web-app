<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Session_types_evaluations extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'session_evaluations' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'birthday_tab'
            )
        );
        $this->dbforge->add_column('lesson_types', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('lesson_types', 'session_evaluations');
    }
}