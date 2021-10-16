<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_payment_confirmation_email extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
				array(
                    'key' => 'send_payment_confirmation',
                    'title' => 'Send Payment Confirmation',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 14,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_payment_confirmation,email_payment_confirmation_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_payment_confirmation_subject',
                    'title' => 'Payment Confirmation Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 15,
                    'value' => 'Payment Confirmation',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}',
					'toggle_fields' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_payment_confirmation',
                    'title' => 'Payment Confirmation',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 16,
                    'value' => '<p>Hi {contact_first},</p>
<p>Please find confirmation of your recent payment below:</p>
<p>Payment Date: {date}<br>
Method: {method}<br>
Amount: {amount}<br>
Reference: {reference}</p>
<p>This has now been applied to your account.</p>',
                    'instruction' => 'Available tags: {contact_first}, {contact_last}, {date}, {method}, {reference}, {amount}',
					'toggle_fields' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
				'send_payment_confirmation',
                'email_payment_confirmation_subject',
                'email_payment_confirmation'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}