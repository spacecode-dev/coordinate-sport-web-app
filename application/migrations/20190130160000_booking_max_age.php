<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Booking_max_age extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'max_age' => array(
                'type' => "INT",
                'constraint' => 3,
                'default' => 0,
                'null' => TRUE,
                'after' => 'min_age'
            )
        );
        $this->dbforge->add_column('bookings', $fields);

		// add fields
		$fields = array(
			'max_age' => array(
				'type' => "INT",
				'constraint' => 3,
				'default' => NULL,
				'null' => TRUE,
				'after' => 'min_age'
			)
		);
		$this->dbforge->add_column('bookings_blocks', $fields);

		// define new settings
		$data = array(
			array(
				'key' => 'max_age',
				'title' => 'Maximum Age for Online Booking',
				'type' => 'number',
				'section' => 'general',
				'order' => 366,
				'value' => 99,
				'instruction' => '',
				'created_at' => mdate('%Y-%m-%d %H:%i:%s')
			)
		);

		// bulk insert
		$this->db->insert_batch('settings', $data);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('bookings', 'max_age');
		$this->dbforge->drop_column('bookings_blocks', 'max_age');

		// remove new settings
		$where_in = array(
			'max_age'
		);
		$this->db->from('settings')->where_in('key', $where_in)->delete();
    }
}
