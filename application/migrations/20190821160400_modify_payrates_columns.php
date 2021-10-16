<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Modify_payrates_columns extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// lod db forge
		$this->load->dbforge();
	}

	public function up() {
        $this->dbforge->drop_column('session_qual_rates', 'pay_rate');
        $this->dbforge->drop_column('session_qual_rates', 'increased_pay_rate');


        $this->dbforge->add_column('session_qual_rates', [
            'pay_rate' => [
                'type' => 'TEXT'
            ],
            'increased_pay_rate' => [
                'type' => 'TEXT'
            ]
        ]);
	}

	public function down() {
        $this->dbforge->drop_column('session_qual_rates', 'pay_rate');
        $this->dbforge->drop_column('session_qual_rates', 'increased_pay_rate');

        $this->dbforge->add_column('session_qual_rates', [
            'pay_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ],
            'increased_pay_rate' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0
            ]
        ]);
	}
}
