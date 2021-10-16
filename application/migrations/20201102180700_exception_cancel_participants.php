<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_exception_cancel_participants extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			// define new settings
            $data = array(
				array(
					'key' => 'email_exception_participant_cancellation',
					'title' => 'Exception - Participant Cancellation',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'customer_exception_emails_emailsms',
					'order' => 63,
					'value' => '
					<p>Dear {participant_first},</p>
 					<p>We are writing to let you know that we have <span data-dobid="hdw">unfortunately</span> had to cancel some of your sessions. Please see the details below for the session that this applies:</p>
 					<p>{details}</p>
 					<p>If you have any queries please do not hesitate to contact us.</p>
 					<p>Many Thanks,<br><br>{org_name}</p>',
					'instruction' => 'Available Tags: {participant_first}, {details}, {org_name}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				),
				array(
					'key' => 'email_exception_participant_cancellation_subject',
					'title' => 'Exception - Participants Cancellation Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'customer_exception_emails_emailsms',
					'order' => 62,
					'value' => 'Session Cancellation',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				)
			);

            // bulk insert
            $this->db->insert_batch('settings', $data);

		}

		public function down() {
			// remove new settings
			$where_in = array(
				'email_exception_participant_cancellation',
				'email_exception_participant_cancellation_subject'
			);
			$this->db->from('settings')->where_in('key', $where_in)->delete();
		}
}
