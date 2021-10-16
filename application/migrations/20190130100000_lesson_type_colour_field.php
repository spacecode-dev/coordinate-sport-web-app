<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_type_colour_field extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'colour' => array(
                'type' => 'VARCHAR',
				'constraint' => 20,
                'null' => TRUE,
                'after' => 'name'
            )
        );
        $this->dbforge->add_column('lesson_types', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('lesson_types', 'colour');
    }
}
