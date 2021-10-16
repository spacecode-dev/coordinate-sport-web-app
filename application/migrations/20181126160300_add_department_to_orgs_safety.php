<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_department_to_orgs_safety extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add column
            $fields = array(
                'brandID' => array(
                    'type' => "INT",
                    'constraint' => 11,
                    'after' => 'byID'
                )
            );
            $this->dbforge->add_column('orgs_safety', $fields);

            $this->dbforge->add_key('brandID');

            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_safety') . '` ADD CONSTRAINT `fk_safety_brand` FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove fields
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('orgs_safety') . '` DROP FOREIGN KEY `fk_safety_brand`');

            $this->dbforge->drop_column('orgs_safety', 'active');
        }
}