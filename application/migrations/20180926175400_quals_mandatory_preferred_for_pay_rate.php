<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Quals_mandatory_preferred_for_pay_rate extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'preferred_for_pay_rate' => array(
				'type' => 'BOOLEAN',
				'after' => 'not_required',
				'default' => 0
			)
		);
		$this->dbforge->add_column('staff_quals_mandatory', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('staff_quals_mandatory', 'preferred_for_pay_rate');
	}
}
