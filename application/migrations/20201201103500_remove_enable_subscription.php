<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_enable_subscription extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
		
	}

	public function up() {
		
		// remove enable_subscriptions
		$this->dbforge->drop_column('bookings', 'enable_subscriptions');
	
	}

	public function down() {
		//reverse
		$fields = array(
			'enable_subscriptions' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => 0,
				'null' => FALSE,
				'after' => 'website_description'
			)
		);
		$this->dbforge->add_column('bookings', $fields);
	}
}
