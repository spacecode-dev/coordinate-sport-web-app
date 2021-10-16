<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Manual_offer_accept_settings extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

			$data = [
				[
					'key' => 'offer_accept_manual_emails_emailsms',
					'title' => 'Offer & Accept (Manual) Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 15,
					'value' => 0,
					'toggle_fields' => 'send_offer_accept_manual_emails',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'send_offer_accept_manual_emails',
					'title' => 'Send Offer & Accept (Manual) Emails',
					'type' => 'checkbox',
					'section' => 'emailsms',
					'order' => 199,
					'value' => 1,
					'toggle_fields' => 'email_offer_accept_offer_subject_manual, email_offer_accept_offer_manual,
					email_offer_accept_notifications_to_manual,
					email_offer_accept_accepted_subject_manual,
					email_offer_accept_accepted_manual,
					email_offer_accept_declined_subject_manual,
					email_offer_accept_declined_manual,
					email_offer_accept_exhausted_subject_manual,
					email_offer_accept_exhausted_manual',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_offer_subject_manual',
					'title' => 'Offer & Accept (Manual) - Offer Session Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 200,
					'value' => 'New Sessions Offered',
					'instruction' => 'Available tags: {first_name}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_offer_manual',
					'title' => 'Offer & Accept (Manual) - Offer Session',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 201,
					'value' => '<p>Hello {first_name},</p>
<p>You have been invited to teach on one or more sessions. Please go to the link below to accept or decline them:</p>
<p>{link}</p>',
					'instruction' => 'Available tags: {first_name}, {link}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_notifications_to_manual',
					'title' => 'Offer & Accept (Manual) - Send Notifications To',
					'type' => 'email',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 202,
					'instruction' => 'Notifications of accepted/rejected session offers will be sent here',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_accepted_subject_manual',
					'title' => 'Offer & Accept (Manual) - Offer Accepted Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 203,
					'value' => 'Sessions Accepted',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_accepted_manual',
					'title' => 'Offer & Accept (Manual) - Offer Accepted',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 204,
					'value' => '<p>Hello,</p>
<p>One or more session offers have been accepted:</p>
<p>{details}</p>
<p>Please go to the link below to view details:</p>
<p>{link}</p>',
					'instruction' => 'Available tags: {details}, {link}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_declined_subject_manual',
					'title' => 'Offer & Accept (Manual) - Offer Declined Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 205,
					'value' => 'Sessions Declined',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_declined_manual',
					'title' => 'Offer & Accept (Manual) - Offer Declined',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 206,
					'value' => '<p>Hello,</p>
<p>One or more session offers have been declined:</p>
<p>{details}</p>
<p>Please go to the link below to view details:</p>
<p>{link}</p>',
					'instruction' => 'Available tags: {details}, {link}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_exhausted_subject_manual',
					'title' => 'Offer & Accept (Manual) - Offers Declined By All Subject',
					'type' => 'text',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 207,
					'value' => 'Offer Exhausted',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
				[
					'key' => 'email_offer_accept_exhausted_manual',
					'title' => 'Offer & Accept (Manual) - Offers Declined By All',
					'type' => 'wysiwyg',
					'section' => 'emailsms',
					'subsection' => 'offer_accept_manual_emails_emailsms',
					'order' => 208,
					'value' => '<p>Hello,</p>
<p>The following session has exhausted all available staffing options:</p>
<p>{details}</p>
<p>View Session Staff: {link}</p>',
					'instruction' => 'Available tags: {details}, {link}',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],
			];

			foreach ($data as $field) {
                $this->db->insert('settings', $field);
            }
		}

		public function down() {
            $keys = [
                'offer_accept_manual_emails_emailsms',
				'send_offer_accept_manual_emails',
                'email_offer_accept_offer_subject_manual',
                'email_offer_accept_offer_manual',
                'email_offer_accept_notifications_to_manual',
                'email_offer_accept_accepted_subject_manual',
                'email_offer_accept_accepted_manual',
                'email_offer_accept_declined_subject_manual',
                'email_offer_accept_declined_manual',
                'email_offer_accept_exhausted_subject_manual',
				'email_offer_accept_exhausted_manual'
            ];

            foreach ($keys as $key) {
                $this->db->delete('settings', [
                    'key' => $key
                ], 1);
            }
		}
}
