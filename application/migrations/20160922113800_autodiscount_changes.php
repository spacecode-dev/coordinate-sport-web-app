<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Autodiscount_changes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // modify fields - set to generic while we map values
        $fields = array(
            'autodiscount' => array(
                'name' => 'autodiscount',
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'off'
            )
        );
        $this->dbforge->modify_column('bookings', $fields);

        // update existing autodiscounts to percentage
        $where = array(
            'autodiscount' => 1
        );
        $data = array(
            'autodiscount' => 'percentage'
        );
        $this->db->update('bookings', $data, $where);

        // update others to off
        $where = array(
            'autodiscount !=' => 'percentage'
        );
        $data = array(
            'autodiscount' => 'off'
        );
        $this->db->update('bookings', $data, $where);

        // modify fields - remove 1 option
        $fields = array(
            'autodiscount' => array(
                'name' => 'autodiscount',
                'type' => "ENUM('off', 'percentage', 'amount')",
                'null' => FALSE,
                'default' => 'off'
            ),
            'autodiscount_amount' => array(
                'name' => 'autodiscount_amount',
                'type' => 'DECIMAL(8,2)',
                'null' => FALSE,
                'default' => 10
            )
        );
        $this->dbforge->modify_column('bookings', $fields);
    }

    public function down() {
        // modify fields
        $fields = array(
            'autodiscount' => array(
                'name' => 'autodiscount',
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => 'off'
            )
        );
        $this->dbforge->modify_column('bookings', $fields);

        // update existing autodiscounts to percentage
        $where = array(
            'autodiscount' => 'percentage'
        );
        $data = array(
            'autodiscount' => 1
        );
        $this->db->update('bookings', $data, $where);

        // modify fields - back to original
        $fields = array(
            'autodiscount' => array(
                'name' => 'autodiscount',
                'type' => "TINYINT",
                'constraint' => 1,
                'null' => FALSE,
                'default' => 1
            ),
            'autodiscount_amount' => array(
                'name' => 'autodiscount_amount',
                'type' => 'INT(3)',
                'null' => FALSE,
                'default' => 10
            )
        );
        $this->dbforge->modify_column('bookings', $fields);
    }
}