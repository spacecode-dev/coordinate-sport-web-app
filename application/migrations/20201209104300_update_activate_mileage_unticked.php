<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_activate_mileage_unticked extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
		
		// increase timeout and memory limit
		set_time_limit(0);
		ini_set('memory_limit', '1024M');
		
	}

	public function up() {
		
		//update field default value
		$fields = array(
			'activate_mileage' => array(
				'name' => "activate_mileage",
				'type' => "tinyint(1)",
				'default' => 0
			)
		);
		$this->dbforge->modify_column('staff', $fields);
		
		$where = array(
			"addon_mileage" => 0
		);
		$query = $this->db->from("accounts")->where($where)->get();
		
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$where = array(
					"activate_mileage" => 1,
					"accountID" => $result->accountID
				);
				$data = array(
					"activate_mileage" => 0
				);
				$this->db->update("staff", $data, $where);
			}
		}
	
	}

	public function down() {
		//reverse
		$fields = array(
			'activate_mileage' => array(
				'name' => "activate_mileage",
				'type' => "tinyint(1)",
				'default' => 1
			)
		);
		$this->dbforge->modify_column('staff', $fields);
		
		$where = array(
			"addon_mileage" => 0
		);
		$query = $this->db->from("accounts")->where($where)->get();
		
		if($query->num_rows() > 0){
			foreach($query->result() as $result){
				$where = array(
					"activate_mileage" => 0,
					"accountID" => $result->accountID
				);
				$data = array(
					"activate_mileage" => 1
				);
				$this->db->update("staff", $data, $where);
			}
		}
	}
}
