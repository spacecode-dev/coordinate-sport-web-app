<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_exclude_mileage_session_types extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		
		// Add column in lesson types
		$fields = array(
			'exclude_mileage_session' => array(
				'type' => 'TINYINT(1)',
				'default' => 0,
				'after' => 'hourly_rate'
			)
		);
		
		$this->dbforge->add_column("lesson_types", $fields);
		
	}

	public function down() {
		// revese
		$this->dbforge->drop_column('lesson_types', 'exclude_mileage_session');
	}
}
