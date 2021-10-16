<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_event_confirmation_include_address extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings_
            $data = array(
                array(
                    'key' => 'email_event_confirmation_include_address',
                    'title' => 'Show address in Project Booking Confirmation',
                    'type' => 'checkbox',
                    'section' => 'emailsms',
                    'order' => 13,
                    'value' => 0,
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );
			// bulk insert
            $this->db->insert_batch('settings', $data);

			// update settings
	        $data = array(
	           'toggle_fields' => 'email_event_confirmation_subject,email_event_confirmation,email_event_confirmation_bcc,email_event_confirmation_include_address',
	            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
	        );
			$where = array(
				'key' => 'send_event_confirmation'
			);
	        $this->db->update('settings', $data, $where, 1);

			// wipe location field in bookings
			$data = array(
				'location' => NULL
			);
			$this->db->update('bookings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_event_confirmation_include_address'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();

			// update settings
	        $data = array(
	           'toggle_fields' => 'email_event_confirmation_subject,email_event_confirmation,email_event_confirmation_bcc',
	            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
	        );
			$where = array(
				'key' => 'send_event_confirmation'
			);
	        $this->db->update('settings', $data, $where, 1);
        }
}
