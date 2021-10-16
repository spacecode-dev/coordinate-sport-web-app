<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Setting_rename_event_confirmation extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
		// update settings
        $data = array(
            'title' => "Send Project Booking Confirmation",
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'send_event_confirmation'
		);
        $this->db->update('settings', $data, $where, 1);

		// update settings
        $data = array(
            'title' => "Project Booking Confirmation",
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation'
		);
        $this->db->update('settings', $data, $where, 1);

        // update settings
        $data = array(
            'title' => "Project Booking Confirmation Subject",
            'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
        );
		$where = array(
			'key' => 'email_event_confirmation_subject'
		);
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {
		// no going back
    }
}
