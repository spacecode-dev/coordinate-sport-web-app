<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Staff_additional_payscales extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'payments_scale_lead' => array(
                'type' => 'DECIMAL',
                'after' => 'payments_scale_assist',
                'constraint' => '10,2',
                'default' => 0
			),
            'payments_scale_participant' => array(
                'type' => 'DECIMAL',
                'after' => 'payments_scale_lead',
                'constraint' => '10,2',
                'default' => 0
            ),
            'payments_scale_observer' => array(
                'type' => 'DECIMAL',
                'after' => 'payments_scale_participant',
                'constraint' => '10,2',
                'default' => 0
            )
		);
		$this->dbforge->add_column('staff', $fields);

	}

	public function down() {
		$this->dbforge->drop_column('staff', 'payments_scale_lead');
		$this->dbforge->drop_column('staff', 'payments_scale_participant');
		$this->dbforge->drop_column('staff', 'payments_scale_observer');
	}
}