<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_email_multiple extends CI_Migration {

    public $integration_fields;

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // update field
        $fields = array(
            'type' => array(
                'name' => 'type',
                'type' => "ENUM('text', 'textarea', 'number', 'email', 'email-multiple', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html')",
                'null' => FALSE,
                'default' => 'text'
            )
        );
        $this->dbforge->modify_column('settings', $fields);

        // update setting
        $data = array(
            'type' => 'email-multiple'
        );
        $where = array(
            'key' => 'staff_invoice_to'
        );
        $this->db->update('settings', $data, $where, 1);
    }

    public function down() {
        // update setting
        $data = array(
            'type' => 'email'
        );
        $where = array(
            'key' => 'staff_invoice_to'
        );
        $this->db->update('settings', $data, $where, 1);

        // update field
        $fields = array(
            'type' => array(
                'name' => 'type',
                'type' => "ENUM('text', 'textarea', 'number', 'email', 'wysiwyg', 'staff', 'select', 'image', 'checkbox', 'brand', 'url', 'tel', 'html')",
                'null' => FALSE,
                'default' => 'text'
            )
        );
        $this->dbforge->modify_column('settings', $fields);
    }
}