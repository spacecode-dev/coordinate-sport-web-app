<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Additional_monitoring_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'monitoring4' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring3'
            ),
            'monitoring5' => array(
                'type' => 'VARCHAR',
                'constraint' => 100,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring4'
            ),
        );
        $this->dbforge->add_column('bookings', $fields);

        // add fields
        $fields = array(
            'monitoring4' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring3'
            ),
            'monitoring5' => array(
                'type' => 'VARCHAR',
                'constraint' => 255,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'monitoring4'
            ),
        );
        $this->dbforge->add_column('bookings_individuals_monitoring', $fields);
        $this->dbforge->add_column('bookings_attendance_names', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'monitoring4');
        $this->dbforge->drop_column('bookings', 'monitoring5');

        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring4');
        $this->dbforge->drop_column('bookings_individuals_monitoring', 'monitoring5');

        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring4');
        $this->dbforge->drop_column('bookings_attendance_names', 'monitoring5');
    }
}