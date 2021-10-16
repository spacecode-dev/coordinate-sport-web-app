<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_emergency_contact extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify Column
			$fields = array(
				'emergency_contact_1_phone' => array(
					'type' => 'VARCHAR',
					'constraint' => 50,
					'default' => NULL
				)
			);
			$this->dbforge->modify_column('family_children', $fields);
		}

		public function down() {
			// Reverse
			$fields = array(
				'emergency_contact_1_phone' => array(
					'type' => 'VARCHAR',
					'constraint' => 20,
					'default' => NULL
				)
			);
			$this->dbforge->modify_column('family_children', $fields);
		}
}
