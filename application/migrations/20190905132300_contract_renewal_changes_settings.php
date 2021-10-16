<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Contract_renewal_changes_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add setting
            $data = array(
                'key' => 'email_renewal_reminder_first',
                'title' => 'Send First Reminder',
                'type' => 'number',
                'section' => 'emailsms',
				'subsection' => 'contract_renewal_reminder_emails_emailsms',
                'order' => 128,
                'value' => '60',
                'instruction' => 'Amount of days to send the first reminder before the contract renewal date',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );
            $this->db->insert('settings', $data);

			// add setting
			$data = array(
                'key' => 'email_renewal_reminder_additional',
                'title' => 'Send Additional Reminders',
                'type' => 'number',
                'section' => 'emailsms',
				'subsection' => 'contract_renewal_reminder_emails_emailsms',
                'order' => 129,
                'value' => '',
                'instruction' => 'Enter the ratio of days to send addtional reminders after the first reminder',
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );
            $this->db->insert('settings', $data);
        }

        public function down() {
            // remove setting
            $where = array(
                'key' => 'email_renewal_reminder_first'
            );
            $this->db->delete('settings', $where, 1);

			// remove setting
            $where = array(
                'key' => 'email_renewal_reminder_additional'
            );
            $this->db->delete('settings', $where, 1);
        }
}
