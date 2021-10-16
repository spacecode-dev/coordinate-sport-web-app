<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_settings_customer_org_type extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// define timesheet fields
			$fields = array(
				'org_typeID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'auto_increment' => TRUE
				),
				'accountID' => array(
					'type' => 'INT',
					'constraint' => 11
				),
				'name' => array(
					'type' => 'VARCHAR',
					'constraint' => 50
				),
				'active' => array(
					'type' => 'INT',
					'constraint' => 11,
					'default' => 1
				),
				'created' => array(
					'type' => 'DATETIME'
				),
				'modified' => array(
					'type' => 'DATETIME'
				),
			);
			$this->dbforge->add_field($fields);

			// add keys
			$this->dbforge->add_key('org_typeID', TRUE);
			$this->dbforge->add_key('accountID');

			// set table attributes
			$attributes = array(
				'ENGINE' => 'InnoDB'
			);

			// create table
			$this->dbforge->create_table('settings_customer_types', FALSE, $attributes);

			// add fields
			$fields = array(
				'org_typeID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'default' => NULL,
					'null' => TRUE,
					'after' => 'areaID'
				)
			);
			$this->dbforge->add_column('orgs', $fields);
		}

		public function down() {

			// remove fields
			$this->dbforge->drop_column('orgs', 'org_typeID');

			// drop table
			$this->dbforge->drop_table('settings_customer_types',TRUE);
		}
}
