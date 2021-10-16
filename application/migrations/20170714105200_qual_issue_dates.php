<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Qual_issue_dates extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'issue_date' => array(
                'type' => 'DATE',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'reference'
            ),
        );
        $this->dbforge->add_column('staff_quals', $fields);

        // update field
        $fields = array(
            'expiry' => array(
                'name' => 'expiry_date',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('staff_quals', $fields);

        // add fields
        $fields = array(
            'qual_first_issue_date' => array(
                'type' => 'DATE',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'qual_first'
            ),
            'qual_child_issue_date' => array(
                'type' => 'DATE',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'qual_child'
            ),
            'qual_fsscrb_issue_date' => array(
                'type' => 'DATE',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'qual_fsscrb'
            ),
            'qual_othercrb_issue_date' => array(
                'type' => 'DATE',
                'default' => NULL,
                'null' => TRUE,
                'after' => 'qual_othercrb'
            ),
        );
        $this->dbforge->add_column('staff', $fields);

        // update fields
        $fields = array(
            'qual_first_expiry' => array(
                'name' => 'qual_first_expiry_date',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_child_expiry' => array(
                'name' => 'qual_child_expiry_date',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_fsscrb_expiry' => array(
                'name' => 'qual_fsscrb_expiry_date',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_othercrb_expiry' => array(
                'name' => 'qual_othercrb_expiry_date',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('staff', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('staff_quals', 'issue_date');

        // update field
        $fields = array(
            'expiry_date' => array(
                'name' => 'expiry',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('staff_quals', $fields);

        // remove fields
        $this->dbforge->drop_column('staff', 'qual_first_issue_date');
        $this->dbforge->drop_column('staff', 'qual_child_issue_date');
        $this->dbforge->drop_column('staff', 'qual_fsscrb_issue_date');
        $this->dbforge->drop_column('staff', 'qual_othercrb_issue_date');

        // update fields
        $fields = array(
            'qual_first_expiry_date' => array(
                'name' => 'qual_first_expiry',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_child_expiry_date' => array(
                'name' => 'qual_child_expiry',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_fsscrb_expiry_date' => array(
                'name' => 'qual_fsscrb_expiry',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            ),
            'qual_othercrb_expiry_date' => array(
                'name' => 'qual_othercrb_expiry',
                'type' => "DATE",
                'default' => NULL,
                'null' => TRUE
            )
        );
        $this->dbforge->modify_column('staff', $fields);
    }
}