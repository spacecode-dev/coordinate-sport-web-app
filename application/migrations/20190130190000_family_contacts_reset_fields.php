<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Family_contacts_reset_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'reset_hash' => array(
                'type' => 'VARCHAR',
                'constraint' => 32,
                'null' => TRUE,
                'after' => 'blacklisted'
            ),
            'reset_at' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'reset_hash'
            ),
            'invalid_logins' => array(
                'type' => 'INT',
                'constraint' => 1,
                'default' => 0,
                'after' => 'reset_at'
            ),
            'locked_until' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'invalid_logins'
            ),
			'last_login' => array(
                'type' => 'DATETIME',
                'null' => TRUE,
                'after' => 'locked_until'
            )
        );
        $this->dbforge->add_column('family_contacts', $fields);

        // update settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
<p>You or someone else has requested a password reset.</p>
<p>To reset your password, please click the link below:</p>
<p>{reset_link}</p>
<p>Note: This link is only valid for 15 minutes from when this email was requested. If you didn't request this, please ignore this email.</p>",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {company}, {reset_link}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_participant_reset_password'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);

		// update settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
 <p>Thank you for registering. You can now log in to your new account at:</p>
 <p>{link}</p>",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {link}, {company}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_new_participant'
		);
        $this->db->update('settings', $data, $where, 1);

    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('family_contacts', 'reset_hash');
        $this->dbforge->drop_column('family_contacts', 'reset_at');
        $this->dbforge->drop_column('family_contacts', 'invalid_logins');
        $this->dbforge->drop_column('family_contacts', 'locked_until');
        $this->dbforge->drop_column('family_contacts', 'last_login');

		// revert settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
 <p>As requested, your password has been reset. Your new password is: {password}</p>
 <p>Please visit {login_link} to login to your account.</p>",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}, {login_link}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_participant_reset_password'
		);
        $this->db->update('settings', $data, $where, 1);

		// update settings
        $data = array(
            'value' => "<p>Hi {contact_first},</p>
 <p>Here are your login details for your new account:</p>
 <p>Email Address: {contact_email}<br /> Password: {password}</p>",
            'instruction' => 'Available tags: {contact_title}, {contact_first}, {contact_last}, {contact_email}, {password}, {company}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_new_participant'
		);
        $this->db->update('settings', $data, $where, 1);
    }
}
