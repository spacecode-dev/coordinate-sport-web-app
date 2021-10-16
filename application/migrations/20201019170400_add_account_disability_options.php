<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_Account_Disability_Options extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		if (!$this->db->table_exists("family_disabilities")) {
			$fields = array (
				'accountID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'contactID' => array(
					'type' => 'INT',
					'default' => NULL,
					'constraint' => 11,
					'NULL' => true
				),
				'childID' => array(
					'type' => 'INT',
					'default' => NULL,
					'constraint' => 11,
					'NULL' => true
				),
			);

			$disabilities = array(
				"not_applicable",
				"hearing",
				"learning_difficulty",
				"learning_disability",
				"mental_health_condition",
				"physical_ambulant",
				"physical_wheelchair",
				"sight",
				"other",
				"prefer_not_to_say"
			);

			foreach ($disabilities as $disability) {
				$fields[$disability] = array(
					'type' => 'INT',
					'NULL' => true,
					'default' => NULL,
					'constraint' => 1
				);
			}

			$fields["added"] = array('type' => 'DATETIME');
			$fields["modified"] = array('type' => 'DATETIME', 'null' => TRUE);

			$this->dbforge->add_field($fields);

			// add keys
			$this->dbforge->add_key('accountID');
			$this->dbforge->add_key('contactID');
			$this->dbforge->add_key('childID');

			// set table attributes
			$attributes = array(
				'ENGINE' => 'InnoDB'
			);

			// create table
			$this->dbforge->create_table('family_disabilities', FALSE, $attributes);

			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_disabilities') . '` ADD CONSTRAINT `fk_family_disabilities_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_disabilities') . '` ADD FOREIGN KEY (`childID`) REFERENCES `' . $this->db->dbprefix('family_children') . '`(`childID`) ON DELETE CASCADE ON UPDATE CASCADE');
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('family_disabilities') . '` ADD FOREIGN KEY (`contactID`) REFERENCES `' . $this->db->dbprefix('family_contacts') . '`(`contactID`) ON DELETE CASCADE ON UPDATE CASCADE');
		}
	}

	public function down() {
		$this->dbforge->drop_table('family_disabilities', TRUE);
	}
}
