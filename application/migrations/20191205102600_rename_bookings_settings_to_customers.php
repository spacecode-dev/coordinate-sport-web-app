<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_bookings_settings_to_customers extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// move section to customers
            $data = [
                'subsection' => 'customers_general',
            ];
			$where = [
				'key' => 'disable_prospects_automation'
			];
            $this->db->update('settings', $data, $where, 1);

			// rename bookings to customers
            $data = [
                'key' => 'customers_general',
				'title' => 'Customers'
            ];
			$where = [
				'key' => 'bookings_general'
			];
            $this->db->update('settings', $data, $where, 1);
		}

		public function down() {
			// rename customers to bookings
            $data = [
                'key' => 'bookings_general',
				'title' => 'Bookings'
            ];
			$where = [
				'key' => 'customers_general'
			];
            $this->db->update('settings', $data, $where, 1);

			// move section to bookings
            $data = [
                'subsection' => 'bookings_general',
            ];
			$where = [
				'key' => 'disable_prospects_automation'
			];
            $this->db->update('settings', $data, $where, 1);
		}
}
