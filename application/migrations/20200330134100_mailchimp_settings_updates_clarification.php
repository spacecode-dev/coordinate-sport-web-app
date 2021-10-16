<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mailchimp_settings_updates_clarification extends CI_Migration
{

	public function __construct()
	{
		parent::__construct();
	}

	public function up()
	{
		// update field
		$data = [
			'instruction' => 'Enter the Audience ID to provide the application access to the list of all participant customers that have given marketing constent. <a href="https://mailchimp.com/help/find-audience-id/" target="_blank">Find Audience ID</a>.'
		];
		$where = [
			'key' => 'mailchimp_audience_id'
		];
		$this->db->update('settings', $data, $where, 1);
	}

	public function down()
	{
		// revert field
		$data = [
			'instruction' => 'Enter the Audience ID to provide the application access to the list of all customers that have given marketing constent. <a href="https://mailchimp.com/help/find-audience-id/" target="_blank">Find Audience ID</a>.'
		];
		$where = [
			'key' => 'mailchimp_audience_id'
		];
		$this->db->update('settings', $data, $where, 1);
	}
}
