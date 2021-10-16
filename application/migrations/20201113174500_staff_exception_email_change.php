<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_staff_exception_email_change extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Rename Title staff_exception cancellation
			$data = array(
				'title' => 'Exception - Cancellation',
				'description' => 'Edit the content of the cancellation emails sent to customers here.',
			);
			$where = array("key" => 'customer_exception_emails_emailsms');
			
			$this->db->update("settings", $data, $where, 1);
			
			// Create a new for staff change
			
			$data = array(
				'key' => 'customer_change_exception_emails_emailsms',
				'title' => 'Exception - Staff Change',
				'type' => 'checkbox',
				'section' => 'emailsms-main',
				'order' => 7,
				'tab' => 'customers',
				'description' => 'Edit the content of the staff change emails sent to customers here.',
				'toggle_fields' => 'send_exceptions',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			
			$this->db->insert('settings', $data);
			
			// Move fields in new section
			$data = array(
				'subsection' => 'customer_change_exception_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			
			$where_in = array(
				'email_exception_company_staffchange',
				'email_exception_company_staffchange_subject',
				'email_exception_bulk_staffchange',
				'email_exception_bulk_staffchange_subject',
				'email_exception_customer_staffchange',
				'email_exception_customer_staffchange_subject'
			);
			
			//Update data
			$this->db->where_in('key', $where_in);
			$this->db->update('settings', $data);
			
			
		}

		public function down() {
			// reverse
			$data = array(
				'subsection' => 'customer_exception_emails_emailsms',
				'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
			);
			
			$where_in = array(
				'email_exception_company_staffchange',
				'email_exception_company_staffchange_subject',
				'email_exception_bulk_staffchange',
				'email_exception_bulk_staffchange_subject',
				'email_exception_customer_staffchange',
				'email_exception_customer_staffchange_subject'
			);
			
			//Update data
			$this->db->where_in('key', $where_in);
			$this->db->update('settings', $data);
			
			$where = array('key' => 'customer_change_exception_emails_emailsms');
			$this->db->from('settings')->where($where)->delete();
			
			$data = array(
				'title' => 'Session Exception Emails',
				'description' => 'Edit the content of the exception emails sent to customers here.',
			);
			$where = array("key" => 'customer_exception_emails_emailsms');
			
			$this->db->update("settings", $data, $where, 1);
			
		}
}
