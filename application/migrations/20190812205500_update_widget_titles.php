<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_widget_titles extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// lod db forge
		$this->load->dbforge();
	}

	public function up() {
		// update setting
		$data = array(
			'title' => 'Custom Widget 2 Title',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'dashboard_custom_widget_2_title'
		);
		$this->db->update('settings', $data, $where, 1);

		// update setting
		$data = array(
			'title' => 'Custom Widget 2 HTML',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'dashboard_custom_widget_2_html'
		);
		$this->db->update('settings', $data, $where, 1);

		// update setting
		$data = array(
			'title' => 'Custom Widget 3 Title',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'dashboard_custom_widget_3_title'
		);
		$this->db->update('settings', $data, $where, 1);

		// update setting
		$data = array(
			'title' => 'Custom Widget 3 HTML',
			'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
		);
		$where = array(
			'key' => 'dashboard_custom_widget_3_html'
		);
		$this->db->update('settings', $data, $where, 1);
	}

	public function down() {
		// no going back
	}
}
