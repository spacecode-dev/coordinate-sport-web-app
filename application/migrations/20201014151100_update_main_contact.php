<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_update_main_contact extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();

			// increase timeout and memory limit
			set_time_limit(0);
			ini_set('memory_limit', '1024M');
		}

		public function up() {
			// update main_contact
			// get accounts
    		$accounts = $this->db->from('accounts')->get();
			// populate accounts with existing activities and migrate
    		foreach ($accounts->result() as $account) {
				$where = array("accountID" => $account->accountID);
				$res = $this->db->select("*")->from("family")->where($where)->get();
				if($res->num_rows() > 0){
					foreach($res->result() as $result){
						$where = array("accountID" => $account->accountID,
						"familyID" => $result->familyID,
						"main" => '1');
						$res_query = $this->db->select("*")->from("family_contacts")->where($where)->get();
						if($res_query->num_rows() == 0){
							$where = array("accountID" => $account->accountID,
							"familyID" => $result->familyID);
							$res_query = $this->db->select("*")->from("family_contacts")->where($where)->get();
							if($res_query->num_rows() > 1){
								$order = "added asc";
								$res_query = $this->db->select("*")->from("family_contacts")->where($where)->order_by($order)->limit(1)->get();
								foreach($res_query->result() as $res_result){
									$data = array("main" => '1');
									$where["contactID"] = $res_result->contactID;
									$this->db->update('family_contacts', $data, $where, 1);
								}
							}else{
								$data = array("main" => '1');
								$this->db->update('family_contacts', $data, $where, 1);
							}
						}
					}
				}
			}
		}

		public function down() {

		}
}
