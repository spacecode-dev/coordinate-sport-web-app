<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_subscription_tables extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        
        $fields = array (
            'subID' => array (
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'accountID' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'bookingID' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'familyID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => true
            ),
            'subName' => array(
                'type' => 'VARCHAR',
                'constraint' => 100
            ),
            'frequency' => array(
                'type' => "ENUM('weekly', 'monthly', 'yearly')",
                'null' => FALSE 
            ),
            'price' => array(
                'type' => "DECIMAL(10,2)",
            ),
            'individual_subscription' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
            ),
            'endDate' => array(
                'type' => 'DATE',
                'null' => true
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
        $this->dbforge->add_key('subID', TRUE);
        $this->dbforge->add_key('accountID');
        $this->dbforge->add_key('bookingID');
        $this->dbforge->add_key('familyID');

        // set table attributes
        $attributes = array(
            'ENGINE' => 'InnoDB'
        );

        // create table
        $this->dbforge->create_table('subscriptions', FALSE, $attributes);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` ADD CONSTRAINT `fk_subscriptions_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` ADD CONSTRAINT `fk_subscriptions_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` ADD CONSTRAINT `fk_subscriptions_familyID` FOREIGN KEY (`familyID`) REFERENCES `' . $this->db->dbprefix('family') . '`(`familyID`) ON DELETE CASCADE ON UPDATE CASCADE');

        // create sessions link table
        $fields = array(
            'linkID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'subID' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'accountID' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'bookingID' => array(
                'type' => 'INT',
                'constraint' => 11
            ),
            'typeID' => array(
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
        $this->dbforge->add_key('subID');
        $this->dbforge->add_key('accountID');
        $this->dbforge->add_key('bookingID');
        $this->dbforge->add_key('typeID');

        // set table attributes
        $attributes = array(
            'ENGINE' => 'InnoDB'
        );

        // create table
        $this->dbforge->create_table('subscriptions_lessons_types', FALSE, $attributes);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` ADD CONSTRAINT `fk_subscriptions_lessons_types_subID` FOREIGN KEY (`subID`) REFERENCES `' . $this->db->dbprefix('subscriptions') . '`(`subID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` ADD CONSTRAINT `fk_subscriptions_lessons_types_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` ADD CONSTRAINT `fk_subscriptions_lessons_types_bookingID` FOREIGN KEY (`bookingID`) REFERENCES `' . $this->db->dbprefix('bookings') . '`(`bookingID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` ADD CONSTRAINT `fk_subscriptions_lessons_types_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE CASCADE ON UPDATE CASCADE');

    }

    public function down() {
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` DROP FOREIGN KEY `fk_subscriptions_accountID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` DROP FOREIGN KEY `fk_subscriptions_bookingID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions') . '` DROP FOREIGN KEY `fk_subscriptions_familyID`');

        $this->dbforge->drop_table('subscriptions', TRUE);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` DROP FOREIGN KEY `fk_subscriptions_lessons_types_subID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` DROP FOREIGN KEY `fk_subscriptions_lessons_types_accountID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` DROP FOREIGN KEY `fk_subscriptions_lessons_types_bookingID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('subscriptions_lessons_types') . '` DROP FOREIGN KEY `fk_subscriptions_lessons_types_typeID`');

        $this->dbforge->drop_table('subscriptions_lesson_types', TRUE);

    }
}