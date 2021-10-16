<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Family_account_balance_fields extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
		// add fields
        $fields = array(
            'account_balance' => array(
                'type' => 'DECIMAL',
				'constraint' => '8,2',
                'null' => 0,
                'after' => 'imported'
            ),
			'credit_limit' => array(
                'type' => 'DECIMAL',
				'constraint' => '8,2',
                'null' => TRUE,
                'after' => 'account_balance'
            )
        );
        $this->dbforge->add_column('family', $fields);
    }

    public function down() {
        // remove fields
		$this->dbforge->drop_column('family', 'account_balance');
		$this->dbforge->drop_column('family', 'credit_limit');
    }
}
