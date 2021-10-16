<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Integration_settings_update extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$data = array(
				'order' => '101',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
			    'key' => 'cc_processor'
            ])->update('settings', $data);

            $data = array(
                'order' => '102',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'require_full_payment'
            ])->update('settings', $data);

            $data = array(
                'order' => '132',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'mailchimp_key'
            ])->update('settings', $data);

		}

		public function down() {
            $data = array(
                'order' => '120',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'cc_processor'
            ])->update('settings', $data);

            $data = array(
                'order' => '121',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'require_full_payment'
            ])->update('settings', $data);

            $data = array(
                'order' => '100',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'mailchimp_key'
            ])->update('settings', $data);
		}
}
