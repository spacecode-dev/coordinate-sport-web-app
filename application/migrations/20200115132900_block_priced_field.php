<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Block_priced_field extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// add fields
		$fields = array(
			'block_priced' => array(
				'type' => "TINYINT",
				'constraint' => 1,
				'null' => FALSE,
				'default' => 0,
				'after' => 'shapeup_weight'
			),
		);
		$this->dbforge->add_column('bookings_cart_sessions', $fields);
	}

	public function down() {
		// remove fields
		$this->dbforge->drop_column('bookings_cart_sessions', 'block_priced');
	}
}
