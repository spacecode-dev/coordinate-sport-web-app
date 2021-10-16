<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_invoices_settings_changes extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update setting
            $data = array(
                'order' => 300,
                'section' => 'general'
            );
            $where = array(
                'key' => 'staff_invoice_address'
            );
            $this->db->update('settings', $data, $where, 1);
            $data = array(
                'order' => 181
            );
            $where = array(
                'key' => 'staff_invoice_to'
            );
            $this->db->update('settings', $data, $where, 1);

            // add new
            $data = array(
                array(
                    'key' => 'staff_invoice_prefix',
                    'title' => 'Timesheet Invoices Prefix',
                    'type' => 'text',
                    'section' => 'general',
                    'order' => 301,
                    'value' => '',
                    'instruction' => 'Invoice numbers will be prefixed with this',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'staff_invoice_default_buyer',
                    'title' => 'Timesheet Invoices Default Buyer ID',
                    'type' => 'text',
                    'section' => 'general',
                    'order' => 302,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // update setting
            $data = array(
                'order' => 181,
                'section' => 'emailsms'
            );
            $where = array(
                'key' => 'staff_invoice_address'
            );
            $this->db->update('settings', $data, $where, 1);

            // remove new settings
            $where_in = array(
                'staff_invoice_prefix',
                'staff_invoice_default_buyer',
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}