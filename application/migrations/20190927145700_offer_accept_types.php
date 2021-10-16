<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offer_accept_types extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {

        // add fields
        $fields = array(
            'groupID' => array(
                'type' => "INT",
                'constraint' => 11,
                'null' => TRUE,
                'after' => 'staffID'
            ),
            'offer_type' => array(
                'type' => "ENUM('auto', 'individual', 'group')",
                'null' => FALSE,
                'after' => 'groupID'
            )
        );
        $this->dbforge->add_column('offer_accept', $fields);

        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` ADD CONSTRAINT `fk_group_offerID` FOREIGN KEY (`groupID`) REFERENCES `' . $this->db->dbprefix('groups') . '`(`groupID`) ON DELETE CASCADE ON UPDATE CASCADE');
    }

    public function down() {
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('offer_accept') . '` DROP FOREIGN KEY `fk_group_offerID`');
        // remove fields
        $this->dbforge->drop_column('offer_accept', 'groupID');
        $this->dbforge->drop_column('offer_accept', 'offer_type');
    }
}