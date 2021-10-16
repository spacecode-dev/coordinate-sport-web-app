<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoices_timesheets extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'linkID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'invoiceID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'timesheetID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('linkID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('invoiceID');
            $this->dbforge->add_key('timesheetID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_invoices_timesheets', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` ADD CONSTRAINT `fk_staff_invoices_timesheets_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` ADD CONSTRAINT `fk_staff_invoices_timesheets_invoiceID` FOREIGN KEY (`invoiceID`) REFERENCES `' . $this->db->dbprefix('staff_invoices') . '`(`invoiceID`) ON DELETE NO ACTION ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` ADD CONSTRAINT `fk_staff_invoices_timesheets_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // populate new table
    		$res = $this->db->from('staff_invoices_items')->group_by('timesheetID')->get();

            if ($res->result() > 0) {
        		foreach ($res->result() as $row) {
                    $data = array(
                        'accountID' => $row->accountID,
                        'timesheetID' => $row->timesheetID,
                        'invoiceID' => $row->invoiceID,
                        'added' => mdate('%Y-%m-%d %H:%i:%s'),
    					'modified' => mdate('%Y-%m-%d %H:%i:%s')
                    );
                    $this->db->insert('staff_invoices_timesheets', $data);
                }
            }
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheets_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheets_timesheetID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheets_invoiceID`');

            // remove table, if exists
            $this->dbforge->drop_table('staff_invoices_timesheets', TRUE);
        }
}