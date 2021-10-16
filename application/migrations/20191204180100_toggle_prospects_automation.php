<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Toggle_prospects_automation extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

            $data = [
                'key' => 'disable_prospects_automation',
                'title' => 'Deactivate Prospective to Live Automation',
                'type' => 'checkbox',
                'section' => 'general',
                'subsection' => 'bookings_general',
                'order' => 1,
                'value' => 0,
                'instruction' => 'Tick this box if you do not require your customers to be automatically catergorised as Live Customers rather than prospective customers when there are bookings assigned to them.',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
            ];

            $this->db->insert('settings', $data);
		}

		public function down() {
            $this->db->delete('settings', [
                'key' => 'disable_prospects_automation'
            ], 1);
		}
}
