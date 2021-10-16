<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Settings_checkbox_toggles extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add column
            $fields = array(
                'toggle_fields' => array(
                    'type' => "VARCHAR",
                    'constraint' => 200,
                    'null' => TRUE,
                    'after' => 'instruction'
                )
            );
            $this->dbforge->add_column('settings', $fields);

            // update settings
            $data = array(
                'toggle_fields' => 'email_birthday_email_subject,email_birthday_email,email_birthday_email_brand,email_birthday_email_image'
            );
            $where = array(
                'key' => 'send_birthday_emails'
            );
            $this->db->update('settings', $data, $where, 1);
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('settings', 'toggle_fields');
        }
}