<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_by_id_offer_accept extends CI_Migration {
    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add role field field to timesheet items
        $fields = array(
            'byID' => array(
                'type' => "INT",
                'constraint' => 11,
                'null' => TRUE,
                'after' => 'staffID'
            )
        );

        $this->dbforge->add_column('offer_accept', $fields);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD INDEX (`byID`)');

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD CONSTRAINT `fk_offer_accept_byID` FOREIGN KEY (`byID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE NO ACTION ON UPDATE CASCADE');
    }

    public function down() {
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` DROP FOREIGN KEY `fk_offer_accept_byID`');

        $this->dbforge->drop_column('offer_accept', 'byID');
    }
}