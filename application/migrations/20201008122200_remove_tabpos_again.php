<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_tabpos_again extends CI_Migration {

		private $tabs = [];

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// we use a tab field which accepts a string instead now, so tabpos is no longer needed, remove tabpos field
			$this->dbforge->drop_column('settings', 'tabpos', TRUE);
		}

		public function down() {
			// add tabpos field
			$fields = array(
				'tabpos' => array(
					'type' => "ENUM('1','2','3','4')",
					'default' => NULL,
					'after' => 'options'
				)
			);
			$this->dbforge->add_column('settings', $fields);
		}
}
