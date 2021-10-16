<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_mandatory_quals_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {

			$fields = array(
			'require_reference' => array(
				'type' => 'tinyint(1)',
				'default' => 0,
				'after' => 'name'
			),
			'require_issue_expiry_date' => array(
				'type' => 'tinyint(1)',
				'default' => 0,
				'after' => 'name'
			));

			$this->dbforge->add_column('mandatory_quals', $fields);

			$fields = array(
			'reference' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'default' => NULL,
				'after' => 'not_required'
			),
			'expiry_date' => array(
				'type' => 'date',
				'default' => NULL,
				'after' => 'not_required'
			),
			'issue_date' => array(
				'type' => 'date',
				'default' => NULL,
				'after' => 'not_required'
			),);

			$this->dbforge->add_column('staff_quals_mandatory', $fields);

        }

        public function down() {
            $this->dbforge->drop_column('mandatory_quals', 'require_reference');
            $this->dbforge->drop_column('mandatory_quals', 'require_issue_expiry_date');
            $this->dbforge->drop_column('staff_quals_mandatory', 'reference');
            $this->dbforge->drop_column('staff_quals_mandatory', 'expiry_date');
            $this->dbforge->drop_column('staff_quals_mandatory', 'issue_date');
        }
}
