<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_rename_field_sagepay extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// update setting
			
			$data = array("title" => 'Opayo (Sage Pay) Encryption Password');
			$where = array(
				'key' => 'sagepay_encryption_password'
			);
			$this->db->update('settings', $data, $where, 1);
			
			$data = array("title" => 'Opayo (Sage Pay) Environment', 'instruction' => 'For Opayo (Sage Pay) Form Integration v3');
			$where = array(
				'key' => 'sagepay_environment'
			);
			$this->db->update('settings', $data, $where, 1);
			
			$data = array("title" => 'Opayo (Sage Pay) Vendor Name');
			$where = array(
				'key' => 'sagepay_vendor'
			);
			$this->db->update('settings', $data, $where, 1);

		}

		public function down() {
			// revert setting
			
			$data = array("title" => 'SagePay Encryption Password');
			$where = array(
				'key' => 'sagepay_encryption_password'
			);
			$this->db->update('settings', $data, $where, 1);
			
			$data = array("title" => 'SagePay Environment', 'instruction' => 'For SagePay Form Integration v3');
			$where = array(
				'key' => 'sagepay_environment'
			);
			$this->db->update('settings', $data, $where, 1);
			
			$data = array("title" => 'SagePay Vendor Name');
			$where = array(
				'key' => 'sagepay_vendor'
			);
			$this->db->update('settings', $data, $where, 1);
		}
}
