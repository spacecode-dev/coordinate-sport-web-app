<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_exclude_mileage extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		
		// Add Key 
		$data = array(
			'key' => 'excluded_mileage_without_fuel_card',
			'title' => 'Excluded Mileage without Fuel Card',
			'type' => 'text',
			'section' => 'general',
			'subsection' => 'timesheets_general',
			'order' => 452,
			'options' => '',
			'value' => '',
			'instruction' => 'The mileage entered here will be excluded every day from each member of staff that does not use a fuel card'
		);
		
		//insert
		$this->db->insert("settings", $data);
		
		// Update Key
		$where = array(
			"key" => "excluded_mileage"
		);
		$data = array(
			"title" => "Excluded Mileage with Fuel Card",
			"instruction" => "The mileage entered here will be excluded every day from each member of staff that uses a Fuel Card"
		);
		
		$this->db->update("settings", $data, $where);
		
	}

	public function down() {
		// revese
		$this->db->from('settings')->where('key', "excluded_mileage_without_fuel_card")->delete();
		
		// Update Key
		$where = array(
			"key" => "excluded_mileage"
		);
		$data = array(
			"title" => "Excluded Mileage",
			"instruction" => "The mileage entered here will be excluded every day from each member of staff"
		);
		
		$this->db->update("settings", $data, $where);
	}
}
