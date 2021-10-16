<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bcc_event_confirmations extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_event_confirmation_bcc',
                    'title' => 'Send a Copy of Event Booking Confirmations to',
                    'type' => 'email-multiple',
                    'section' => 'emailsms',
                    'order' => 12,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_event_confirmation_bcc'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}