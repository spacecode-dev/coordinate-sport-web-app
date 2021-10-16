<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_New_customer_booking_confirmation extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            $where = array(
                'key' => 'email_customer_booking_notification'
            );
            $data = array(
                'value' => '<p>Hello,</p>
<p>{org_name} has just made an online booking for the block: {block_name}.</p>
<p>This booking was made by {contact_full_name} {contact_email}.</p>
<p>You can view and edit this block at: {block_link}</p>',
                'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}, {contact_full_name}, {contact_email}',
            );
            $this->db->update('settings', $data, $where);

            $this->db->delete('accounts_settings', $where);
        }

        public function down() {
            $where = array(
                'key' => 'email_customer_booking_notification'
            );
            $data = array(
                'value' => '<p>Hello,</p>
<p>{org_name} has just made an online booking for the block: {block_name}.</p>
<p>You can view and edit this block at: {block_link}</p>',
                'instruction' => 'Available tags: {org_name}, {block_name}, {block_link}',
            );
            $this->db->update('settings', $data, $where);
        }
}