<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Renewal_reminder_changes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // rename
            $fields = array(
                'renewal_reminder_month' => array(
                    'name' => 'renewal_reminder_1',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                ),
                'renewal_reminder_2week' => array(
                    'name' => 'renewal_reminder_2',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // new fields
            $fields = array(
                'renewal_reminder_3' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'renewal_reminder_2'
                ),
                'renewal_reminder_4' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'renewal_reminder_3'
                )
            );
            $this->dbforge->add_column('bookings', $fields);
        }

        public function down() {
            // rename
            $fields = array(
                'renewal_reminder_1' => array(
                    'name' => 'renewal_reminder_month',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                ),
                'renewal_reminder_2' => array(
                    'name' => 'renewal_reminder_2week',
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('bookings', $fields);

            // remove fields
            $this->dbforge->drop_column('bookings', 'renewal_reminder_3');
            $this->dbforge->drop_column('bookings', 'renewal_reminder_4');
        }
}