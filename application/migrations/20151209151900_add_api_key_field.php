<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_api_key_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // add api_key field to accounts
            $fields = array(
                'api_key' => array(
                    'type' => 'VARCHAR(32) NOT NULL',
                    'after' => 'phone'
                )
            );

            $this->dbforge->add_column('accounts', $fields);

            // generate api keys for existing accounts
            $res = $this->db->from('accounts')->get();

            if ($res->num_rows() > 0 ){
                foreach ($res->result() as $row) {
                    // update account
                    $where = array(
                        'accountID' => $row->accountID
                    );
                    $data = array(
                        'api_key' => $this->crm_library->generate_api_key()
                    );
                    $this->db->update('accounts', $data, $where);
                }
            }
        }

        public function down() {
            // remove api_key field from accounts
            $this->dbforge->drop_column('accounts', 'api_key');
        }
}