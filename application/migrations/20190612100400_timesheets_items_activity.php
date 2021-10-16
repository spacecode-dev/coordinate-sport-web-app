<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Timesheets_items_activity extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add additional fields to timesheet items
            $fields = array(
                'activityID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE,
                    'after' => 'brandID'
                )
            );

            // add fields to items
            $this->dbforge->add_column('timesheets_items', $fields);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`activityID`) REFERENCES `' . $this->db->dbprefix('activities') . '`(`activityID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // remove columns added above
            $this->dbforge->drop_column('timesheets_items', 'activityID', TRUE);
        }
}
