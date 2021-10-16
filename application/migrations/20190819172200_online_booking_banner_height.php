<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Online_booking_banner_height extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// lod db forge
		$this->load->dbforge();
	}

	public function up() {
		// update setting
		$data = array(
			'instruction' => 'Recommended size: 1920px x 400px',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'online_booking_header_image'
		);
		$this->db->update('settings', $data, $where, 1);
	}

	public function down() {
		// update setting
		$data = array(
			'instruction' => 'Recommended size: 1920px x 800px. Will be cropped depending on screen size',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'online_booking_header_image'
		);
		$this->db->update('settings', $data, $where, 1);
	}
}
