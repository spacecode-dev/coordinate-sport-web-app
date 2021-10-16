<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Optional_ages extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// update settings
	        $data = array(
	           'instruction' => 'Set to 0 to disable, can be overridden at booking, block or session level',
	            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
	        );
			$where = array(
				'key' => 'min_age'
			);
	        $this->db->update('settings', $data, $where, 1);
			$where = array(
				'key' => 'max_age'
			);
	        $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
			// update settings
	        $data = array(
	           'instruction' => '',
	            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
	        );
			$where = array(
				'key' => 'min_age'
			);
	        $this->db->update('settings', $data, $where, 1);
			$where = array(
				'key' => 'max_age'
			);
	        $this->db->update('settings', $data, $where, 1);
        }
}
