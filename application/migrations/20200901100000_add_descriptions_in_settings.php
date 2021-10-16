<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_descriptions_in_settings extends CI_Migration {

	public function __construct() {
		parent::__construct();
		log_message('error','HERE0');

		// load db forge
		$this->load->dbforge();
		log_message('error','HERE0');
	}

	public function up() {
		// define fields
		if (!$this->db->field_exists('description', 'settings')) {
			$fields = array(
				'description' => array(
					'type' => 'TEXT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'value'
				)
			);
			$this->dbforge->add_column('settings', $fields);
		}

		if(!$this->db->field_exists('tabpos', 'settings')) {
			$fields = array(
				'tabpos' => array(
					'type' => "ENUM('1','2','3','4')",
					'default' => '1',
					'after' => 'options'
				)
			);

			$this->dbforge->add_column('settings', $fields);
		}

		// update settings
		$data = array (
			array(
				'key' => 'account_renewal_email_alert_emailsms',
				'description' => 'This is the email that will be sent out to each customer account to notify them of their expiry date or date of their account renewal',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'general_emailsms',
				'description' => 'Set your name, email address and the SMS name you will use to communicate with customers. You can also set an email footer for all of your emails here',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_booking_notification_emailsms',
				'title' => 'Notification of Customer Booking',
				'description' => 'Set the email address to where the notifications of customer bookings are sent.',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'invoices_emailsms',
				'description' => 'Set the email address to where the invoices from self-employed staff members are sent.',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkin_account_alert_emailsms',
				'title' => 'Not Checked In Admin Alert',
				'description' => 'Edit the content of the alert sent to the default account email if a staff member have not checked in to their session.',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkout_account_alert_emailsms',
				'title' => 'Not Checked Out Admin Alert',
				'description' => 'Edit the content of the alert sent to the default account if a staff member have not checked out of their session.',
				'tabpos' => '1',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'staff_checkin_outside_account_alert_emailsms',
				'title' => 'Checked In Outside of Session Location Admin Alert',
				'tabpos' => '1',
				'description' => 'Edit the content of the alert sent to the default account email if a staff member have checked in outside of their expected session location.',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'birthday_email_emailsms',
				'description' => 'Edit the content of the email sent to members of staff on their birthday.',
				'tabpos' => '2',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'offer_accept_manual_emails_emailsms',
				'description' => 'Set the email address to where the Offer and Accept notifications are sent.',
				'tabpos' => '2',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'new_session_notifications_emailsms',
				'title' => 'Session Emails',
				'description' => 'Edit the contents of the emails sent to staff members when sessions are assigned to them, cancelled or changed.',
				'tabpos' => '2',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'staff_welcome_email_emailsms',
				'title' => 'Welcome Email',
				'tabpos' => '2',
				'description' => 'Edit the content of the email sent to staff members after their account is created in the application.',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkedin_staff_alert_emailsms',
				'title' => 'Not Checked in Alert',
				'description' => 'Edit the details of the alert sent to staff members if they have not checked in to their session.',
				'tabpos' => '2',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkedout_staff_alert_emailsms',
				'title' => 'Not Checked Out Alert',
				'description' => 'Edit the details of the alert sent to staff members if they have not checked out of their session.',
				'tabpos' => '2',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'project_booking_confirmation_emailsms',
				'description' => 'Edit the content of the booking confirmation emails here. You can send them using a bulk action called \'Send Confirmation\' in the \'Sessions\' tab of a project.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_exception_emails_emailsms',
				'title' => 'Session Exception Emails',
				'description' => 'Edit the content of the exception emails sent to customers here.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'contract_renewal_reminder_emails_emailsms',
				'title' => 'Contract Renewal Reminder',
				'description' => 'Edit the details of the reminder email sent your customers about upcoming contract renewal dates.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'gocardless_emailsms',
				'title' => 'GoCardless Direct Debit Subscription',
				'description' => 'Edit the details of the GoCardless subscription setup and confirmation emails.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_welcome_email_emailsms',
				'title' => 'Welcome Email',
				'description' => 'Edit the content of the email sent to customers after their account is created in the application.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'new_booking_email_emailsms',
				'title' => 'Booking Confirmation Email',
				'description' => 'Edit the content of your booking confirmation emails here.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'dbs_email_emailsms',
				'title' => 'DBS Email',
				'description' => 'Edit the content of your DBS detail request emails here. You can send them using a bulk action called \'Send DBS\' in the \'Sessions\' tab of a project.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_booking_confirmation_emailsms',
				'title' => 'Customer Portal Booking Confirmation',
				'description' => 'Edit the content of your customer portal booking confirmation emails here.',
				'tabpos' => '3',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'payment_confirmation_emailsms',
				'description' => 'Edit the content of your payment confirmation emails here.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'block_thanks_email_emailsms',
				'description' => 'Edit the content of the thank you email that is sent after a block has ended. You can enable this setting by ticking a box called \'Send Thanks Email\' found in the block settings.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'event_thanks_email_emailsms',
				'title' => 'Project Booking Thanks Email',
				'description' => 'Edit the content of the thank you email that is sent after a project booking has ended.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'payment_reminder_emails_emailsms',
				'title' => 'Payment Reminders',
				'description' => 'Edit the content of the reminders with the details of any outstanding payments sent before and after the bookings take place.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'participant_welcome_email_emailsms',
				'title' => 'Welcome Email',
				'description' => 'Edit the content of the email sent to participants after they register on the bookings site.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'participant_reset_password_email_emailsms',
				'title' => 'Password Reset Email',
				'description' => 'Edit the content of the email sent to participants after they have requested a password reset.',
				'tabpos' => '4',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_customer_booking_notification_to',
				'title' => 'Email Customer Booking Notifications To',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_offer_accept_notifications_to_manual',
				'title' => 'Email Notifications To',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_staff_changed_sessions',
				'subsection' => 'new_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_staff_changed_sessions_subject',
				'subsection' => 'new_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);
		$this->db->update_batch('settings', $data, 'key');

		// remove setting
		$where = array(
			'key' => 'changed_session_notifications_emailsms'
		);
		$this->db->delete('settings', $where, 1);
	}

	public function down() {
		// remove fields
		if($this->db->field_exists('description', 'settings')){
			$this->dbforge->drop_column('settings', 'description', TRUE);
		}
		if($this->db->field_exists('tabpos', 'settings')){
			$this->dbforge->drop_column('settings', 'tabpos', TRUE);
		}

		// update settings
		$data = array (
			array(
				'key' => 'customer_booking_notification_emailsms',
				'title' => 'Customer Booking Notification',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkin_account_alert_emailsms',
				'title' => 'Not Checked In - Account Alert',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkout_account_alert_emailsms',
				'title' => 'Not Checked Out Admin Alert',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'staff_checkin_outside_account_alert_emailsms',
				'title' => 'Staff Checked In Outside Of Session Location - Account Alert',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'new_session_notifications_emailsms',
				'title' => 'New Session(s) Notifications',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'staff_welcome_email_emailsms',
				'title' => 'Staff Welcome Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkedin_staff_alert_emailsms',
				'title' => 'Not Checked In - Staff Alert',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'not_checkedout_staff_alert_emailsms',
				'title' => 'Not Checked Out - Staff Alert',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_exception_emails_emailsms',
				'title' => 'Customer Exception Emails',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'contract_renewal_reminder_emails_emailsms',
				'title' => 'Contract Renewal Reminder Emails',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'gocardless_emailsms',
				'title' => 'GoCardless',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_welcome_email_emailsms',
				'title' => 'Customer Welcome Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'new_booking_email_emailsms',
				'title' => 'New Booking Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'dbs_email_emailsms',
				'title' => 'DBS Emails',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'customer_booking_confirmation_emailsms',
				'title' => 'Customer Booking Confirmation',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'event_thanks_email_emailsms',
				'title' => 'Participant Booking Thanks Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'payment_reminder_emails_emailsms',
				'title' => 'Payment Reminder Emails',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'participant_welcome_email_emailsms',
				'title' => 'Participant Welcome Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'participant_reset_password_email_emailsms',
				'title' => 'Participant Reset Password Email',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_customer_booking_notification_to',
				'title' => 'Send Customer Booking Notifications To',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_offer_accept_notifications_to_manual',
				'title' => 'Send Notifications To',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);
		$this->db->update_batch('settings', $data, 'key');

		//Insert removed field
		$data = array(
			'key' => 'changed_session_notifications_emailsms',
			'title' => 'Changed Session(s) Notifications',
			'type' => 'checkbox',
			'section' => 'emailsms-main',
			'order' => 17,
			'value' => 0,
			'toggle_fields' => 'send_staff_changed_sessions',
			'readonly' => 0
		);
		$this->db->insert('settings', $data);

		//Set fields value in subsection
		$data = array(
			array(
				'key' => 'email_staff_changed_sessions',
				'subsection' => 'changed_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			),
			array(
				'key' => 'email_staff_changed_sessions_subject',
				'subsection' => 'changed_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);
		$this->db->update_batch('settings', $data, 'key');
	}
}
