<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoices_subject_setting extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // add new
            $data = array(
                array(
                    'key' => 'staff_invoice_default_subject',
                    'title' => 'Timesheet Invoices Default Subject',
                    'type' => 'text',
                    'section' => 'general',
                    'order' => 303,
                    'value' => 'Invoice for {staff_name}',
                    'instruction' => 'Available tags: {staff_name}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'staff_invoice_default_subject'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}