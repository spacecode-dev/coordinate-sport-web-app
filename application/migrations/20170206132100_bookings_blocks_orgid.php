<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_bookings_blocks_orgid extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'orgID' => array(
                'type' => 'INT',
                'null' => TRUE,
                'after' => 'bookingID'
            )
        );
        $this->dbforge->add_column('bookings_blocks', $fields);

        // set foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_blocks') . '` ADD CONSTRAINT `fk_bookings_blocks_orgID` FOREIGN KEY (`orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE');

    }

    public function down() {
        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_blocks') . '` DROP FOREIGN KEY `fk_bookings_blocks_orgID`');

        // remove fields
        $this->dbforge->drop_column('bookings_blocks', 'orgID');
    }
}