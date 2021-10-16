<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Salaried_sessions_offer_accept extends CI_Migration {
	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add field
		$fields = array(
			'salaried' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default'=> 0,
				'after' => 'type'
			)
		);
		$this->dbforge->add_column('offer_accept', $fields);
	}

	public function down() {
		// remove field
		$this->dbforge->drop_column('offer_accept', 'salaried');
	}
}
