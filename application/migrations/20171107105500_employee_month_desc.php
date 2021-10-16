<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Employee_month_desc extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify settings
        $data = array(
            'instruction' => '',
        );
        $where = array(
            'key' => 'employee_of_month'
        );
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {
        // modify settings
        $data = array(
            'instruction' => 'If not set, top employees from staff performance will be shown instead',
        );
        $where = array(
            'key' => 'employee_of_month'
        );
        $this->db->update('settings', $data, $where, 1);
    }
}