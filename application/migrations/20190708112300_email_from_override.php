<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Email_from_override extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
			// add column
            $fields = array(
                'readonly' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
					'default' => 0,
                    'null' => FALSE,
                    'after' => 'max_width'
                )
            );
            $this->dbforge->add_column('settings', $fields);

            // add setting
            $data = array(
                'key' => 'email_from_override',
                'title' => 'Send Emails From',
                'type' => 'email',
                'section' => 'emailsms',
                'order' => 0,
                'value' => '',
                'instruction' => '',
				'readonly' => 1,
                'created_at' => mdate('%Y-%m-%d %H:%i:%s'),
                'updated_at' => mdate('%Y-%m-%d %H:%i:%s')
            );
            $this->db->insert('settings', $data);
        }

        public function down() {
			// remove fields
            $this->dbforge->drop_column('settings', 'readonly');

            // remove setting
            $where = array(
                'key' => 'email_from_override'
            );
            $this->db->delete('settings', $where, 1);
        }
}
