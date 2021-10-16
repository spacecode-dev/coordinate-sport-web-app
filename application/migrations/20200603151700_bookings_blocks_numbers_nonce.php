<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Bookings_blocks_numbers_nonce extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add field
			$fields = array(
				'numbers_nonce' => array(
					'type' => 'VARCHAR',
					'constraint' => 10,
					'null' => TRUE,
					'default'=> NULL,
					'after' => 'numbers_path',
					'comment' => 'Used for one time access to numbers register for zoho'
				)
			);
			$this->dbforge->add_column('bookings_blocks', $fields);
		}

		public function down() {
			// remove field
			$this->dbforge->drop_column('bookings_blocks', 'numbers_nonce');
		}
}
