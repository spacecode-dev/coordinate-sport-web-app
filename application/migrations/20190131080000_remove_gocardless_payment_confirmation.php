<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_gocardless_payment_confirmation extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// remove settings
            $where_in = array(
                'send_gocardless_payment',
                'email_gocardless_payment_subject',
                'email_gocardless_payment'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }

        public function down() {
			// define new settings
            $data = array(
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
                    'key' => 'email_gocardless_payment_subject',
                    'title' => 'GoCardless Payment Confirmation Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 162,
                    'value' => 'Thank you for your payment',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {amount}, {balance}',
					'toggle_fields' => NULL,
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
					'toggle_fields' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }
}