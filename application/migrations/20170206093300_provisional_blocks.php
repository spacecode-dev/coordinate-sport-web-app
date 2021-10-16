<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Provisional_blocks extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // lod db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'provisional' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE,
                'after' => 'byID'
            )
        );
        $this->dbforge->add_column('bookings_blocks', $fields);

        // if booking is provisional, make all blocks provisional
        $where = array(
            'provisional' => 1
        );
        $res = $this->db->from('bookings')->where($where)->get();
        if ($res->num_rows() > 0) {
            foreach ($res->result() as $row) {
                $data = array(
                    'provisional' => 1
                );
                $where = array(
                    'bookingID' => $row->bookingID
                );
                $res_update = $this->db->update('bookings_blocks', $data, $where);
            }
        }

        // rename old provisional field
        $fields = array(
            'provisional' => array(
                'name' => 'provisional_old',
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('bookings', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings_blocks', 'provisional');

        // rename old provisional field back
        $fields = array(
            'provisional_old' => array(
                'name' => 'provisional',
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => FALSE
            )
        );
        $this->dbforge->modify_column('bookings', $fields);
    }
}