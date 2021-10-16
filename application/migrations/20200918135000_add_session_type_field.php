<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_session_type_field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify fields
		$fields = array(
			'no_of_sessions_per_week' => array(
				'type' => 'int',
				'default' => 0,
				'null' => false,
				'after' => 'price'
			),
			'session_cut_off' => array(
				'type' => 'int',
				'default' => NULL,
				'null' => true,
				'after' => 'price'
			)
		);
		$this->dbforge->add_column('subscriptions', $fields);

	}

	public function down() {
		// drop fields
		$this->dbforge->drop_column('subscriptions', 'no_of_sessions_per_week');
		$this->dbforge->drop_column('subscriptions', 'session_cut_off');
	}
}
