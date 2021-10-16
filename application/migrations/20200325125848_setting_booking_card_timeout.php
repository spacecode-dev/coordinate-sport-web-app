<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_booking_card_timeout extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// add new section
			$data = [
				'key' => 'booking_card_timeout_unit',
				'title' => 'Booking Cart Timeout',
				'type' => 'select',
				'options' =>
					"1 : Hours
					 2 : Days"
				,
				'section' => 'general',
				'subsection' => 'bookings_general',
				'order' => 2,
				'toggle_fields' => '',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$this->db->insert('settings', $data);

			// add setting
			$data = [
				'key' => 'booking_card_timeout_amount',
				'title' => 'Amount of Time',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'bookings_general',
				'order' => 3,
				'toggle_fields' => '',
				'instruction' => 'Set to 0 to disable the timeout period',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			];
			$this->db->insert('settings', $data);
		}

		public function down() {
			$this->db->delete('settings', [
				'key' => 'booking_card_timeout_unit'
			], 1);
			$this->db->delete('settings', [
				'key' => 'booking_card_timeout_amount'
			], 1);
		}
}
