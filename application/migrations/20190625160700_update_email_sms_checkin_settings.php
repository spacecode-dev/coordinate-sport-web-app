<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_email_sms_checkin_settings extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// lod db forge
		$this->load->dbforge();
	}

	public function up() {
		// update settings
		$data = array(
			'title' => 'Check In Threshold Time',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'instruction' => 'If not clicked, the Check In button will remain on the dashboard until the end of the last session at one location.',
		);
		$where = array(
			'key' => 'email_not_checkin_staff_threshold_time'
		);
		$this->db->update('settings', $data, $where, 1);

		// update settings
		$data = array(
			'title' => 'Check Out Threshold Time',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'instruction' => 'The Check out button will also be removed from dashboard once the Check Out threshold time has been exceeded by 10 minutes.',
		);
		$where = array(
			'key' => 'email_not_checkout_staff_threshold_time'
		);
		$this->db->update('settings', $data, $where, 1);
	}

	public function down() {
		// revert settings
		$data = array(
			'title' => 'Threshold Time',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'instruction' => ''
		);
		$where = array(
			'key' => 'email_not_checkin_staff_threshold_time'
		);
		$this->db->update('settings', $data, $where, 1);

		$data = array(
			'title' => 'Threshold Time',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s'),
			'instruction' => ''
		);
		$where = array(
			'key' => 'email_not_checkout_staff_threshold_time'
		);
		$this->db->update('settings', $data, $where, 1);
	}
}
