<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_refund_boolean_to_exceptions extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$fields = array(
				'refunded_participants' =>array(
					'type' => 'TINYINT(1)',
					'default' => 0,
					'after' => 'assign_to'
				)
			);

			$this->dbforge->add_column("bookings_lessons_exceptions", $fields);
		}

		public function down() {
			$this->dbforge->drop_column('bookings_lessons_exceptions', 'refunded_participants');
		}
}
