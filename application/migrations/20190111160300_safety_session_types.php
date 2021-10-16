<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Safety_session_types extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {

        // add field to orgs_safety
        $fields = array(
            'typeID' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' =>  NULL,
                'after' => 'addressID'
            )
        );
        $this->dbforge->add_column('orgs_safety', $fields);

        // add key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_safety') . '` ADD INDEX (`typeID`)');

        // set foreign key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_safety') . '` ADD CONSTRAINT `fk_orgs_safety_typeID` FOREIGN KEY (`typeID`) REFERENCES `' . $this->db->dbprefix('lesson_types') . '`(`typeID`) ON DELETE NO ACTION ON UPDATE CASCADE');
    }

    public function down() {
        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_safety') . '` DROP FOREIGN KEY `fk_orgs_safety_typeID`');

        // remove field from orgs_safety, if exists
        if ($this->db->field_exists('typeID', 'orgs_safety')) {
            $this->dbforge->drop_column('orgs_safety', 'typeID');
        }
    }
}