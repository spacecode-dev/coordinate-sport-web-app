<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Custom_domains_fields extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'booking_customdomain' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => TRUE,
				'after' => 'booking_subdomain'
			),
			'crm_customdomain' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'null' => TRUE,
				'after' => 'booking_customdomain'
			)
		);
		$this->dbforge->add_column('accounts', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('accounts', 'booking_customdomain');
		$this->dbforge->drop_column('accounts', 'crm_customdomain');
	}
}
