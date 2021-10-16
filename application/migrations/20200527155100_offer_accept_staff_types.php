<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Offer_accept_staff_types extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify fields
		$fields = array(
			'type' => array(
				'name' => 'type',
				'type' => "ENUM('head','lead','assistant','observer','participant')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('offer_accept', $fields);
	}

	public function down() {
		// modify fields
		$fields = array(
			'type' => array(
				'name' => 'type',
				'type' => "ENUM('head','lead','assistant')",
				'null' => FALSE
			)
		);
		$this->dbforge->modify_column('offer_accept', $fields);
	}
}
