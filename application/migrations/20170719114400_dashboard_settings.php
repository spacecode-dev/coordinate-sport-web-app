<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Dashboard_settings extends CI_Migration {

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
                'type' => "ENUM('text','textarea','number','email','wysiwyg','staff','select','image','checkbox','brand','url','tel','html')",
                'default' => 'text',
                'null' => FALSE
            ),
            'section' => array(
                'name' => 'section',
                'type' => "ENUM('general','styling','global','emailsms','dashboard')",
                'default' => 'general',
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('settings', $fields);

        $j = 1;
        for ($i=1; $i <= 3; $i++) {
            // define new settings
            $data = array(
                array(
                    'key' => 'dashboard_custom_widget_' . $i . '_title',
                    'title' => 'Custom Widget 1 Title',
                    'type' => 'text',
                    'section' => 'dashboard',
                    'order' => $j,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                ),
                array(
                    'key' => 'dashboard_custom_widget_' . $i . '_html',
                    'title' => 'Custom Widget 1 HTML',
                    'type' => 'html',
                    'section' => 'dashboard',
                    'order' => $j+1,
                    'value' => '',
                    'instruction' => '',
                    'created_at' => mdate('%Y-%m-%d %H:%i:%s')
                )
            );
            $j += 2;

            // bulk insert
            $this->db->insert_batch('settings', $data);
        }
    }

    public function down() {
        // remove new settings
        $where_in = array(
            'dashboard_custom_widget_1_title',
            'dashboard_custom_widget_1_html',
            'dashboard_custom_widget_2_title',
            'dashboard_custom_widget_2_html',
            'dashboard_custom_widget_3_title',
            'dashboard_custom_widget_3_html'
        );
        $this->db->from('settings')->where_in('key', $where_in)->delete();

        // update field
        $fields = array(
            'type' => array(
                'name' => 'type',
                'type' => "ENUM('text','textarea','number','email','wysiwyg','staff','select','image','checkbox','brand','url','tel')",
                'default' => 'text',
                'null' => FALSE
            ),
            'section' => array(
                'name' => 'section',
                'type' => "ENUM('general','styling','global','emailsms')",
                'default' => 'general',
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('settings', $fields);
    }
}