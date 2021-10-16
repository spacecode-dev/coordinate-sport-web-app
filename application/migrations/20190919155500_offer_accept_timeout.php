<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offer_accept_timeout extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add main section fields with empty subsection and toggle other checkbox
            $data = [
                'key' => 'offer_accept_timeout',
                'title' => 'Offer & Accept (Auto) Timeout',
                'type' => 'number',
                'section' => 'general',
                'subsection' => 'general_general',
                'order' => 71,
                'value' => 24,
                'instruction' => 'The hours of the session offer time out.',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
            ];

            $this->db->insert('settings', $data);
		}

		public function down() {
            $this->db->delete('settings', [
                'key' => 'offer_accept_timeout'
            ], 1);
		}
}
