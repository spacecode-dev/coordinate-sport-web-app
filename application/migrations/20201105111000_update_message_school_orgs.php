<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_message_school_orgs extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// add fields
			$fields = array(
				'group' => array(
					'type' => "ENUM('staff', 'schools', 'organisations')",
					'default' => 'staff',
					'after' => 'folder'
				)
			);
			$this->dbforge->add_column('messages', $fields);

			$fields = array(
				'for_orgID' => array(
					'type' => "INT",
					'constraint' => 11,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'forID'
				)
			);
			$this->dbforge->add_column('messages', $fields);

			// update field
			$fields = array(
				'forID' => array(
					'type' => "INT",
					'constraint' => 11,
					'null' => TRUE,
					'default' => NULL
				)
			);
			$this->dbforge->modify_column('messages', $fields);

			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '` ADD CONSTRAINT `fk_messages_for_orgID` FOREIGN KEY (`for_orgID`) REFERENCES `' . $this->db->dbprefix('orgs') . '`(`orgID`) ON DELETE CASCADE ON UPDATE CASCADE');

		}

		public function down() {
			$this->dbforge->drop_column('messages', 'group');

			$res = $this->db->query("SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME =  '".$this->db->dbprefix('messages')."' AND COLUMN_NAME =  'for_orgID'");
			if($res->num_rows() > 0){
				foreach($res->result() as $contstraint_name) break;
				$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '` DROP FOREIGN KEY `'.$contstraint_name->CONSTRAINT_NAME.'`');
			}

			$this->dbforge->drop_column('messages', 'orgID', TRUE);
			// update field
			$fields = array(
				'forID' => array(
					'type' => "INT",
					'constraint' => 11
				)
			);
			$this->dbforge->modify_column('messages', $fields);
		}
}
