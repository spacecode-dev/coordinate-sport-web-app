<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_email_settings_general extends CI_Migration {
    
    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        $where = array(
            'key' => 'email',
            'section' => 'general',
            'subsection' => 'general_general'
        );

        $data = array (
            'title' => 'Default Account Email'
        );

        $this->db->update('settings', $data, $where);
    }

    public function down() {

    }
}