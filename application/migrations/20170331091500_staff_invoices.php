<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoices extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'invoiceID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'auto_increment' => TRUE
                ),
                'accountID' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'staffID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'timesheetID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                ),
                'sent' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0
                ),
                'number' => array(
                    'type' => 'INT',
                    'constraint' => 11
                ),
                'date' => array(
                    'type' => 'DATE'
                ),
                'subject' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200
                ),
                'buyer_id' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50
                ),
                'utr' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 50
                ),
                'bank_name' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 100
                ),
                'bank_account' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20
                ),
                'bank_sort_code' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 20
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
                ),
                'added' => array(
                    'type' => 'DATETIME'
                ),
                'modified' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                ),
                'sent_date' => array(
                    'type' => 'DATETIME',
                    'null' => TRUE
                )
            );
            $this->dbforge->add_field($fields);

            // add keys
            $this->dbforge->add_key('invoiceID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('staffID');
            $this->dbforge->add_key('timesheetID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_invoices', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` ADD CONSTRAINT `fk_staff_invoices_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` ADD CONSTRAINT `fk_staff_invoices_staffID` FOREIGN KEY (`staffID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` ADD CONSTRAINT `fk_staff_invoices_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE');

            // invoice items
            // define fields
            $fields = array(
                'rowID' => array(
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
                    'constraint' => 11,
                ),
                'type' => array(
                    'type' => "ENUM('item', 'expense')"
                ),
                'itemID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => NULL
                ),
                'expenseID' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'default' => NULL
                ),
                'desc' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 200
                ),
                'amount' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '8,2'
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
            $this->dbforge->add_key('rowID', TRUE);
            $this->dbforge->add_key('accountID');
            $this->dbforge->add_key('invoiceID');
            $this->dbforge->add_key('itemID');
            $this->dbforge->add_key('expenseID');

            // set table attributes
            $attributes = array(
                'ENGINE' => 'InnoDB'
            );

            // create table
            $this->dbforge->create_table('staff_invoices_items', FALSE, $attributes);

            // set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD CONSTRAINT `fk_staff_invoices_items_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD CONSTRAINT `fk_staff_invoices_items_invoiceID` FOREIGN KEY (`invoiceID`) REFERENCES `' . $this->db->dbprefix('staff_invoices') . '`(`invoiceID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD CONSTRAINT `fk_staff_invoices_items_itemID` FOREIGN KEY (`itemID`) REFERENCES `' . $this->db->dbprefix('timesheets_items') . '`(`itemID`) ON DELETE CASCADE ON UPDATE CASCADE');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` ADD CONSTRAINT `fk_staff_invoices_items_expenseID` FOREIGN KEY (`expenseID`) REFERENCES `' . $this->db->dbprefix('timesheets_expenses') . '`(`expenseID`) ON DELETE CASCADE ON UPDATE CASCADE');

        }

        public function down() {
            // remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` DROP FOREIGN KEY `fk_staff_invoices_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` DROP FOREIGN KEY `fk_staff_invoices_staffID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices') . '` DROP FOREIGN KEY `fk_staff_invoices_timesheetID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` DROP FOREIGN KEY `fk_staff_invoices_items_accountID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` DROP FOREIGN KEY `fk_staff_invoices_items_invoiceID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` DROP FOREIGN KEY `fk_staff_invoices_items_itemID`');
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('staff_invoices_items') . '` DROP FOREIGN KEY `fk_staff_invoices_items_expenseID`');

            // remove tables, if exist
            $this->dbforge->drop_table('staff_invoices', TRUE);
            $this->dbforge->drop_table('staff_invoices_items', TRUE);
        }
}