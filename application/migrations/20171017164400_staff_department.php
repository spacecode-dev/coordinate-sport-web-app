<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_department extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add field to staff
        $fields = array(
            'brandID' => array(
                'type' => 'INT',
                'constraint' => 3,
                'default' => NULL,
                'null' => TRUE,
                'after' => 'teamleaderID'
            )
        );
        $this->dbforge->add_column('staff', $fields);

        // add key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff') . '` ADD INDEX (`brandID`)');

        // set foreign key
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff') . '` ADD CONSTRAINT `fk_staff_brandID` FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE');
    }

    public function down() {
        // remove foreign keys
        $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff') . '` DROP FOREIGN KEY `fk_staff_brandID`');

        // remove fields
        $this->dbforge->drop_column('staff', 'brandID');

    }
}