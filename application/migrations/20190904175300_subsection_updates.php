<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Subsection_updates extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {

            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'emailsms-main', 'dashboard', 'integrations', 'privacy', 'safety')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

			// add main section fields with empty subsection and toggle other checkbox
			$data = [
				[
					'key' => 'general_emailsms',
					'title' => 'General',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 1,
					'value' => 0,
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				], [
					'key' => 'project_booking_confirmation_emailsms',
					'title' => 'Project Booking Confirmation',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 2,
					'value' => 0,
					'toggle_fields' => 'send_event_confirmation',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'payment_confirmation_emailsms',
					'title' => 'Payment Confirmation',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 3,
					'value' => 0,
					'toggle_fields' => 'send_payment_confirmation',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'event_thanks_email_emailsms',
					'title' => 'Event Thanks Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 4,
					'value' => 0,
					'toggle_fields' => 'send_event_thanks',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'new_booking_email_emailsms',
					'title' => 'New Booking Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 5,
					'value' => 0,
					'toggle_fields' => 'send_new_booking',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'dbs_email_emailsms',
					'title' => 'DBS Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 6,
					'value' => 0,
					'toggle_fields' => 'send_dbs',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'customer_exception_emails_emailsms',
					'title' => 'Customer Exception Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 7,
					'value' => 0,
					'toggle_fields' => 'send_exceptions',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'payment_reminder_emails_emailsms',
					'title' => 'Payment Reminder Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 8,
					'value' => 0,
					'toggle_fields' => 'send_payment_reminder_before,send_payment_reminder_after,send_payment_reminder_sms',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'contract_renewal_reminder_emails_emailsms',
					'title' => 'Contract Renewal Reminder Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 9,
					'value' => 0,
					'toggle_fields' => 'send_renewal_reminders',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'birthday_email_emailsms',
					'title' => 'Birthday Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 10,
					'value' => 0,
					'toggle_fields' => 'send_birthday_emails',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'gocardless_emailsms',
					'title' => 'GoCardless',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 11,
					'value' => 0,
					'toggle_fields' => 'send_gocardless_subscription,send_gocardless_mandate',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'customer_booking_notification_emailsms',
					'title' => 'Customer Booking Notification',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 12,
					'value' => 0,
					'toggle_fields' => 'send_customer_booking_notification',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'customer_booking_confirmation_emailsms',
					'title' => 'Customer Booking Confirmation',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 13,
					'value' => 0,
					'toggle_fields' => 'send_customer_booking_confirmation',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'invoices_emailsms',
					'title' => 'Invoices',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 14,
					'value' => 0,
					'toggle_fields' => 'send_staff_invoices',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'offer_accept_emails_emailsms',
					'title' => 'Offer Accept Emails',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 15,
					'value' => 0,
					'toggle_fields' => 'send_offer_accept_emails',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'new_session_notifications_emailsms',
					'title' => 'New Session(s) Notifications',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 16,
					'value' => 0,
					'toggle_fields' => 'send_staff_new_sessions,send_staff_cancelled_sessions',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'changed_session_notifications_emailsms',
					'title' => 'Changed Session(s) Notifications',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 17,
					'value' => 0,
					'toggle_fields' => 'send_staff_changed_sessions',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'participant_welcome_email_emailsms',
					'title' => 'Participant Welcome Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 18,
					'value' => 0,
					'toggle_fields' => 'send_new_participant',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'staff_welcome_email_emailsms',
					'title' => 'Staff Welcome Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 19,
					'value' => 0,
					'toggle_fields' => 'send_new_staff',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'customer_welcome_email_emailsms',
					'title' => 'Customer Welcome Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 20,
					'value' => 0,
					'toggle_fields' => 'send_customer_password',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'account_renewal_email_alert_emailsms',
					'title' => 'Account Renewal Email Alert',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 21,
					'value' => 0,
					'toggle_fields' => 'send_renewal_alert',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'participant_reset_password_email_emailsms',
					'title' => 'Participant Reset Password Email',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 22,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'not_checkedin_staff_alert_emailsms',
					'title' => 'Not Checked In - Staff Alerts',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 23,
					'value' => 0,
					'toggle_fields' => 'send_not_checkin_staff_email,send_not_checkin_staff_sms',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'not_checkedout_staff_alert_emailsms',
					'title' => 'Not Checked Out - Staff Alert',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 24,
					'value' => 0,
					'toggle_fields' => 'send_not_checkout_staff_email,send_not_checkout_staff_sms',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'not_checkin_account_alert_emailsms',
					'title' => 'Not Checked In - Account Alert',
					'type' => 'checkbox',
					'section' => 'emailsms-main',
					'order' => 25,
					'value' => 0,
					'toggle_fields' => '',
					'instruction' => '',
					'created_at' => mdate('%Y-%m-%d %H:%i:%s')
				],[
					'key' => 'staff_checkin_outside_account_alert_emailsms',
					'title' => 'Staff Checked In Outside Of Session Location - Account Alert',
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
				'subsection' => 'general_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_footer',
				'email_from',
				'email_from_name',
				'sms_from'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'project_booking_confirmation_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_event_confirmation',
				'email_event_confirmation_bcc',
				'email_event_confirmation_include_address',
				'email_event_confirmation_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'payment_confirmation_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_payment_confirmation',
				'email_payment_confirmation_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'event_thanks_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_event_thanks',
				'email_event_thanks_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'new_booking_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_new_booking',
				'email_new_booking_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'dbs_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_senddbs',
				'email_senddbs_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'customer_exception_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_exception_bulk_cancellation',
				'email_exception_bulk_cancellation_subject',
				'email_exception_bulk_staffchange',
				'email_exception_bulk_staffchange_subject',
				'email_exception_company_cancellation',
				'email_exception_company_cancellation_subject',
				'email_exception_company_staffchange',
				'email_exception_company_staffchange_subject',
				'email_exception_customer_cancellation',
				'email_exception_customer_cancellation_subject',
				'email_exception_customer_staffchange',
				'email_exception_customer_staffchange_subject',
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'payment_reminder_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_payment_reminder_after',
				'email_payment_reminder_after_subject',
				'email_payment_reminder_before',
				'email_payment_reminder_before_subject',
				'send_payment_reminder_after',
				'send_payment_reminder_before',
				'send_payment_reminder_sms',
				'sms_payment_reminder_after'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'contract_renewal_reminder_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_renewal_reminder',
				'email_renewal_reminder_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'gocardless_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_gocardless_mandate',
				'email_gocardless_mandate_subject',
				'email_gocardless_subscription',
				'email_gocardless_subscription_subject',
				'send_gocardless_mandate',
				'send_gocardless_subscription'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'customer_booking_notification_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_customer_booking_notification',
				'email_customer_booking_notification_subject',
				'email_customer_booking_notification_to'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'customer_booking_confirmation_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_customer_booking_confirmation',
				'email_customer_booking_confirmation_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'invoices_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'staff_invoice_email',
				'staff_invoice_subject',
				'staff_invoice_to'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'offer_accept_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_offer_accept_accepted',
				'email_offer_accept_accepted_subject',
				'email_offer_accept_declined',
				'email_offer_accept_declined_subject',
				'email_offer_accept_exhausted',
				'email_offer_accept_exhausted_subject',
				'email_offer_accept_notifications_to',
				'email_offer_accept_offer',
				'email_offer_accept_offer_subject',
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'new_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_staff_cancelled_sessions',
				'email_staff_cancelled_sessions_subject',
				'email_staff_new_sessions',
				'email_staff_new_sessions_subject',
				'send_staff_cancelled_sessions'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'changed_session_notifications_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_staff_changed_sessions',
				'email_staff_changed_sessions_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'participant_welcome_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_new_participant',
				'email_new_participant_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'staff_welcome_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_new_staff',
				'email_new_staff_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'customer_welcome_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_customer_password',
				'email_customer_password_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'account_renewal_email_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'send_renewal_alert_message',
				'send_renewal_alert_recipient',
				'send_renewal_alert_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'participant_reset_password_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_participant_reset_password',
				'email_participant_reset_password_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'not_checkedin_staff_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_not_checkin_staff_body',
				'email_not_checkin_staff_subject',
				'email_not_checkin_staff_threshold_time',
				'send_not_checkin_staff_email',
				'send_not_checkin_staff_sms'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'not_checkedout_staff_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_not_checkout_staff_body',
				'email_not_checkout_staff_subject',
				'email_not_checkout_staff_threshold_time',
				'send_not_checkout_staff_email',
				'send_not_checkout_staff_sms'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'not_checkin_account_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_not_checkin_account_body',
				'email_not_checkin_account_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'not_checkout_account_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_not_checkout_account_body',
				'email_not_checkout_account_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'staff_checkin_outside_account_alert_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_checkin_wrong_location_account_body',
				'email_checkin_wrong_location_account_subject'
			]);

			$this->db->update('settings', $data);

			$data = array(
				'subsection' => 'birthday_email_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->where_in('key', [
				'email_birthday_email',
				'email_birthday_email_brand',
				'email_birthday_email_image',
				'email_birthday_email_subject'
			]);

			$this->db->update('settings', $data);
		}

		public function down() {

            $fields = array(
                'section' => array(
                    'name' => 'section',
                    'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations', 'privacy', 'safety')",
                    'null' => FALSE,
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            $keys = [
                'general_emailsms',
                'project_booking_confirmation_emailsms',
                'payment_confirmation_emailsms',
                'event_thanks_email_emailsms',
                'new_booking_email_emailsms',
                'dbs_email_emailsms',
                'customer_exception_emails_emailsms',
                'payment_reminder_emails_emailsms',
                'contract_renewal_reminder_emails_emailsms',
                'birthday_email_emailsms',
                'gocardless_emailsms',
                'customer_booking_notification_emailsms',
                'customer_booking_confirmation_emailsms',
                'invoices_emailsms',
                'offer_accept_emails_emailsms',
                'new_session_notifications_emailsms',
                'changed_session_notifications_emailsms',
                'participant_welcome_email_emailsms',
                'staff_welcome_email_emailsms',
                'customer_welcome_email_emailsms',
                'account_renewal_email_alert_emailsms',
                'participant_reset_password_email_emailsms',
                'not_checkedin_staff_alert_emailsms',
                'not_checkedout_staff_alert_emailsms',
                'not_checkin_account_alert_emailsms',
                'not_checkout_account_alert_emailsms',
                'staff_checkin_outside_account_alert_emailsms'
            ];

            foreach ($keys as $key) {
                $this->db->delete('settings', [
                    'key' => $key
                ], 1);
            }

            //Todo remove keys

			$this->db->where_in('key', [
				'email_footer',
				'email_from',
				'email_from_name',
				'sms_from',
				'email_event_confirmation',
				'email_event_confirmation_bcc',
				'email_event_confirmation_include_address',
				'email_event_confirmation_subject',
				'email_payment_confirmation',
				'email_payment_confirmation_subject',
				'email_event_thanks',
				'email_event_thanks_subject',
				'email_new_booking',
				'email_new_booking_subject',
				'email_senddbs',
				'email_senddbs_subject',
				'email_exception_bulk_cancellation',
				'email_exception_bulk_cancellation_subject',
				'email_exception_bulk_staffchange',
				'email_exception_bulk_staffchange_subject',
				'email_exception_company_cancellation',
				'email_exception_company_cancellation_subject',
				'email_exception_company_staffchange',
				'email_exception_company_staffchange_subject',
				'email_exception_customer_cancellation',
				'email_exception_customer_cancellation_subject',
				'email_exception_customer_staffchange',
				'email_exception_customer_staffchange_subject',
				'email_payment_reminder_after',
				'email_payment_reminder_after_subject',
				'email_payment_reminder_before',
				'email_payment_reminder_before_subject',
				'send_payment_reminder_after',
				'send_payment_reminder_before',
				'send_payment_reminder_sms',
				'sms_payment_reminder_after',
				'email_renewal_reminder',
				'email_renewal_reminder_subject',
				'email_gocardless_mandate',
				'email_gocardless_mandate_subject',
				'email_gocardless_subscription',
				'email_gocardless_subscription_subject',
				'send_gocardless_mandate',
				'send_gocardless_subscription',
				'email_customer_booking_notification',
				'email_customer_booking_notification_subject',
				'email_customer_booking_notification_to',
				'email_customer_booking_confirmation',
				'email_customer_booking_confirmation_subject',
				'staff_invoice_email',
				'staff_invoice_subject',
				'staff_invoice_to',
				'email_offer_accept_accepted',
				'email_offer_accept_accepted_subject',
				'email_offer_accept_declined',
				'email_offer_accept_declined_subject',
				'email_offer_accept_exhausted',
				'email_offer_accept_exhausted_subject',
				'email_offer_accept_notifications_to',
				'email_offer_accept_offer',
				'email_offer_accept_offer_subject',
				'email_staff_cancelled_sessions',
				'email_staff_cancelled_sessions_subject',
				'email_staff_new_sessions',
				'email_staff_new_sessions_subject',
				'send_staff_cancelled_sessions',
				'email_staff_changed_sessions',
				'email_staff_changed_sessions_subject',
				'email_new_participant',
				'email_new_participant_subject',
				'email_new_staff',
				'email_new_staff_subject',
				'email_customer_password',
				'email_customer_password_subject',
				'send_renewal_alert_message',
				'send_renewal_alert_recipient',
				'send_renewal_alert_subject',
				'email_participant_reset_password',
				'email_participant_reset_password_subject',
				'email_not_checkin_staff_body',
				'email_not_checkin_staff_subject',
				'email_not_checkin_staff_threshold_time',
				'send_not_checkin_staff_email',
				'send_not_checkin_staff_sms',
				'email_not_checkout_staff_body',
				'email_not_checkout_staff_subject',
				'email_not_checkout_staff_threshold_time',
				'send_not_checkout_staff_email',
				'send_not_checkout_staff_sms',
				'email_not_checkin_account_body',
				'email_not_checkin_account_subject',
				'email_not_checkout_account_body',
				'email_not_checkout_account_subject',
				'email_checkin_wrong_location_account_body',
				'email_checkin_wrong_location_account_subject'
			]);

			$data = array(
				'subsection' => NULL,
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);

			$this->db->update('settings', $data);
		}
}
