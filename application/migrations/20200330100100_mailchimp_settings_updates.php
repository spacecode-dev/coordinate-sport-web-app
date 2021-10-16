<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mailchimp_settings_updates extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		// update field
		$data = [
			'instruction' => 'Enter the API Key to provide the application the access to your MailChimp account. <a href="http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key" target="_blank">Find Your API Key</a>.<br>
To monitor any changes to the marketing consent given by participants, <a href="https://mailchimp.com/developer/guides/about-webhooks/" target="_blank">Add a Webhook</a> with a URL of <a href="{site_url}webhooks/mailchimp" target="_blank">{site_url}webhooks/mailchimp</a>.'
		];
		$where = [
			'key' => 'mailchimp_key'
		];
		$this->db->update('settings', $data, $where, 1);

		// new field
		$data = [
			'key' => 'mailchimp_audience_id',
			'title' => 'Audience ID',
			'type' => 'text',
			'section' => 'integrations',
			'subsection' => null,
			'order' => 133,
			'instruction' => 'Enter the Audience ID to provide the application access to the list of all customers that have given marketing constent. <a href="https://mailchimp.com/help/find-audience-id/" target="_blank">Find Audience ID</a>.',
			'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		];
		$this->db->insert('settings', $data);
	}

	public function down()
	{
		// remove field
		$where = [
			'key' => 'mailchimp_audience_id'
		];
		$this->db->delete('settings', $where, 1);

		// revert field
		$data = [
			'instruction' => 'Enter this if you want to sync your user\'s subscriptions with your MailChimp account. <a href="http://kb.mailchimp.com/accounts/management/about-api-keys#Find-or-Generate-Your-API-Key" target="_blank">Find Your API Key</a>.
To sync unsubscribes back from MailChimp, <a href="http://kb.mailchimp.com/integrations/other-integrations/how-to-set-up-webhooks" target="_blank">Add a Webhook</a> with a URL of <a href="{site_url}webhooks/mailchimp" target="_blank">{site_url}webhooks/mailchimp</a>, type of unsubscribes and send updates when made by a subscriber or admin.'
		];
		$where = [
			'key' => 'mailchimp_key'
		];
		$this->db->update('settings', $data, $where, 1);
	}
}
