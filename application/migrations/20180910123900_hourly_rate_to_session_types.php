<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Hourly_rate_to_session_types extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// modify settings fields
			$fields = array(
				'hourly_rate' => array(
					'type' => 'DECIMAL',
					'constraint' => '10,2',
					'after' => 'extra_time_assistant',
					'default' => 0,
				),
			);
			$this->dbforge->add_column('lesson_types', $fields);
		}

		public function down() {
			// remove fields
			$this->dbforge->drop_column('lesson_types', 'hourly_rate');
		}
}