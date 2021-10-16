<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_participant_subscriptions extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $fields = array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'auto_increment' => TRUE
            ),
            'childID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE 
            ),
            'contactID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
            ),
            'accountID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'subID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'gc_subscription_id' => array(
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => TRUE
            ),
            'gc_code' => array(
                'type' => 'VARCHAR',
                'constraint' => 6,
                'default' => NULL,
                'null' => TRUE,
                'unique' => TRUE,
            ),
            'status' => array(
                'type' => "ENUM('inactive','active','cancelled','expired')",
                'default' => 'inactive'
            ),
            'last_payment_date' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
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
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('accountID');
        $this->dbforge->add_key('childID');
        $this->dbforge->add_key('subID');
        $this->dbforge->add_key('contactID');

        // set table attributes
        $attributes = array(
            'ENGINE' => 'InnoDB'
        );

        // create table
        $this->dbforge->create_table('participant_subscriptions', FALSE, $attributes);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` ADD CONSTRAINT `fk_participant_subscriptions_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` ADD CONSTRAINT `fk_participant_subscriptions_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` ADD CONSTRAINT `fk_participant_subscriptions_subID` FOREIGN KEY (`subID`) REFERENCES `' . $this->db->dbprefix('subscriptions') . '`(`subID`) ON DELETE CASCADE ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` ADD CONSTRAINT `fk_participant_subscriptions_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down() {

        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` DROP FOREIGN KEY `fk_participant_subscriptions_accountID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` DROP FOREIGN KEY `fk_participant_subscriptions_childID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` DROP FOREIGN KEY `fk_participant_subscriptions_subID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('participant_subscriptions') . '` DROP FOREIGN KEY `fk_participant_subscriptions_contactID`');
        
        // remove tables, if exist
        $this->dbforge->drop_table('participant_subscriptions', TRUE);
    }
}