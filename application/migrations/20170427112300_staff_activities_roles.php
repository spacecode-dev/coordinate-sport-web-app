<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_activities_roles extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add fields
            $fields = array(
                'assistant' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'activityID'
                ),
                'lead' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'activityID'
                ),
                'head' => array(
                    'type' => "TINYINT",
                    'constraint' => 1,
                    'null' => FALSE,
                    'default' => 0,
                    'after' => 'activityID'
                )
            );
            $this->dbforge->add_column('staff_activities', $fields);

            // map existing activities to assistant
            $res = $this->db->from('staff_activities')->get();
            if ($res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $data = array(
                        'assistant' => 1,
                        'modified' => mdate('%Y-%m-%d %H:%i:%s')
                    );
                    $where = array(
                        'linkID' => $row->linkID
                    );
                    $res_update = $this->db->update('staff_activities', $data, $where, 1);
                }
            }
        }

        public function down() {
            // remove fields
            $this->dbforge->drop_column('staff_activities', 'head');
            $this->dbforge->drop_column('staff_activities', 'lead');
            $this->dbforge->drop_column('staff_activities', 'assistant');
        }
}