<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_messages_participants extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'group' => array(
					'name' => 'group',
					'type' => "ENUM('staff', 'schools', 'organisations', 'participants')",
					'default' => 'staff'
				)
			);
			$this->dbforge->modify_column('messages', $fields);

			$fields = array(
				'for_participantID' => array(
					'type' => "INT",
					'constraint' => 11,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'for_orgID'
				)
			);
			$this->dbforge->add_column('messages', $fields);

			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '`ADD CONSTRAINT `fk_messages_for_participantID` FOREIGN KEY (`for_participantID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');

		}

		public function down() {
			$fields = array(
				'group' => array(
					'name' => 'group',
					'type' => "ENUM('staff', 'schools', 'organisations')",
					'default' => 'staff'
				)
			);
			$this->dbforge->modify_column('messages', $fields);

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '` DROP FOREIGN KEY `fk_messages_for_participantID`');

			$this->dbforge->drop_column('messages', 'for_participantID');
		}
}
