<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_User_activity_staff extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'show_user_activity' => array(
				'type' => 'BOOLEAN',
				'after' => 'privacy_agreed',
				'default' => 0
			)
		);
		$this->dbforge->add_column('staff', $fields);
	}

	public function down() {
		$this->dbforge->drop_column('staff', 'show_user_activity');
	}
}