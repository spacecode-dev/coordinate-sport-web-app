<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_staff_changed_sessions extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
				array(
                    'key' => 'send_staff_changed_sessions',
                    'title' => 'Send Staff Changed Session(s) Notifications',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 215,
                    'value' => '1',
                    'instruction' => '',
                    'toggle_fields' => 'email_staff_changed_sessions,email_staff_changed_sessions_subject',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_staff_changed_sessions_subject',
                    'title' => 'Staff Changed Session(s) Notification Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 216,
                    'value' => 'Staff Times Adjusted on Session(s)',
                    'instruction' => 'Available tags: {staff_first}',
					'toggle_fields' => NULL,
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_staff_changed_sessions',
                    'title' => 'Staff Changed Session(s) Notification',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 217,
                    'value' => '<p>Hi {staff_first},</p>
<p>The times for the following session(s) have now been changed:</p>
<p>{details}</p>
<p>Please check your timetable for full details: {timetable_link}</p>',
                    'instruction' => 'Available tags: {staff_first}, {details}, {timetable_link}',
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
				'send_email_staff_changed_sessions',
                'email_staff_changed_sessions_subject',
                'email_staff_changed_sessions'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}