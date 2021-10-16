<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_gocardless_email_subject extends CI_Migration {
    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $data = array (
            'value' => 'Complete your direct debit set up',
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );

        $where = array(
			'key' => 'email_gocardless_mandate_subject'
		);
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {

    }
}