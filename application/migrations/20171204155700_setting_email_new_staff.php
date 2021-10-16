<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_new_staff extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_new_staff_subject',
                    'title' => 'Staff Welcome Email Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 230,
                    'value' => 'Welcome to {company}',
                    'instruction' => 'Available tags: {staff_first}, {staff_last}, {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_new_staff',
                    'title' => 'Staff Welcome Email',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 231,
                    'value' => '<p>Hi {staff_first},</p>
<p>Please find your login details for the Coordinate system below:</p>
<p>Email Address: {staff_email}<br>
Password: {password}</p>
<p>You can login to your account at {login_link} using either your computer, tablet or mobile phone.</p>
<p><strong><u>I need help or something looks wrong in my Dashboard?</u></strong></p>
<p>If you require assistance or have questions about what\'s appearing in your Dashboard, then in the first instance you should contact the Super User(s) or the administrators of the system in your organisation as follows:</p>
<p>{admins}</p>
<p><strong><u>How do I change my password?</u></strong></p>
<p>You can change your password when logged into the portal by clicking on your name at the top right-hand corner of the page and then clicking "Change Password".</p>
<p>Thank you for your time and attention.</p>',
                    'instruction' => 'Available tags: {staff_first}, {staff_last}, {staff_email}, {password}, {company}, {login_link}, {admins}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_new_staff_subject',
                'email_new_staff'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}