<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Global_settings_update extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$data = array(
				'order' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where([
			    'key' => 'max_invalid_logins'
            ])->update('settings', $data);

            $data = array(
                'order' => '2',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'email_from_default'
            ])->update('settings', $data);

            $data = array(
                'order' => '3',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'email_reset_password'
            ])->update('settings', $data);

		}

		public function down() {
            $data = array(
                'order' => '10',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'max_invalid_logins'
            ])->update('settings', $data);

            $data = array(
                'order' => '1',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'email_from_default'
            ])->update('settings', $data);

            $data = array(
                'order' => '10',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where([
                'key' => 'email_reset_password'
            ])->update('settings', $data);
		}
}
