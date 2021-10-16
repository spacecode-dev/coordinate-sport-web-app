<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_participant_account_module_set_existed_accounts_to_true extends CI_Migration {

		public function __construct() {
			parent::__construct();

		}

		public function up() {
			$data = array(
				'addon_participant_account_module' => '1'
			);
			$this->db->update('accounts', $data);
		}

		public function down() {
		}
}
