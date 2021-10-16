<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_performance_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add field to staff
        $fields = array(
            'target_observation_score' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'target_utilisation'
            )
        );
        $this->dbforge->add_column('staff', $fields);

        // add field to staff notes
        $fields = array(
            'observation_score' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'type'
            )
        );
        $this->dbforge->add_column('staff_notes', $fields);

        // add field to staff exceptions
        $fields = array(
            'type' => array(
                'type' => "ENUM('authorised', 'unauthorised', 'other')",
                'default' => 'other',
                'null' => FALSE,
                'after' => 'to'
            )
        );
        $this->dbforge->add_column('staff_availability_exceptions', $fields);

        // modify fields
        $fields = array(
            'type' => array(
                'name' => 'type',
                'type' => "ENUM('feedbackpositive', 'feedbacknegative', 'observation', 'induction', 'appraisal', 'disciplinary', 'misc', 'payroll', 'pupilassessment', 'late')",
                'null' => FALSE,
            )
        );
        $this->dbforge->modify_column('staff_notes', $fields);

        // modify settings
        $data = array(
            'instruction' => 'If not set, top employees from staff performance will be shown instead',
        );
        $where = array(
            'key' => 'employee_of_month'
        );
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff', 'target_observation_score');
        $this->dbforge->drop_column('staff_notes', 'observation_score');
        $this->dbforge->drop_column('staff_availability_exceptions', 'type');

        // modify fields
        $fields = array(
            'type' => array(
                'name' => 'type',
                'type' => "ENUM('feedbackpositive', 'feedbacknegative', 'observation', 'induction', 'appraisal', 'disciplinary', 'misc', 'payroll')",
                'null' => FALSE,
            )
        );
        $this->dbforge->modify_column('staff_notes', $fields);

        // modify settings
        $data = array(
            'instruction' => '',
        );
        $where = array(
            'key' => 'employee_of_month'
        );
        $this->db->update('settings', $data, $where, 1);
    }
}