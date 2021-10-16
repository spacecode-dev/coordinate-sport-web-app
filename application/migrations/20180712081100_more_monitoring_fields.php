<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_More_monitoring_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'monitoring6' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring5'
            ),
            'monitoring7' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring6'
            ),
            'monitoring8' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring7'
            ),
            'monitoring9' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring8'
            ),
            'monitoring10' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring9'
            ),
        );
        $this->dbforge->add_column('bookings', $fields);

        // add fields
        $fields = array(
            'monitoring6' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring5'
            ),
            'monitoring7' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring6'
            ),
            'monitoring8' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring7'
            ),
            'monitoring9' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring8'
            ),
            'monitoring10' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring9'
            ),
        );
        $this->dbforge->add_column('bookings_individuals_monitoring', $fields);
        $this->dbforge->add_column('bookings_attendance_names', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'monitoring6');
        $this->dbforge->drop_column('bookings', 'monitoring7');
        $this->dbforge->drop_column('bookings', 'monitoring8');
        $this->dbforge->drop_column('bookings', 'monitoring9');
        $this->dbforge->drop_column('bookings', 'monitoring10');


        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring6');
        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring7');
        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring8');
        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring9');
        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring10');

        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring6');
        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring7');
        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring8');
        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring9');
        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring10');
    }
}