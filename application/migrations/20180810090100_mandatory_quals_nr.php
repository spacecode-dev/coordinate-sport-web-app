<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Mandatory_quals_nr extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // add field
            $fields = array(
                'valid' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'qualID'
                ),
                'not_required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'valid'
                )
            );
            $this->dbforge->add_column('staff_quals_mandatory', $fields);

            // update existing mandatory quals as valid
            $data = array(
                'valid' => 1
            );
            $this->db->update('staff_quals_mandatory', $data);

            // add fields
            $fields = array(
                'qual_first_not_required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'qual_first_expiry_date'
                ),
                'qual_child_not_required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'qual_child_expiry_date'
                ),
                'qual_fsscrb_not_required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'qual_fsscrb_expiry_date'
                ),
                'qual_othercrb_not_required' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'after' => 'qual_othercrb_expiry_date'
                )
            );
            $this->dbforge->add_column('staff', $fields);
        }

        public function down() {
            // remove fields
            //$this->dbforge->drop_column('staff_quals_mandatory', 'valid');
            $this->dbforge->drop_column('staff_quals_mandatory', 'not_required');
            $this->dbforge->drop_column('staff', 'qual_first_not_required');
            $this->dbforge->drop_column('staff', 'qual_child_not_required');
            $this->dbforge->drop_column('staff', 'qual_fsscrb_not_required');
            $this->dbforge->drop_column('staff', 'qual_othercrb_not_required');
        }
}