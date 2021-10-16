<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_integrations extends CI_Migration {

    public $integration_fields;

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();

        $this->integration_fields = array(
            'mailchimp_key',
            'gocardless_access_token',
            'gocardless_app_id',
            'gocardless_app_secret',
            'gocardless_environment',
            'gocardless_merchant_id',
            'gocardless_success_redirect'
        );
    }

    public function up() {
        // update field
        $fields = array(
            'section' => array(
                'name' => 'section',
                'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard', 'integrations')",
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('settings', $fields);

        // move sections
        $data = array(
            'section' => 'integrations'
        );
        $this->db->where_in('key', $this->integration_fields);
        $this->db->update('settings', $data);
    }

    public function down() {
        // update field
        $fields = array(
            'section' => array(
                'name' => 'section',
                'type' => "ENUM('general', 'styling', 'global', 'emailsms', 'dashboard')",
                'default' => FALSE
            )
        );
        $this->dbforge->modify_column('settings', $fields);

        // move sections
        $data = array(
            'section' => 'general'
        );
        $this->db->where_in('key', $this->integration_fields);
        $this->db->update('settings', $data);
    }
}