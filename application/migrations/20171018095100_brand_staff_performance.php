<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Brand_staff_performance extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'staff_performance_exclude_session_evaluations' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'mailchimp_id'
            ),
            'staff_performance_exclude_pupil_assessments' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'staff_performance_exclude_session_evaluations'
            )
        );
        $this->dbforge->add_column('brands', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('brands', 'staff_performance_exclude_session_evaluations');
        $this->dbforge->drop_column('brands', 'staff_performance_exclude_pupil_assessments');
    }
}