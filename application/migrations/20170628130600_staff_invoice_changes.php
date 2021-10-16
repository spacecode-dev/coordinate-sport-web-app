<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoice_changes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'invoiced' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'approverID'
                ),
            );
            $this->dbforge->add_column('timesheets', $fields);

            // add fields
            $fields = array(
                'timesheetID' => array(
                    'type' => "INT",
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'invoiceID'
                ),
            );
            $this->dbforge->add_column('staff_invoices_items', $fields);

            // add key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD INDEX (`timesheetID`)');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD CONSTRAINT `fk_staff_invoices_items_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE NO ACTION ON UPDATE CASCADE');

            // rename fields
            $fields = array(
                'timesheetID' => array(
                    'name' => 'timesheetID_old',
                    'type' => 'INT',
                    'constraint' => 11,
                    'null' => TRUE,
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('staff_invoices', $fields);

            // set previous timesheets to invoiced
            $where = array(
                'timesheetID_old IS NOT NULL' => NULL
            );
            $res = $this->db->from('staff_invoices')->where($where)->get();
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $where_update = array(
                        'timesheetID' => $row->timesheetID_old
                    );
                    $data_update = array(
                        'invoiced' => 1
                    );
                    $this->db->update('timesheets', $data_update, $where_update, 1);
                }
            }
        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` DROP FOREIGN KEY `fk_staff_invoices_items_timesheetID`');

            // remove fields
            $this->dbforge->drop_column('timesheets', 'invoiced');
            $this->dbforge->drop_column('staff_invoices_items', 'timesheetID');

            // rename fields
            $fields = array(
                'timesheetID_old' => array(
                    'name' => 'timesheetID',
                    'type' => 'INT',
                    'constraint' => 11
                )
            );
            $this->dbforge->modify_column('staff_invoices', $fields);
        }
}