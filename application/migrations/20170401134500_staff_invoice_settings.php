<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoice_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'staff_invoice_to',
                    'title' => 'Send Timesheet Invoices To',
                    'type' => 'email',
                    'section' => 'emailsms',
                    'order' => 180,
                    'value' => NULL,
                    'instruction' => 'Staff invoices will be sent to this email address',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'staff_invoice_address',
                    'title' => 'Timesheet Invoices Mailing Address',
                    'type' => 'textarea',
                    'section' => 'emailsms',
                    'order' => 181,
                    'value' => NULL,
                    'instruction' => 'Shown on staff invoices',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'staff_invoice_subject',
                    'title' => 'Timesheet Invoices Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 182,
                    'value' => '{staff_name} - Invoice {invoice_no}',
                    'instruction' => 'Available tags: {invoice_no}, {staff_name}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'staff_invoice_email',
                    'title' => 'Timesheet Invoices Email',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 183,
                    'value' => '<p>Hello,</p>
<p>{staff_name} has just submitted a new invoice ({invoice_no}) which is attached.</p>',
                    'instruction' => 'Available tags: {invoice_no}, {staff_name}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'staff_invoice_to',
                'staff_invoice_address',
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}