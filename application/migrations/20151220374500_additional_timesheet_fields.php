<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Additional_timesheet_fields extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add additional fields to timesheet items and expenses
            $fields = array(
                'orgID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE,
                    'after' => 'timesheetID'
                ),
                'brandID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => FALSE,
                    'after' => 'orgID'
                ),
                'reason_desc' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200,
                    'null' => TRUE,
                    'after' => 'note'
                )
            );

            // add fields to items
            $this->dbforge->add_column('timesheets_items', $fields);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_items') . '` ADD FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // add fields to expenses
            $this->dbforge->add_column('timesheets_expenses', $fields);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_expenses') . '` ADD FOREIGN KEY (`brandID`) REFERENCES `' . $this->db->dbprefix('brands') . '`(`brandID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // change note to reason
            $fields = array(
                'note' => array(
                    'name' => 'reason',
                    'type' => "ENUM('travel','training','marketing','other')",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('timesheets_items', $fields);
            $this->dbforge->modify_column('timesheets_expenses', $fields);
        }

        public function down() {
            // remove columns added above
            $this->dbforge->drop_column('timesheets_items', 'orgID', TRUE);
            $this->dbforge->drop_column('timesheets_items', 'brandID', TRUE);
            $this->dbforge->drop_column('timesheets_items', 'reason_desc', TRUE);
            $this->dbforge->drop_column('timesheets_expenses', 'orgID', TRUE);
            $this->dbforge->drop_column('timesheets_expenses', 'brandID', TRUE);
            $this->dbforge->drop_column('timesheets_expenses', 'reason_desc', TRUE);

            // change reason to note
            $fields = array(
                'reason' => array(
                    'name' => 'note',
                    'type' => "VARCHAR",
                    'constraint' => 200,
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('timesheets_items', $fields);
            $this->dbforge->modify_column('timesheets_expenses', $fields);
        }
}