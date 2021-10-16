<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_customer_booking_notification_tags extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update settings
            $data = array(
                'instruction' => 'Available tags: {org_name}, {block_name}'
            );
            $where = array(
                'key' => 'email_customer_booking_notification_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update settings
            $data = array(
                'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}, {detail_link}'
            );
            $where = array(
                'key' => 'email_customer_booking_notification'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // update settings
            $data = array(
                'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}'
            );
            $where = array(
                'key' => 'email_customer_booking_notification_subject'
            );
            $this->db->update('settings', $data, $where, 1);

            // update settings
            $data = array(
                'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}'
            );
            $where = array(
                'key' => 'email_customer_booking_notification'
            );
            $this->db->update('settings', $data, $where, 1);
        }
}
