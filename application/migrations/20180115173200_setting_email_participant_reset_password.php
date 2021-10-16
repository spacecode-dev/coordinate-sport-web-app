<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_participant_reset_password extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_participant_reset_password_subject',
                    'title' => 'Participant Reset Password Email Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 222,
                    'value' => 'New Password for {company}',
                    'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_participant_reset_password',
                    'title' => 'Participant Reset Password Email',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 223,
                    'value' => '<p>Hi {contact_first},</p>
<p>As requested, your password has been reset. Your new password is: {password}</p>
<p>Please visit {login_link} to login to your account.</p>',
                    'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}, {login_link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_participant_reset_password_subject',
                'email_participant_reset_password'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}