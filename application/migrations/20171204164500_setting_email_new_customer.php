<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_email_new_customer extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // update subject
            $where = array(
                'key' => 'email_customer_password_subject'
            );
            $data = array(
                'title' => 'Customer Contact Welcome Email Subject',
                'order' => 240,
                'value' => 'Welcome to {company}',
                'instruction' => 'Available tags: {contact_name}, {company}',
            );
            $this->db->update('settings', $data, $where);

            // update body
            $where = array(
                'key' => 'email_customer_password'
            );
            $data = array(
                'title' => 'Customer Contact Welcome Email',
                'order' => 241,
                'value' => '<p>Hi {contact_name},</p>
<p>Here are your login details for your new account for {org_name}:</p>
<p>Email Address: {contact_email}<br>
Password: {password}</p>',
                'instruction' => 'Available tags: {contact_name}, {contact_email}, {org_name}, {password}, {company}',
            );
            $this->db->update('settings', $data, $where);
        }

        public function down() {

            // revert subject
            $where = array(
                'key' => 'email_customer_password_subject'
            );
            $data = array(
                'title' => 'Bulk Customer Password Email Subject',
                'order' => 180,
                'value' => 'Login details for {org_name}',
                'instruction' => 'Available tags: {contact_name}, {contact_email}, {org_name}, {password}',
            );
            $this->db->update('settings', $data, $where);

            // revert body
            $where = array(
                'key' => 'email_customer_password'
            );
            $data = array(
                'title' => 'Bulk Customer Password Email',
                'order' => 181,
                'value' => '<p>Hello {contact_name},</p>
<p>{org_name} has been assigned the following details for online booking:</p>
<p>Username/email: {contact_email}<br />
Pasword: {password}</p>',
                'instruction' => 'Available tags: {contact_name}, {contact_email}, {org_name}, {password}'
            );
            $this->db->update('settings', $data, $where);
        }
}