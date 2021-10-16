<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Default_credit_limit extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// define new settings
		$data = array(
			array(
				'key' => 'default_credit_limit',
				'title' => 'Default Cash Credit Limit',
				'type' => 'number',
				'section' => 'general',
				'order' => 430,
				'value' => 100,
				'instruction' => 'Participants can\'t exceed this amount of cash debt when booking',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);

		// bulk insert
		$this->db->insert_batch('settings', $data);
    }

    public function down() {
		// remove new settings
		$where_in = array(
			'default_credit_limit'
		);
		$this->db->from('settings')->where_in('key', $where_in)->delete();
    }
}
