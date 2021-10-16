<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_new_participant extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_new_participant_subject',
                    'title' => 'Participant Welcome Email Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 220,
                    'value' => 'Welcome to {company}',
                    'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_new_participant',
                    'title' => 'Participant Welcome Email',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 221,
                    'value' => '<p>Hi {contact_first},</p>
<p>Here are your login details for your new account:</p>
<p>Email Address: {contact_email}<br>
Password: {password}</p>',
                    'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_new_participant_subject',
                'email_new_participant'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}