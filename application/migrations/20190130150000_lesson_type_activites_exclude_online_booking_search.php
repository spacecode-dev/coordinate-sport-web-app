<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_type_activites_exclude_online_booking_search extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'exclude_online_booking_search' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
                'null' => FALSE,
                'after' => 'session_evaluations'
            ),
			'exclude_online_booking_price_summary' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
                'null' => FALSE,
                'after' => 'exclude_online_booking_search'
            )
        );
        $this->dbforge->add_column('lesson_types', $fields);

		// add fields
        $fields = array(
            'exclude_online_booking_search' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
                'null' => FALSE,
                'after' => 'name'
            )
        );
        $this->dbforge->add_column('activities', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('lesson_types', 'exclude_online_booking_search');
		$this->dbforge->drop_column('lesson_types', 'exclude_online_booking_price_summary');
		$this->dbforge->drop_column('activities', 'exclude_online_booking_search');
    }
}
