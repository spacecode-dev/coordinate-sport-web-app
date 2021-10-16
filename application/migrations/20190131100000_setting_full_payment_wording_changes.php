<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_full_payment_wording_changes extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // update settings
        $data = array(
            'instruction' => '',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'require_full_payment'
		);
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {
		// update settings
        $data = array(
            'instruction' => 'Only applicable to Stripe payments',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'require_full_payment'
		);
        $this->db->update('settings', $data, $where, 1);
    }
}
