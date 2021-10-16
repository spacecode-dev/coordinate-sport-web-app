<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_staff_cancelled_sessions extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_staff_cancelled_sessions_subject',
                    'title' => 'Staff Cancelled Session(s) Notification Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 212,
                    'value' => 'Cancelled Session(s)',
                    'instruction' => 'Available tags: {staff_first}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_staff_cancelled_sessions',
                    'title' => 'Staff Cancelled Session(s) Notification',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 213,
                    'value' => '<p>Hi {staff_first},</p>
<p>The following session(s) have been <strong>cancelled</strong>:</p>
<p>{details}</p>
<p>Please check your timetable for full details: {timetable_link}</p>',
                    'instruction' => 'Available tags: {staff_first}, {details}, {timetable_link}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_staff_cancelled_sessions_subject',
                'email_staff_cancelled_sessions'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}