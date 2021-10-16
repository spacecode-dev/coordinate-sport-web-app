<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_stripe_customer_id extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {

		$fields = array(
			'stripe_customer_id' => array(
				'type' => "VARCHAR (255)",
				'after' => 'gc_mandate_id',
				'null' => true
			),
		);

		$this->dbforge->add_column('family_contacts', $fields);
	}

	public function down() {
		$this->dbforge->drop_column('family_contacts', 'stripe_customer_id');
	}
}
