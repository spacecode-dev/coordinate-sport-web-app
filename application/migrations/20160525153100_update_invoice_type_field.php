<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_invoice_type_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // lod db forge
            $this->load->dbforge();
        }

        public function up() {
            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('booking','blocks','contract pricing','other','participants','participants per block','particpants per session')",
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('bookings_invoices', $fields);

            // update existing with participants type
            $data = array(
                'type' => 'participants per session'
            );
            $where = array(
                'type' => 'participants'
            );
            $this->db->update('bookings_invoices', $data, $where);

            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('booking','blocks','contract pricing','other','participants per block','particpants per session')",
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('bookings_invoices', $fields);
        }

        public function down() {
            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('booking','blocks','contract pricing','other','participants','participants per block','particpants per session')",
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('bookings_invoices', $fields);

            // update existing with participants type
            $data = array(
                'type' => 'participants'
            );
            $where = array(
                'type' => 'participants per session'
            );
            $this->db->update('bookings_invoices', $data, $where);

            // update field
            $fields = array(
                'type' => array(
                    'name' => 'type',
                    'type' => "ENUM('booking','blocks','contract pricing','other','participants')",
                    'default' => NULL
                )
            );
            $this->dbforge->modify_column('bookings_invoices', $fields);
        }
}