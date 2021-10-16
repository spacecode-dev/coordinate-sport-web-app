<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_archive_messages extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			if (!$this->db->field_exists('isArchived', 'messages')) {
				$fields = array(
					'isArchived' => array(
						'type' => "ENUM('yes', 'no')",
						'default' => 'no',
						'after' => 'folder'
					)
				);
				$this->dbforge->add_column('messages', $fields);
			}
		}

		public function down() {
			$this->dbforge->drop_column('messages', 'isArchived');
		}
}
