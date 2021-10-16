<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Move_stripe_webhook_secret_setting extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		$where = array(
			'key' => 'stripe_whs'
		);
		$res_check = $this->db->from('settings')->where($where)->get();

		if ($res_check->num_rows()>0) {
			$data = array("order" => 131);
			$this->db->update('settings', $data, $where, 1);
		}
    }

    public function down() {
		$where = array(
			'key' => 'stripe_whs'
		);
		$res_check = $this->db->from('settings')->where($where)->get();

		if ($res_check->num_rows()>0) {
			$data = array("order" => 132);
			$this->db->update('settings', $data, $where, 1);
		}
    }
}
