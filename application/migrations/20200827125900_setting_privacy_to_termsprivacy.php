<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_privacy_to_termsprivacy extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add termsprivacy section
		$fields = array(
			'section' => array(
				'name' => 'section',
				'type' => "ENUM('general','general-main','styling','global','emailsms','emailsms-main','dashboard','integrations','privacy','termsprivacy','safety')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('settings', $fields);

		// rename existing
		$where = [
			'section' => 'privacy'
		];
		$data = [
			'section' => 'termsprivacy'
		];
		$this->db->update('settings', $data, $where);

		// remove privacy section
		$fields = array(
			'section' => array(
				'name' => 'section',
				'type' => "ENUM('general','general-main','styling','global','emailsms','emailsms-main','dashboard','integrations','termsprivacy','safety')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('settings', $fields);
	}

	public function down() {
		// add privacy section
		$fields = array(
			'section' => array(
				'name' => 'section',
				'type' => "ENUM('general','general-main','styling','global','emailsms','emailsms-main','dashboard','integrations','privacy','termsprivacy','safety')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('settings', $fields);

		// rename existing
		$where = [
			'section' => 'termsprivacy'
		];
		$data = [
			'section' => 'privacy'
		];
		$this->db->update('settings', $data, $where);

		// remove termsprivacy section
		$fields = array(
			'section' => array(
				'name' => 'section',
				'type' => "ENUM('general','general-main','styling','global','emailsms','emailsms-main','dashboard','integrations','privacy','safety')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('settings', $fields);
	}
}
