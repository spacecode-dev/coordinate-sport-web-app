<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_Types_Display_On_Payroll extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'display_on_payroll' => array(
				'type' => 'BOOLEAN',
				'after' => 'required_for_session',
				'default' => 0
			)
		);
		$this->dbforge->add_column('staffing_types', $fields);

	}

	public function down() {
		$this->dbforge->drop_column('staffing_types', 'display_on_payroll');
	}
}