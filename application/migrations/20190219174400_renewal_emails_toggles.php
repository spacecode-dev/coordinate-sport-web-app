<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Renewal_emails_toggles extends CI_Migration {

		private $fields = array();

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
			// define fields
			$this->fields = array(
				array(
					'key' => 'send_renewal_alert',
					'title' => 'Send Renewal Email Alert',
					'type' => 'checkbox',
					'section' => 'emailsms',
					'order' => 249,
					'value' => '1',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'send_renewal_alert_subject',
					'title' => 'Account Renewal Alert Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'order' => 250,
					'value' => 'Account Expiring',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'send_renewal_alert_message',
					'title' => 'Account Renewal Alert Email',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'order' => 251,
					'value' => '<p>Dear Support</p><p>This is an automated email to notify you that {account_name} account will be expiring on {account_date}</p><p>Please take action on this request or forward to the applicable Account Manager.</p><p>Thank You</p>',
					'instruction' => 'Available tags: {account_name}, {account_date}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
                array(
                    'key' => 'send_renewal_alert_recipient',
                    'title' => 'Account Renewal Alert Recipient',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 252,
                    'value' => 'support@coordinate.cloud',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
			);
		}

		public function up() {
			// bulk insert
			$this->db->insert_batch('settings', $this->fields);
		}

		public function down() {
			// remove new settings
			$where_in = array();
			foreach ($this->fields as $field) {
				$where_in[] = $field['key'];
			}
			$this->db->from('settings')->where_in('key', $where_in)->delete();
		}
}