<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_messages_schools_orgs_main_contacts extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			$fields = array(
				'for_org_cusID' => array(
					'type' => "INT",
					'constraint' => 11,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'for_orgID'
				)
			);
			$this->dbforge->add_column('messages', $fields);

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '` ADD CONSTRAINT `fk_messages_for_org_cusID` FOREIGN KEY (`for_org_cusID`) REFERENCES `' . $this->db->dbprefix('orgs_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}

		public function down() {
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('messages') . '` DROP FOREIGN KEY `fk_messages_for_org_cusID`');
			$this->dbforge->drop_column('messages', 'for_org_cusID');
		}
}
