<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Active_depts_types_activities extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // define fields
        $fields = array(
            'active' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 1,
				'null' => FALSE,
				'after' => 'byID'
            )
        );
		$this->dbforge->add_column('activities', $fields);
		$this->dbforge->add_column('brands', $fields);
		$this->dbforge->add_column('lesson_types', $fields);
    }

    public function down() {
		// remove columns added above
		$this->dbforge->drop_column('active', 'activities', TRUE);
		$this->dbforge->drop_column('active', 'brands', TRUE);
		$this->dbforge->drop_column('active', 'lesson_types', TRUE);
    }
}
