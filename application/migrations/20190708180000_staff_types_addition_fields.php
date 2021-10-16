<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_Types_Addition_Fields extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'required_for_session' => array(
				'type' => 'BOOLEAN',
				'after' => 'name',
				'default' => 0
			)
		);
		$this->dbforge->add_column('staffing_types', $fields);

	}

	public function down() {
		$this->dbforge->drop_column('staffing_types', 'required_for_session');
	}
}