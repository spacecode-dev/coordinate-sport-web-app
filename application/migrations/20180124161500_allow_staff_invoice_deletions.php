<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Allow_staff_invoice_deletions extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheets_invoiceID`');

            // set foreign key
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_timesheets') . '` ADD CONSTRAINT `fk_staff_invoices_timesheets_invoiceID` FOREIGN KEY (`invoiceID`) REFERENCES `' . $this->db->dbprefix('staff_invoices') . '`(`invoiceID`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        public function down() {
            // no going back
        }
}