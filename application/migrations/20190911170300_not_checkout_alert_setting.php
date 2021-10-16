<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Not_checkout_alert_setting extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add main section fields with empty subsection and toggle other checkbox
            $data = array(
                'order' => 27,
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'staff_checkin_outside_account_alert_emailsms')->update('settings', $data);

			$data = [
				[
					'key' => 'not_checkout_account_alert_emailsms',
					'title' => 'Not Checked Out - Account Alert',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 26,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				]
			];

			foreach ($data as $field) {
                $this->db->insert('settings', $field);
            }

			$data = array(
				'subsection' => 'not_checkout_account_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_not_checkout_account_body',
				'email_not_checkout_account_subject'
			]);

			$this->db->update('settings', $data);
		}

		public function down() {
            $keys = [
                'not_checkout_account_alert_emailsms'
            ];

            foreach ($keys as $key) {
                $this->db->delete('settings', [
                    'key' => $key
                ], 1);
            }

            $data = array(
                'order' => 26,
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );

            $this->db->where('key', 'staff_checkin_outside_account_alert_emailsms')->update('settings', $data);
		}
}
