<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Customer_booking_notification extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_customer_booking_notification_to',
                    'title' => 'Send Customer Booking Notifications To',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 170,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_customer_booking_notification_subject',
                    'title' => 'Customer Booking Notification Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 172,
                    'value' => 'New customer booking from {org_name}',
                    'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_customer_booking_notification',
                    'title' => 'Customer Booking Notification',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 173,
                    'value' => '<p>Hello,</p>
<p>{org_name} has just made an online booking for the block: {block_name}.</p>
<p>You can view and edit this block at: {block_link}</p>',
                    'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_customer_booking_notification_to',
                'email_customer_booking_notification_subject',
                'email_customer_booking_notification'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}