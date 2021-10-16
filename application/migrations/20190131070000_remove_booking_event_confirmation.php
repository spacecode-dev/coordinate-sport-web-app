<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_booking_event_confirmation extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // rename field
            $fields = array(
                'bookingconfirmation' => array(
                    'name' => 'bookingconfirmation_old',
                    'type' => 'TEXT',
                    'null' => TRUE
                ),
				'eventconfirmation' => array(
                    'name' => 'eventconfirmation_old',
                    'type' => 'TEXT',
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }

        public function down() {
			// rename field
            $fields = array(
                'bookingconfirmation_old' => array(
                    'name' => 'bookingconfirmation',
                    'type' => 'TEXT',
                    'null' => TRUE
                ),
				'eventconfirmation_old' => array(
                    'name' => 'eventconfirmation',
                    'type' => 'TEXT',
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);
        }
}