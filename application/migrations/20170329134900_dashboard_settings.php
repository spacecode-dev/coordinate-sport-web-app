<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dashboard_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // define fields for defaults
            $fields = array(
                'key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50
                ),
                'title' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'order' => array(
                    'type' => 'INT',
                    'constraint' => 5,
                    'default' => NULL
                ),
                'section' => array(
                    'type' => "ENUM('bookings','staff','participants','safety','equipment')",
                    'default' => 'bookings'
                ),
                'value_amber' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => NULL
                ),
                'value_red' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => NULL
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('key', TRUE);

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('settings_dashboard', FALSE, $attributes);

            // define fields
            $fields = array(
                'settingID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'byID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE
                ),
                'key' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50
                ),
                'value_amber' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => NULL
                ),
                'value_red' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => NULL
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('settingID', TRUE);
            $this->dbforge->add_key('key');
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('accounts_settings_dashboard', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` ADD CONSTRAINT `fk_accounts_settings_dashboard_key` FOREIGN KEY (`key`) REFERENCES `' . $this->db->dbprefix('settings_dashboard') . '`(`key`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` ADD CONSTRAINT `fk_accounts_settings_dashboard_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` ADD CONSTRAINT `fk_accounts_settings_dashboard_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // map alerts
            $dashboard_alerts = array(
                'bookings_no_staff' => array(
                    'title' => 'Sessions with No Staff',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_inactive_staff' => array(
                    'title' => 'Sessions with Inactive Staff',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_availability_exceptions' => array(
                    'title' => 'Availability Exception Conflicts',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_unconfirmed' => array(
                    'title' => 'Unconfirmed Bookings',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_provisional_blocks' => array(
                    'title' => 'Provisional Blocks',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_renewaldue' => array(
                    'title' => 'Bookings due for Renewal',
                    'section' => 'bookings',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
    			'bookings_uninvoiced' => array(
                    'title' => 'Uninvoiced Bookings',
                    'section' => 'bookings',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
    			'bookings_unsent_invoices' => array(
                    'title' => 'Unsent Invoices',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'bookings_no_lessons' => array(
                    'title' => 'Bookings with No Sessions',
                    'section' => 'bookings',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
    			'staff_mandatory_expiring'  => array(
                    'title' => 'Expired/Expiring Mandatory Qualifications',
                    'section' => 'staff',
                    'value_amber' => '3 month',
                    'value_red' => '2 week'
                ),
    			'staff_additional_expiring'  => array(
                    'title' => 'Expired/Expiring Additional Qualifications',
                    'section' => 'staff',
                    'value_amber' => '3 month',
                    'value_red' => '2 week'
                ),
    			'staff_availability_exceptions'  => array(
                    'title' => 'Upcoming Availability Exceptions (Holiday, etc)',
                    'section' => 'staff',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
    			'staff_probations'  => array(
                    'title' => 'Probations Due',
                    'section' => 'staff',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
    			'staff_driving'  => array(
                    'title' => 'Driving Expiring/Missing Declaration',
                    'section' => 'staff',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
    			'staff_website_due'  => array(
                    'title' => 'Staff Due For Web Site',
                    'section' => 'staff',
                    'value_amber' => '6 month',
                    'value_red' => '1 year'
                ),
                'staff_birthdays'  => array(
                    'title' => 'Staff Birthday',
                    'section' => 'staff',
                    'value_amber' => '45 day',
                    'value_red' => '2 week'
                ),
                'families_outstanding' => array(
                    'title' => 'Bookings with Outstanding Balances',
                    'section' => 'participants',
                    'value_amber' => '0 day',
                    'value_red' => '2 week'
                ),
                'safety_docs' => array(
                    'title' => 'Expired or Missing',
                    'section' => 'safety',
                    'value_amber' => '1 month',
                    'value_red' => '2 week'
                ),
                'equipment_late' => array(
                    'title' => 'Late Equipment',
                    'section' => 'equipment',
                    'value_amber' => '0 day',
                    'value_red' => '2 week'
                ),
            );

            $order = 1;
            foreach ($dashboard_alerts as $key => $values) {
                $data = array(
                    'key' => $key,
                    'title' => $values['title'],
                    'order' => $order,
                    'section' => $values['section'],
                    'value_amber' => $values['value_amber'],
                    'value_red' => $values['value_red'],
                    'added' => mdate('%Y-%m-%d %H:%i:%s'),
                );
                $this->db->insert('settings_dashboard', $data);
                $order++;
            }
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` DROP FOREIGN KEY `fk_accounts_settings_dashboard_key`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` DROP FOREIGN KEY `fk_accounts_settings_dashboard_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('accounts_settings_dashboard') . '` DROP FOREIGN KEY `fk_accounts_settings_dashboard_byID`');

            // remove table, if exists
            $this->dbforge->drop_table('accounts_settings_dashboard', TRUE);
            $this->dbforge->drop_table('settings_dashboard', TRUE);
        }
}
