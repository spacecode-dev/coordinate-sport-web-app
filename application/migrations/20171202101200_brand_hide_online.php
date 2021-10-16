<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Brand_hide_online extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'hide_online' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'staff_performance_exclude_pupil_assessments'
            )
        );
        $this->dbforge->add_column('brands', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('brands', 'hide_online');
    }
}