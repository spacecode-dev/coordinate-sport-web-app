<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Notification_toggles extends CI_Migration {

        private $fields = array();

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();

            // modify fields
            $fields = array(
                'toggle_fields' => array(
                    'name' => 'toggle_fields',
                    'type' => "TEXT",
                    'null' => TRUE
                )
            );
            $this->dbforge->modify_column('settings', $fields);

            // define fields
            $this->fields = array(
                array(
                    'key' => 'send_event_confirmation',
                    'title' => 'Send Event Booking Confirmations',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 9,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_event_confirmation_subject,email_event_confirmation,email_event_confirmation_bcc',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_event_thanks',
                    'title' => 'Send Event Thanks Emails',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 19,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_event_thanks_subject,email_event_thanks',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_new_booking',
                    'title' => 'Send New Booking Email',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 29,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_new_booking_subject,email_new_booking',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_dbs',
                    'title' => 'Send DBS Emails',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 39,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_senddbs_subject,email_senddbs',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_exceptions',
                    'title' => 'Send Customer Exception Emails',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 49,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_exception_company_staffchange_subject,email_exception_company_staffchange,email_exception_company_cancellation_subject,email_exception_company_cancellation,email_exception_customer_staffchange_subject,email_exception_customer_staffchange,email_exception_customer_cancellation_subject,email_exception_customer_cancellation,email_exception_bulk_staffchange_subject,email_exception_bulk_staffchange,email_exception_bulk_cancellation_subject,email_exception_bulk_cancellation',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_payment_reminder_before',
                    'title' => 'Send Payment Reminder Before (Email)',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 109,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_payment_reminder_before_subject,email_payment_reminder_before',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_payment_reminder_after',
                    'title' => 'Send Payment Reminder After (Email)',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 119,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_payment_reminder_after_subject,email_payment_reminder_after',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_renewal_reminders',
                    'title' => 'Send Renewal Reminder Emails',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 129,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_renewal_reminder_subject,email_renewal_reminder',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_payment_reminder_sms',
                    'title' => 'Send Payment Reminder After (SMS)',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 139,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'sms_payment_reminder_after',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_gocardless_subscription',
                    'title' => 'Send GoCardless Subscription Confirmation',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 159,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_gocardless_subscription_subject,email_gocardless_subscription',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_gocardless_payment',
                    'title' => 'Send GoCardless Payment Confirmation',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 162,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_gocardless_payment_subject,email_gocardless_payment',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_gocardless_mandate',
                    'title' => 'Send GoCardless Mandate Link',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 164,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_gocardless_mandate_subject,email_gocardless_mandate',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_customer_booking_notification',
                    'title' => 'Send Customer Booking Notification',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 169,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_customer_booking_notification_to,email_customer_booking_notification_subject,email_customer_booking_notification',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_customer_booking_confirmation',
                    'title' => 'Send Customer Booking Confirmation',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 174,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_customer_booking_confirmation_subject,email_customer_booking_confirmation',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_staff_invoices',
                    'title' => 'Send Staff Invoices',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 180,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'staff_invoice_to,staff_invoice_subject,staff_invoice_email',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_offer_accept_emails',
                    'title' => 'Send Offer Accept Emails',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 199,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_offer_accept_offer_subject,email_offer_accept_offer,email_offer_accept_notifications_to,email_offer_accept_accepted_subject,email_offer_accept_accepted,email_offer_accept_declined_subject,email_offer_accept_declined,email_offer_accept_exhausted_subject,email_offer_accept_exhausted',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_staff_new_sessions',
                    'title' => 'Send Staff New Session(s) Notifications',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 209,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_staff_new_sessions,email_staff_new_sessions_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_staff_cancelled_sessions',
                    'title' => 'Send Staff Cancelled Session(s) Notifications',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 212,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_staff_cancelled_sessions,email_staff_cancelled_sessions_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_new_participant',
                    'title' => 'Send Participant Welcome Email',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 219,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_new_participant,email_new_participant_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_new_staff',
                    'title' => 'Send Staff Welcome Email',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 229,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_new_staff,email_new_staff_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_customer_password',
                    'title' => 'Send Customer Contact Welcome Email',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 239,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_customer_password,email_customer_password_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );
        }

        public function up() {
            // bulk insert
            $this->db->insert_batch('settings', $this->fields);

            // modify orders
            $orders = array(
                'email_footer' => 3,
                'email_participant_reset_password_subject' => 260,
                'email_participant_reset_password' => 261,
                'email_gocardless_payment_subject' => 163,
                'email_gocardless_payment' => 164,
                'send_gocardless_mandate' => 165,
                'email_gocardless_mandate_subject' => 166,
                'email_gocardless_mandate' => 167,
                'email_customer_booking_confirmation_subject' => 175,
                'email_customer_booking_confirmation' => 176,
                'email_staff_cancelled_sessions_subject' => 213,
                'email_staff_cancelled_sessions' => 214,
                'send_payment_reminder_sms' => 122,
                'sms_payment_reminder_after' => 123
            );
            foreach ($orders as $key => $order) {
                $where = array(
                    'key' => $key
                );
                $data = array(
                    'order' => $order
                );
                $this->db->update('settings', $data, $where, 1);
            }
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