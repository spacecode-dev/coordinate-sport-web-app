<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Customer_password_email extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define new settings
            $data = array(
                array(
                    'key' => 'email_customer_password_subject',
                    'title' => 'Bulk Customer Password Email Subject',
                    'type' => 'text',
                    'section' => 'emailsms',
                    'order' => 180,
                    'value' => 'Login details for {org_name}',
                    'instruction' => 'Available tags: {contact_name}, {contact_email}, {org_name}, {password}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'email_customer_password',
                    'title' => 'Bulk Customer Password Email',
                    'type' => 'wysiwyg',
                    'section' => 'emailsms',
                    'order' => 181,
                    'value' => '<p>Hello {contact_name},</p>
<p>{org_name} has been assigned the following details for online booking:</p>
<p>Username/email: {contact_email}<br />
Pasword: {password}</p>',
                    'instruction' => 'Available tags: {contact_name}, {contact_email}, {org_name}, {password}',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }

        public function down() {
            // remove new settings
            $where_in = array(
                'email_customer_password_subject',
                'email_customer_password'
            );
            $this->db->from('settings')->where_in('key', $where_in)->delete();
        }
}