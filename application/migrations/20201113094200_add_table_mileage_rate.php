<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_table_mileage_rate extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();

		// increase timeout and memory limit
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
	}

	public function up() {

		// Add fields in mileage table
		$fields = array(
			'mileageID' => array(
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
				'constraint' => 255,
				'null' => FALSE
			),
			'rate' => array(
				'type' => 'INT'
			),
			'created' => array(
				'type' => 'DATETIME'
			),
			'modified' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('mileageID', TRUE);
		$this->dbforge->add_key('accountID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('mileage', FALSE, $attributes);


		$query = $this->db->select("*")->from("accounts")->get();
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$where = array("accountID" => $result->accountID);
				$query_mileage = $this->db->from("mileage")->where($where)->get();
				if($query_mileage->num_rows() == 0){
					// Add Rows In table
					$data = array("name" => "Car",
					"rate" => "45",
					"accountID" => $result->accountID,
					"created" => mdate('%Y-%m-%d %H:%i:%s'),
					"modified" => mdate('%Y-%m-%d %H:%i:%s'));

					$this->db->insert("mileage", $data);

					$data = array("name" => "Bicycle",
					"rate" => "25",
					"accountID" => $result->accountID,
					"created" => mdate('%Y-%m-%d %H:%i:%s'),
					"modified" => mdate('%Y-%m-%d %H:%i:%s'));

					$this->db->insert("mileage", $data);
				}
			}
		}
		$fields = array(
			'itemID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'accountID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'timesheetID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'lessonID' => array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => null
			),
			'start_location' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
			),
			'session_location' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
			),
			'via_location' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'default' => null
			),
			'mode' => array(
				'type' => 'VARCHAR',
				'constraint' => 200,
				'default' => null
			),
			'edited' => array(
				'type' => 'TINYINT(1)',
				'default' => 0
			),
			'date' => array(
				'type' => 'date',
				'null' => FALSE,
			),
			'role' => array(
				'type' => "ENUM('head','assistant','participant','observer','lead')",
				'default' => null
			),
			'status' => array(
				'type' => "ENUM('unsubmitted','submitted','approved','declined')",
				'default' => 'unsubmitted'
			),
			'reason' => array(
				'type' => "ENUM('travel','training','marketing','admin','other')",
				'default' => null
			),
			'reason_desc' => array(
				'type' => "VARCHAR",
				'constraint' => 200,
				'default' => null
			),
			'approved' => array(
				'type' => 'DATETIME',
				'default' => null
			),
			'declined' => array(
				'type' => 'DATETIME',
				'default' => null
			),
			'approverID' => array(
				'type' => 'INT',
				'default' => null
			),
			'created' => array(
				'type' => 'DATETIME'
			),
			'modified' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('itemID', TRUE);
		$this->dbforge->add_key('accountID');
		$this->dbforge->add_key('timesheetID');
		$this->dbforge->add_key('lessonID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('timesheets_mileage', FALSE, $attributes);

		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_mileage') . '` ADD CONSTRAINT `fk_timesheet_mileage_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_mileage') . '` ADD CONSTRAINT `fk_timesheet_mileage_approverID` FOREIGN KEY (`approverID`) REFERENCES `' . $this->db->dbprefix('staff') . '`(`staffID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_mileage') . '` ADD CONSTRAINT `fk_timesheet_mileage_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_mileage') . '` ADD CONSTRAINT `fk_timesheet_mileage_lessonID` FOREIGN KEY (`lessonID`) REFERENCES `' . $this->db->dbprefix('bookings_lessons') . '`(`lessonID`) ON DELETE CASCADE ON UPDATE CASCADE');

		// Add fields in mileage table
		$fields = array(
			'id' => array(
				'type' => 'INT',
				'constraint' => 11,
				'auto_increment' => TRUE
			),
			'accountID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'timesheetID' => array(
				'type' => 'INT',
				'constraint' => 11
			),
			'start_mileage' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'default' => 0
			),
			'end_mileage' => array(
				'type' => 'DECIMAL',
				'constraint' => "10,2",
				'default' => 0
			),
			'status' => array(
				'type' => "ENUM('unsubmitted','submitted','approved','declined')",
				'default' => 'unsubmitted'
			),
			'reason' => array(
				'type' => "ENUM('travel','training','marketing','admin','other')",
				'default' => null
			),
			'reason_desc' => array(
				'type' => "VARCHAR",
				'constraint' => 200,
				'default' => null
			),
			'approved' => array(
				'type' => 'DATETIME',
				'default' => null
			),
			'declined' => array(
				'type' => 'DATETIME',
				'default' => null
			),
			'approverID' => array(
				'type' => 'INT',
				'default' => null
			),
			'created' => array(
				'type' => 'DATETIME'
			),
			'modified' => array(
				'type' => 'DATETIME'
			)
		);
		$this->dbforge->add_field($fields);

		// add keys
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->add_key('accountID');
		$this->dbforge->add_key('timesheetID');

		// set table attributes
		$attributes = array(
			'ENGINE' => 'InnoDB'
		);

		// create table
		$this->dbforge->create_table('timesheets_fuel_card', FALSE, $attributes);

		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_fuel_card') . '` ADD CONSTRAINT `fk_timesheets_fuel_card_accountID` FOREIGN KEY (`accountID`) REFERENCES `' . $this->db->dbprefix('accounts') . '`(`accountID`) ON DELETE CASCADE ON UPDATE CASCADE');
		$this->db->query('ALTER TABLE `' . $this->db->dbprefix('timesheets_fuel_card') . '` ADD CONSTRAINT `fk_timesheets_fuel_card_timesheetID` FOREIGN KEY (`timesheetID`) REFERENCES `' . $this->db->dbprefix('timesheets') . '`(`timesheetID`) ON DELETE CASCADE ON UPDATE CASCADE');


		// Add key value in settings table
		$data = array(
			array(
				'key' => 'excluded_mileage',
				'title' => 'Excluded Mileage',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 451,
				'options' => '',
				'value' => '',
				'instruction' => 'The mileage entered here will be excluded every day from each member of staff'
			),
			array(
				'key' => 'mileage_default_start_location',
				'title' => 'Default Start Location',
				'type' => 'select',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 452,
				'options' => 'staff_main_address : Staff Main Address : work_address : Work Address',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_mode_of_transport',
				'title' => 'Default Mode of Transport',
				'type' => 'select',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 450,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_activate_fuel_cards',
				'title' => 'Activate Fuel Cards',
				'type' => 'checkbox',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 453,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_address1',
				'title' => 'Address 1',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 454,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_address2',
				'title' => 'Address 2',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 455,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_county',
				'title' => 'County',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 456,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_town',
				'title' => 'Town',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 457,
				'options' => '',
				'value' => '',
				'instruction' => ''
			),
			array(
				'key' => 'mileage_default_postcode',
				'title' => 'Post Code',
				'type' => 'text',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 458,
				'options' => '',
				'value' => '',
				'instruction' => ''
			)
		);

		// bulk insert
		$this->db->insert_batch("settings", $data);

		// Add key value in settings_fields table

		$data = array(
			array(
				"section" => "staff_recruitment",
				"field" => "mileage_default_start_location",
				"label" => "Default Start Location",
				"show" => 1,
				"required" => 0,
				"order" => 1050,
				"locked" => 0
			),
			array(
				"section" => "staff_recruitment",
				"field" => "mileage_activate_fuel_cards",
				"label" => "Activate Fuel Cards",
				"show" => 1,
				"required" => 0,
				"order" => 1051,
				"locked" => 0
			),
			array(
				"section" => "staff_recruitment",
				"field" => "mileage_default_mode_of_transport",
				"label" => "Default Mode of Transport",
				"show" => 1,
				"required" => 0,
				"order" => 1052,
				"locked" => 0
			)
		);

		//bulk insert
		$this->db->insert_batch("settings_fields", $data);

		//Add column in staff
		$fields = array(
			'default_start_location' =>array(
				'type' => 'varchar',
				'constraint' => 255,
				'default' => NULL,
				'after' => 'payroll_number'
			),
			'mileage_activate_fuel_cards' =>array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => 0,
				'after' => 'payroll_number'
			),
			'mileage_default_mode_of_transport' =>array(
				'type' => 'INT',
				'constraint' => 11,
				'default' => 0,
				'after' => 'payroll_number'
			)
		);

		$this->dbforge->add_column("staff", $fields);

		// Add column in accounts table
		$fields = array(
			'addon_mileage' =>array(
				'type' => 'TINYINT(1)',
				'default' => 0,
				'after' => 'addon_bikeability'
			)
		);

		$this->dbforge->add_column("accounts", $fields);

		// Add Coumn in mileage table
		$fields = array(
			'total_mileage' =>array(
				'type' => 'FLOAT',
				'constraint' => "10,2",
				'default' => 0,
				'after' => 'approverID'
			),'total_cost' =>array(
				'type' => 'FLOAT',
				'constraint' => "10,2",
				'default' => 0,
				'after' => 'approverID'
			)
		);
		$this->dbforge->add_column("timesheets_mileage", $fields);

	}

	public function down() {
		// remove mileage table, if exists
		$this->dbforge->drop_table('mileage', TRUE);

		$this->dbforge->drop_table('timesheets_mileage', TRUE);

		$this->dbforge->drop_table('timesheets_fuel_card', TRUE);

		$this->dbforge->drop_column('staff', 'mileage_activate_fuel_cards');

		$this->dbforge->drop_column('staff', 'default_start_location');

		$this->dbforge->drop_column('staff', 'mileage_default_mode_of_transport');

		$this->dbforge->drop_column('accounts', 'addon_mileage');

		$this->db->from('settings_fields')->where('section', "staff_recruitment")->where('field', "mileage_default_start_location")->delete();

		$this->db->from('settings_fields')->where('section', "staff_recruitment")->where('field', "mileage_activate_fuel_cards")->delete();

		$this->db->from('settings_fields')->where('section', "staff_recruitment")->where('field', "mileage_default_mode_of_transport")->delete();

		$where_in = array(
			'excluded_mileage',
			'mileage_default_start_location',
			'mileage_default_mode_of_transport',
			'mileage_activate_fuel_cards',
			'mileage_default_address1',
			'mileage_default_address2',
			'mileage_default_county',
			'mileage_default_town',
			'mileage_default_postcode',
		);

		$this->db->from('settings')->where_in('key', $where_in)->delete();

	}
}
