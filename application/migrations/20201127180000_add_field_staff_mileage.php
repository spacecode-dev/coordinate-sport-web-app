<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_field_staff_mileage extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Add column in staff table
			$fields = array(
				'activate_mileage' => array(
					'type' => 'TINYINT(1)',
					'default' => 1,
					'after' => 'default_start_location'
				)
			);
			
			$this->dbforge->add_column("staff", $fields);
			
			// Add column in fuel card
			$fields = array(
				'receipt_size' =>array(
					'type' => 'bigint',
					'constraint' => 100,
					'default' => NULL,
					'after' => 'reason_desc'
				),
				'receipt_ext' =>array(
					'type' => 'varchar',
					'constraint' => 100,
					'default' => NULL,
					'after' => 'reason_desc'
				),
				'receipt_type' =>array(
					'type' => 'varchar',
					'constraint' => 100,
					'default' => NULL,
					'after' => 'reason_desc'
				),
				'receipt_path' =>array(
					'type' => 'varchar',
					'constraint' => 100,
					'default' => NULL,
					'after' => 'reason_desc'
				),
				'receipt_name' =>array(
					'type' => 'varchar',
					'constraint' => 100,
					'default' => NULL,
					'after' => 'reason_desc'
				)
			);
			
			$this->dbforge->add_column("timesheets_fuel_card", $fields);
			
			$data = array(
				'key' => 'automatically_approve_fuel_card',
				'title' => 'Automatically Approve Fuel Card Mileage',
				'type' => 'checkbox',
				'section' => 'general',
				'subsection' => 'timesheets_general',
				'order' => 459,
				'options' => '',
				'value' => 1,
				'instruction' => ''
			);
			
			//insert
			$this->db->insert("settings", $data);
			
			// Add key value in settings_fields table
			$data = array(
				"section" => "staff_recruitment",
				"field" => "activate_mileage",
				"label" => "Activate Mileage",
				"show" => 1,
				"required" => 0,
				"order" => 1051,
				"locked" => 0
			);
			
			//insert
			$this->db->insert("settings_fields", $data);
		}

		public function down() {
			// revese
			$this->dbforge->drop_column('staff', 'activate_mileage');
			$this->dbforge->drop_column('timesheets_fuel_card', 'receipt_size');
			$this->dbforge->drop_column('timesheets_fuel_card', 'receipt_ext');
			$this->dbforge->drop_column('timesheets_fuel_card', 'receipt_type');
			$this->dbforge->drop_column('timesheets_fuel_card', 'receipt_path');
			$this->dbforge->drop_column('timesheets_fuel_card', 'receipt_name');
			
			$this->db->from('settings')->where('key', "automatically_approve_fuel_card")->delete();
			$this->db->from('settings_fields')->where('key', "activate_mileage")->where('section', "staff_recruitment")->delete();
			
		}
}
