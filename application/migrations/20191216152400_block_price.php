<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Block_price extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'block_price' => array(
					'type' => 'DECIMAL',
					'constraint' => '8,2',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'thanksemail_sent'
				)
			);
			$this->dbforge->add_column('bookings_blocks', $fields);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('bookings_blocks', 'block_price');
		}
}
