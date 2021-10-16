<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_gocardless_email_changes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // update settings
        $data = array(
			'value' => "Complete your payment plan set up",
            'instruction' => 'Available tags: {contact_first}, {contact_last}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_mandate_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);

		// update settings
        $data = array(
			'value' => '<p>Hi {contact_first},</p>
 <p>Thank you for requesting to pay by direct debit.</p>
 <p>Please click the following link to set up your direct debit securely:</p>
 <p><a href="{mandate_link}">Set up my Direct Debit</a></p>',
			'instruction' => 'Available tags: {contact_first}, {contact_last}, {mandate_link}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_mandate'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);

		// revert settings
        $data = array(
            'value' => "Payment plan confirmation",
            'instruction' => 'Available tags: {contact_first}, {contact_last}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_subscription_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);

		// revert settings
        $data = array(
            'value' => '<p>Hi {contact_first},</p>
 <p>Thank you for requesting to pay by direct debit.</p>
 <p>Your plan will consist of {details}</p>',
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {details}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_subscription'
		);
        $this->db->update('settings', $data, $where, 1);

		// delete any custom account settings for this key
		$this->db->delete('accounts_settings', $where);
    }

    public function down() {
		// revert settings
        $data = array(
            'value' => "Complete your payment plan set up",
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_mandate_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// revert settings
        $data = array(
            'value' => '<p>Hi {contact_first},</p>
 <p>Thank you for requesting to pay by direct debit for {event_name}.</p>
 <p>Please click the following link to set up your direct debit securely:</p>
 <p><a href="{mandate_link}">Set up my Direct Debit</a></p>',
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {mandate_link}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_mandate'
		);
        $this->db->update('settings', $data, $where, 1);

		// revert settings
        $data = array(
            'value' => "Payment plan confirmation",
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_subscription_subject'
		);
        $this->db->update('settings', $data, $where, 1);

		// revert settings
        $data = array(
            'value' => '<p>Hi {contact_first},</p>
 <p>Thank you for requesting to pay by direct debit for {event_name}.</p>
 <p>Your plan will consist of {details}</p>',
            'instruction' => 'Available tags: {contact_first}, {contact_last}, {event_name}, {details}',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_gocardless_subscription'
		);
        $this->db->update('settings', $data, $where, 1);
    }
}
