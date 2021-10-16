<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_subscription_email_pos extends CI_Migration{
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {

		$data = [
			[
				'key' => 'subscription_emailsms',
				'title' => 'Subscription Emails',
				'type' => 'checkbox',
				'section' => 'emailsms-main',
				'order' => 27,
				'value' => 0,
				'tab' => 'participants',
				'toggle_fields' => '',
				'instruction' => '',
				'description' => 'Edit the details of the subscription setup and confirmation emails.',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_confirm_subscription_subject',
				'title' => 'Subscription - Confirm Subscription Subject',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 300,
				'value' => 'Thank you for subscribing to {event_name}!',
				'instruction' => 'Available tags: {event_name}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_confirm_subscription',
				'title' => 'Subscription - Confirm Subscription',
				'type' => 'wysiwyg',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 301,
				'value' => '
                <p>Hi {contact_first},</p>
                <p>We are writing to you to confirm the details of your subscription with {company}.</p>
                <p>{subscription_details}</p>
                <p>You can log in to your account at the following link at anytime to check the details of your subscription: {link} </p>
                <p>Please reply to this email if you require any further assistance.</p>
                <p>Many Thanks,<br><br>{org_name}</p>',
				'instruction' => 'Available tags: {contact_first}, {company}, {subscription_details}, {link}, {org_name}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_cancel_subscription_subject',
				'title' => 'Subscription - Cancel Subscription Subject',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 302,
				'value' => 'Subscription Cancellation',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_cancel_subscription',
				'title' => 'Subscription - Cancel Subscription',
				'type' => 'wysiwyg',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 303,
				'value' => '
                <p>Hi {contact_first},</p>
                <p>We are writing to you to confirm the cancellation of your subscription with {company}.</p>
                <p>{subscription_details}</p>
                <p>You can log in to your account at the following link at anytime to check the details of your subscription: {link} </p>
                <p>Please reply to this email if you require any further assistance.</p>
                <p>Many Thanks,<br><br>{org_name}</p>',
				'instruction' => 'Available tags: {contact_first}, {company}, {subscription_details}, {link}, {org_name}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_update_subscription_subject',
				'title' => 'Subscription - Subscription Update Subject',
				'type' => 'text',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 304,
				'value' => 'We\'ve updated your subscription',
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
			[
				'key' => 'email_update_subscription',
				'title' => 'Subscription - Subscription Update',
				'type' => 'wysiwyg',
				'section' => 'emailsms',
				'subsection' => 'subscription_emailsms',
				'order' => 305,
				'value' => '
                <p>Hi {contact_first},</p>
                <p>We are writing to you to confirm the details of your subscription with {company} have been updated as follows:</p>
                <p>{subscription_details}</p>
                <p>You can log in to your account at the following link at anytime to check the details of your subscription: {link} </p>
                <p>Please reply to this email if you require any further assistance.</p>
                <p>Many Thanks,<br><br>{org_name}</p>',
				'instruction' => 'Available tags: {contact_first}, {company}, {subscription_details}, {link}, {org_name}',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			],
		];

		foreach ($data as $field) {
			$this->db->insert('settings', $field);
		}

		$data = array();
		$where = [
			'key' => 'event_thanks_email_emailsms'
		];
		$data['order'] = 6;
		$this->db->update('settings', $data, $where, 1);

		$data = array();
		$where = [
			'key' => 'subscription_emailsms'
		];
		$data['order'] = 5;
		$data['tabpos'] = 4;
		$this->db->update('settings', $data, $where, 1);

		$fields = array(
			'byID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'null' => TRUE,
				'after' => 'familyID'
			)
		);

		$this->dbforge->add_column('subscriptions', $fields);
	}

	public function down() {

		$keys = [
			'subscription_emailsms',
			'email_confirm_subscription_subject',
			'email_confirm_subscription',
			'email_cancel_subscription_subject',
			'email_cancel_subscription',
			'email_update_subscription_subject',
			'email_update_subscription'
		];

		foreach ($keys as $key) {
			$this->db->delete('settings', [
				'key' => $key
			], 1);
		}

		$data = array();
		$where = [
			'key' => 'event_thanks_email_emailsms'
		];
		$data['order'] = 4;
		$this->db->update('settings', $data, $where, 1);
		$where = [
			'key' => 'subscription_emailsms'
		];

		$data = array();
		$data['order'] = 0;
		$data['tabpos'] = 0;
		$this->db->update('settings', $data, $where, 1);

		// remove field
		$this->dbforge->drop_column('subscriptions', 'byID');
	}
}
