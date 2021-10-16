<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Migrate_bookings_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// add fields
        $fields = array(
            'migratedID' => array(
                'type' => 'INT',
				'constraint' => 11,
				'default' => NULL,
                'null' => TRUE,
                'after' => 'byID'
            )
        );
        $this->dbforge->add_column('bookings_cart', $fields);

		// rename tables
		$this->dbforge->rename_table('bookings_individuals_monitoring', 'bookings_individuals_monitoring_old');
		$this->dbforge->rename_table('bookings_individuals_bikeability', 'bookings_individuals_bikeability_old');
		$this->dbforge->rename_table('bookings_individuals_sessions', 'bookings_individuals_sessions_old');
		$this->dbforge->rename_table('bookings_individuals', 'bookings_individuals_old');
    }

    public function down() {
		// rename tables
		$this->dbforge->rename_table('bookings_individuals_old', 'bookings_individuals');
		$this->dbforge->rename_table('bookings_individuals_sessions_old', 'bookings_individuals_sessions');
		$this->dbforge->rename_table('bookings_individuals_bikeability_old', 'bookings_individuals_bikeability');
		$this->dbforge->rename_table('bookings_individuals_monitoring_old', 'bookings_individuals_monitoring');

        // remove fields
		$this->dbforge->drop_column('bookings_cart', 'migratedID');
    }
}
