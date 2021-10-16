<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Account_status_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add status and status date fields
        $fields = array(
            'status' => array(
                'type' => "ENUM('trial','paid')",
                'default' => 'trial',
                'null' => FALSE,
                'after' => 'admin'
            ),
            'trial_until' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'after' => 'addon_timesheets'
            ),
            'paid_until' => array(
                'type' => 'DATE',
                'null' => TRUE,
                'after' => 'trial_until'
            )
        );
        $this->dbforge->add_column('accounts', $fields);

        // mark main and admin accounts paid
        $where = array(
            'accountID' => 1
        );
        $data = array(
            'status' => 'paid'
        );
        $this->db->update('accounts', $data, $where);
        $where = array(
            'admin' => 1
        );
        $this->db->update('accounts', $data, $where);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('accounts', 'status');
        $this->dbforge->drop_column('accounts', 'trial_until');
        $this->dbforge->drop_column('accounts', 'paid_until');
    }
}