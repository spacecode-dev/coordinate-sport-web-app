<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Brands_activities extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand activities fields
            $fields = array(
                'id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'brandID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'activityID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('brandID');
            $this->dbforge->add_key('activityID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('brands_activities', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('brands_activities') . '` ADD FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('brands_activities') . '` ADD FOREIGN KEY (`activityID`) REFERENCES `' . $this->db->dbprefix('activities') . '`(`activityID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // remove timesheets expenses table, if exists
            $this->dbforge->drop_table('brands_activities', TRUE);
        }
}