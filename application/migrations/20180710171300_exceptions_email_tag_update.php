<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exceptions_email_tag_update extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // replace {main_contact} with {contact_name}
            $res = $this->db->from('settings')->like('key', 'email_exception_')->get();
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $data = array(
                        'value' => str_replace('{main_contact}', '{contact_name}', $row->value),
                        'instruction' => str_replace('{main_contact}', '{contact_name}', $row->instruction)
                    );
                    $where = array(
                        'key' => $row->key
                    );
                    $this->db->update('settings', $data, $where, 1);
                }
            }
        }

        public function down() {
            // replace {contact_name} with {main_contact}
            $res = $this->db->from('settings')->like('key', 'email_exception_')->get();
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $data = array(
                        'value' => str_replace('{contact_name}', '{main_contact}', $row->value),
                        'instruction' => str_replace('{contact_name}', '{main_contact}', $row->instruction)
                    );
                    $where = array(
                        'key' => $row->key
                    );
                    $this->db->update('settings', $data, $where, 1);
                }
            }
        }
}