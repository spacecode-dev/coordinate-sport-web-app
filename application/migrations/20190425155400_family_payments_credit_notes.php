<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Family_payments_credit_notes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // modify fields
            $fields = array(
                'contactID' => array(
                    'name' => 'contactID',
                    'type' => "INT",
					'constraint' => 11,
					'default' => NULL,
                    'null' => TRUE
                ),
				'method' => array(
                    'name' => 'method',
                    'type' => "ENUM('card', 'cash', 'cheque', 'online', 'other', 'childcare voucher', 'direct debit', 'credit note')",
                    'null' => FALSE
                )
            );
            $this->dbforge->modify_column('family_payments', $fields);

			// add columns
            $fields = array(
                'internal' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'byID'
                )
            );
			$this->dbforge->add_column('family_payments', $fields);
        }

        public function down() {
            // no going back

			// remove columns added above
			$this->dbforge->drop_column('family_payments', 'internal', TRUE);
        }
}
