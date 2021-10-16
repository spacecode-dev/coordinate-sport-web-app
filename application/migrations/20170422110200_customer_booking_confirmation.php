<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Customer_booking_confirmation extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_customer_booking_confirmation_subject',
                    'title' => 'Customer Booking Confirmation Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 174,
                    'value' => 'Booking confirmation for {block_name}',
                    'instruction' => 'Available tags: {contact_name}, {org_name}, {block_name}, {date_description}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_customer_booking_confirmation',
                    'title' => 'Customer Booking Confirmation',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 175,
                    'value' => '<p>Hi {contact_name},</p>
<p>Thank you for your booking from {org_name} for {block_name} {date_description}. Please find below details of the sessions booked:</p>
<p>{details}</p>
<p>Please check all details thoroughly, should you notice any mistakes to your booking or would like to make an amendment please contact a member of the team.</p>',
                    'instruction' => 'Available tags: {contact_name}, {org_name}, {block_name}, {date_description}, {details}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_customer_booking_confirmation_subject',
                'email_customer_booking_confirmation'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}