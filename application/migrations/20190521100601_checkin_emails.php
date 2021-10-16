<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Checkin_Emails extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_not_checkin_staff_subject',
                    'title' => 'Not Checked In - Staff Alert Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 270,
                    'value' => 'Not Checked In - Staff Alert',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkin_staff_body',
                    'title' => 'Not Checked In - Staff Alert',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 271,
                    'value' => '<p>This is an automated alert from {company}.</p>
<p>Our Coordinate staff safety application is reporting that you have not checked in to your current scheduled session.</p>
<p>Please Check In to your current session ASAP.</p>',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkin_staff_threshold_time',
                    'title' => 'Threshold Time',
                    'type' => 'int',
                    'section' => 'emailsms',
                    'order' => 272,
                    'value' => 15,
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_not_checkin_staff_email',
                    'title' => 'Send Not Checked In Alert - Email Staff Alert',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 273,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_not_checkin_staff_subject,email_not_checkin_staff_body',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_not_checkin_staff_sms',
                    'title' => 'Send Not Checked In Alert - SMS Staff Alert',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 274,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkout_staff_subject',
                    'title' => 'Not Checked Out - Staff Alert Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 275,
                    'value' => 'Not Checked Out - Staff Alert',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkout_staff_body',
                    'title' => 'Not Checked Out - Staff Alert',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 276,
                    'value' => '<p>This is an automated alert from {company}.</p>
 <p>Our Coordinate staff safety application is reporting that you have not Checked Out of your current session.</p>
 <p>Please check out of your current session ASAP.</p>',
                    'instruction' => 'Available tags: {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkout_staff_threshold_time',
                    'title' => 'Threshold Time',
                    'type' => 'int',
                    'section' => 'emailsms',
                    'order' => 277,
                    'value' => 15,
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_not_checkout_staff_email',
                    'title' => 'Send Not Checked Out Alert - Email Staff Alert',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 278,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_not_checkin_staff_subject,email_not_checkin_staff_body',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'send_not_checkout_staff_sms',
                    'title' => 'Send Not Checked Out Alert - SMS Staff Alert',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 279,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkin_account_subject',
                    'title' => 'Not Checked In - Account Alert Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 280,
                    'value' => 'Staff Not Checked In',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkin_account_body',
                    'title' => 'Not Checked In - Account Alert',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 281,
                    'value' => 'This is an automated email to notify you that {staff_name} has not checked in to their scheduled session at {customer_name}.',
                    'instruction' => 'Available tags: {staff_name}, {customer_name}.',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkout_account_subject',
                    'title' => 'Not Checked Out - Account Alert Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 282,
                    'value' => 'Staff Not Checked Out',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_not_checkout_account_body',
                    'title' => 'Not Checked Out - Account Alert',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 283,
                    'value' => 'This is an automated email to notify you that {staff_name} has not checked out of their scheduled session at {customer_name}',
                    'instruction' => 'Available tags: {staff_name}, {customer_name}.',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_checkin_wrong_location_account_subject',
                    'title' => 'Staff Checked In Outside Of Session Location - Account Alert Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 284,
                    'value' => 'Staff Checked In Outside of Session Location',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_checkin_wrong_location_account_body',
                    'title' => 'Staff Checked In Outside Of Session Location - Account Alert',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 285,
                    'value' => 'This is an automated email to notify you that {staff_name} has checked in outside of their expected and scheduled session location.',
                    'instruction' => 'Available tags: {staff_name}.',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            foreach ($data as $item) {
                $this->db->insert('settings', $item);
            }
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_not_checkin_staff_subject',
                'email_not_checkin_staff_body',
                'email_not_checkin_staff_threshold_time',
                'send_not_checkin_staff_email',
                'send_not_checkin_staff_sms',
                'email_not_checkout_staff_subject',
                'email_not_checkout_staff_body',
                'email_not_checkout_staff_threshold_time',
                'send_not_checkout_staff_email',
                'send_not_checkout_staff_sms',
                'email_not_checkin_account_subject',
                'email_not_checkin_account_body',
                'email_not_checkout_account_subject',
                'email_not_checkout_account_body',
                'email_checkin_wrong_location_account_subject',
                'email_checkin_wrong_location_account_body',
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}