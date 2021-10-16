<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Edit_settings_subscription_emails extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$where = array(
				'key' => 'subscription_emailsms'
			);
			$data = array(
				'title' => 'Participant Subscription Emails'
			);
			$this->db->update('settings', $data, $where);

			//Staff Subscription
			$data = [
				[
					'key' => 'staff_subscription_emailsms',
					'title' => 'Staff Subscription Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 5,
					'value' => 0,
					'tab' => 'staff',
					'toggle_fields' => '',
					'instruction' => '',
					'description' => 'Edit the details of the staff\'s subscription emails.',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'staff_cancel_subscription_subject',
					'title' => 'Cancel Subscription Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'staff_subscription_emailsms',
					'order' => 300,
					'value' => '{contact_first} {contact_last} has cancelled a subscription',
					'instruction' => 'Available tags: {contact_first} {contact_last}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'staff_cancel_subscription_body',
					'title' => 'Cancel Subscription',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'staff_subscription_emailsms',
					'order' => 301,
					'value' => '
                <p>This is an automated email to notify you that {contact_first} {contact_last} has cancelled their subscription.</p>
                <p>{subscription_details}</p>
                <p>To check if everything is in order please login to your account at {login_link}</p>',
					'instruction' => 'Available tags: {contact_first}, {contact_last}, {subscription_details}, {login_link}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				]
			];

			foreach ($data as $field) {
				$this->db->insert('settings', $field);
			}
		}

		public function down() {
			$where = array(
				'key' => 'subscription_emailsms'
			);
			$data = array(
				'title' => 'Subscription Emails'
			);
			$this->db->update('settings', $data, $where);

			//Delete records
			$keys = [
				'staff_subscription_emailsms',
				'staff_cancel_subscription_subject',
				'staff_cancel_subscription_body'
			];

			foreach ($keys as $key) {
				$this->db->delete('settings', [
					'key' => $key
				], 1);
			}
		}
}
