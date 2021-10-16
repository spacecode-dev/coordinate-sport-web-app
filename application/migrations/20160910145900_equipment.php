<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Equipment extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'type' => array(
                'type' => "ENUM('staff', 'org', 'contact', 'child')",
                'default' => 'staff',
                'null' => FALSE,
                'after' => 'equipmentID'
            ),
            'orgID' => array(
                'type' => 'INT',
                'null' => TRUE,
                'after' => 'staffID'
            ),
            'contactID' => array(
                'type' => 'INT',
                'null' => TRUE,
                'after' => 'orgID'
            ),
            'childID' => array(
                'type' => 'INT',
                'null' => TRUE,
                'after' => 'contactID'
            )
        );
        $this->dbforge->add_column('equipment_bookings', $fields);

        // set foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` ADD CONSTRAINT `fk_equipment_bookings_orgID` FOREIGN KEY (`orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` ADD CONSTRAINT `fk_equipment_bookings_contactID` FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` ADD CONSTRAINT `fk_equipment_bookings_childID` FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE NO ACTION ON UPDATE CASCADE');
    }

    public function down() {
        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` DROP FOREIGN KEY `fk_equipment_bookings_orgID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` DROP FOREIGN KEY `fk_equipment_bookings_contactID`');
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('equipment_bookings') . '` DROP FOREIGN KEY `fk_equipment_bookings_childID`');

        // remove fields
        $this->dbforge->drop_column('equipment_bookings', 'type');
        $this->dbforge->drop_column('equipment_bookings', 'orgID');
        $this->dbforge->drop_column('equipment_bookings', 'contactID');
        $this->dbforge->drop_column('equipment_bookings', 'childID');
    }
}