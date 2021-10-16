<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Gocardless_emails extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_gocardless_subscription_subject',
                    'title' => 'GoCardless Subscription Link Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 160,
                    'value' => 'Complete your payment plan set up',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_gocardless_subscription',
                    'title' => 'GoCardless Subscription Link',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 161,
                    'value' => '<p>Hi {contact_first},</p>
<p>Thank you for requesting to pay by direct debit for {event_name}.</p>
<p>Payment will consist of {details}</p>
<p>Please click the following link to set up your direct debit securely:</p>
<p><a href="{subscription_link}">Set up my Direct Debit</a></p>',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {subscription_link}, {details}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_gocardless_payment_subject',
                    'title' => 'GoCardless Payment Confirmation Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 162,
                    'value' => 'Thank you for your payment',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {amount}, {balance}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_gocardless_payment',
                    'title' => 'GoCardless Payment Confirmation',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 163,
                    'value' => '<p>Hi {contact_first},</p>
<p>Thank you for your recent payment of {amount} by direct debit towards {event_name}.</p>
<p>It has now been applied to your account and your remaining balance is: {balance}</p>',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {amount}, {balance}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_gocardless_subscription_subject',
                'email_gocardless_subscription',
                'email_gocardless_payment_subject',
                'email_gocardless_payment'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}