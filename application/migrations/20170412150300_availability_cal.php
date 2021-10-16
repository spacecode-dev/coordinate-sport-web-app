<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Availability_cal extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'calID' => array(
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
                'brandID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
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
            $this->dbforge->add_key('calID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('byID');
            $this->dbforge->add_key('brandID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('availability_cals', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` ADD CONSTRAINT `fk_availability_cals_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` ADD CONSTRAINT `fk_availability_cals_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` ADD CONSTRAINT `fk_availability_cals_brandID` FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // cal activities table
            $fields = array(
                'linkID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'calID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'activityID' => array(
                    'type' => 'INT',
                    'constraint' => 11
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
            $this->dbforge->add_key('linkID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('calID');
            $this->dbforge->add_key('activityID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('availability_cals_activities', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` ADD CONSTRAINT `fk_availability_cals_activities_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` ADD CONSTRAINT `fk_availability_cals_activities_calID` FOREIGN KEY (`calID`) REFERENCES `' . $this->db->dbprefix('availability_cals') . '`(`calID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` ADD CONSTRAINT `fk_availability_cals_activities_activityID` FOREIGN KEY (`activityID`) REFERENCES `' . $this->db->dbprefix('activities') . '`(`activityID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // cal slots table
            $fields = array(
                'slotID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'calID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'startTime' => array(
                    'type' => 'TIME'
                ),
                'endTime' => array(
                    'type' => 'TIME'
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
            $this->dbforge->add_key('slotID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('calID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('availability_cals_slots', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_slots') . '` ADD CONSTRAINT `fk_availability_cals_slots_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_slots') . '` ADD CONSTRAINT `fk_availability_cals_slots_calID` FOREIGN KEY (`calID`) REFERENCES `' . $this->db->dbprefix('availability_cals') . '`(`calID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` DROP FOREIGN KEY `fk_availability_cals_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` DROP FOREIGN KEY `fk_availability_cals_byID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals') . '` DROP FOREIGN KEY `fk_availability_cals_brandID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` DROP FOREIGN KEY `fk_availability_cals_activities_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` DROP FOREIGN KEY `fk_availability_cals_activities_calID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_activities') . '` DROP FOREIGN KEY `fk_availability_cals_activities_activityID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_slots') . '` DROP FOREIGN KEY `fk_availability_cals_slots_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('availability_cals_slots') . '` DROP FOREIGN KEY `fk_availability_cals_slots_calID`');

            // remove tables, if exist
            $this->dbforge->drop_table('availability_cals', TRUE);
            $this->dbforge->drop_table('availability_cals_activities', TRUE);
            $this->dbforge->drop_table('availability_cals_slots', TRUE);
        }
}