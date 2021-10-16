<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Plan_changes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add labels to plans
            $fields = array(
                'label_customer' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'addons_all'
                ),
                'label_customers' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'label_customer'
                ),
                'label_participant' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'label_customers'
                ),
                'label_participants' => array(
                    'type' => "VARCHAR",
                    'constraint' => 20,
                    'null' => TRUE,
                    'after' => 'label_participant'
                ),
                'default_project_types' => array(
                    'type' => "VARCHAR",
                    'constraint' => 200,
                    'default' => "Commercial
Funded",
                    'null' => FALSE,
                    'after' => 'label_participants'
                ),
                'bookings_timetable_own' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'bookings_timetable'
                ),
                'dashboard_bookings' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'default_project_types'
                ),
                'dashboard_staff' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_bookings'
                ),
                'dashboard_participants' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_staff'
                ),
                'dashboard_health_safety' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_participants'
                ),
                'dashboard_equipment' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_health_safety'
                ),
                'dashboard_availability' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_equipment'
                ),
                'dashboard_employee_of_month' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_availability'
                ),
                'dashboard_staff_birthdays' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'default' => 1,
                    'after' => 'dashboard_employee_of_month'
                ),
            );
            $this->dbforge->add_column('accounts_plans', $fields);

            // add schools plan defaults
            $data = array(
                'label_customer' => 'Venue',
                'label_customers' => 'Venues',
                'label_participant' => 'Participant',
                'label_participants' => 'Participants',
                'default_project_types' => 'Extra Curricular
Excursions
Events
Enrichment',
                'dashboard_staff' => 0,
                'dashboard_bookings' => 0,
                'dashboard_availability' => 0,
                'dashboard_employee_of_month' => 0
            );
            $where = array(
                'name' => 'Schools'
            );
            $this->db->update('accounts_plans', $data, $where, 1);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('accounts_plans', 'label_customer');
            $this->dbforge->drop_column('accounts_plans', 'label_customers');
            $this->dbforge->drop_column('accounts_plans', 'label_participant');
            $this->dbforge->drop_column('accounts_plans', 'label_participants');
            $this->dbforge->drop_column('accounts_plans', 'default_project_types');
            $this->dbforge->drop_column('accounts_plans', 'bookings_timetable_own');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_bookings');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_staff');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_participants');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_health_safety');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_equipment');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_availability');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_employee_of_month');
            $this->dbforge->drop_column('accounts_plans', 'dashboard_staff_birthdays');
        }
}