<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_setting_names extends CI_Migration {
    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
    
        $data = array (
            array(
                'key' => 'not_checkedin_staff_alert_emailsms',
                'title' => 'Not Checked In - Staff Alert',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            ),
            array(
                'key' => 'staff_checkin_outside_account_alert_emailsms',
                'title' => 'Staff Checked in Outside of Session Location - Account Alert',
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            )
        );

        $this->db->update_batch('settings', $data, 'key');
    }

    public function down() {
        
    }
}