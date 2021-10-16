<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_export_data_email extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {
			
			$data = [
				[
					'key' => 'export_data_notification',
					'title' => 'Export Data Notification',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 441,
					'value' => 0,
					'tab' => 'general',
					'toggle_fields' => '',
					'instruction' => '',
					'description' => 'Edit the content of an alert sent to staff when the data protection officer exports a file.',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'data_export_notification_subject',
					'title' => 'Data Export Notification Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'order' => 442,
					'subsection' => 'export_data_notification',
					'value' => '',
					'instruction' => 'Available tags: {contact_first} {contact_last}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'data_export_notification_email',
					'title' => 'Data Export Notification Email',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'order' => 443,
					'subsection' => 'export_data_notification',
					'value' => '<p>Hi {contact_first},</p>
					<p> Please find below details of the recent file that has been exported by the data officer(s):</p>
					<p>{export_details}</p>',
					'instruction' => 'Available tags: {contact_first} {contact_last} {export_details}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'send_a_copy_of_data_export_notification_email_to',
					'title' => 'Send a copy of Data Export Notification Email to',
					'type' => 'email-multiple',
					'section' => 'emailsms',
					'order' => 444,
					'subsection' => 'export_data_notification',
					'value' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
			];
			
			foreach ($data as $field) {
				$this->db->insert('settings', $field);
			}
			
        }

        public function down() {
			$keys = [
				'export_data_notification',
				'data_export_notification_subject',
				'data_export_notification_email',
				'send_a_copy_of_data_export_notification_email_to'
			];

			foreach ($keys as $key) {
				$this->db->delete('settings', [
					'key' => $key
				], 1);
			}
		}
}
