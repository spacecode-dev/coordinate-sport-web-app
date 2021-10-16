<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_bookings_cart_childcare_vouchers_relationship extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// remove incorrect relationship
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` DROP FOREIGN KEY `fk_bookings_cart_childcarevoucher_providerID`');

			// add back
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_cart') . '` ADD CONSTRAINT `fk_bookings_cart_childcarevoucher_providerID` FOREIGN KEY (`childcarevoucher_providerID`) REFERENCES `' . $this->db->dbprefix('settings_childcarevoucherproviders') . '`(`providerID`) ON DELETE NO ACTION ON UPDATE CASCADE');
        }

        public function down() {
            // no going back
        }
}
