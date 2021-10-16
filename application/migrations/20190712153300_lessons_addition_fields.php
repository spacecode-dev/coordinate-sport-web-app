<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lessons_Addition_Fields extends CI_Migration {

	public function __construct() {
		parent::__construct();

		// load db forge
		$this->load->dbforge();
	}

	public function up() {
		// modify settings fields
		$fields = array(
			'staff_required_participant' => array(
				'type' => 'BOOLEAN',
				'after' => 'staff_required_lead',
				'default' => 0
			),
            'staff_required_observer' => array(
                'type' => 'BOOLEAN',
                'after' => 'staff_required_participant',
                'default' => 0
            )
		);
		$this->dbforge->add_column('bookings_lessons', $fields);
	}

	public function down() {
		$this->dbforge->drop_column('bookings_lessons', 'staff_required_participant');
		$this->dbforge->drop_column('bookings_lessons', 'staff_required_observer');
	}
}