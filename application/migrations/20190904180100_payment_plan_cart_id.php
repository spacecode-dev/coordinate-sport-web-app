<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Payment_plan_cart_id extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'cartID' => array(
                    'type' => 'INT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'contactID'
                )
            );
			$this->dbforge->add_column('family_payments_plans', $fields);

			// set foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` ADD CONSTRAINT `fk_family_payments_plans_cartID` FOREIGN KEY (`cartID`) REFERENCES `' . $this->db->dbprefix('bookings_cart') . '`(`cartID`) ON DELETE SET NULL ON UPDATE CASCADE');
        }

        public function down() {
			// remove foreign keys
            $this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_payments_plans') . '` DROP FOREIGN KEY `fk_family_payments_plans_cartID`');

			// remove columns added above
			$this->dbforge->drop_column('family_payments_plans', 'cartID', TRUE);
        }
}
