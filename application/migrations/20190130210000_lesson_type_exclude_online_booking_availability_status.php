<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Lesson_type_exclude_online_booking_availability_status extends CI_Migration {

    public function __construct() {
        parent::__construct();

        // load db forge
        $this->load->dbforge();
    }

    public function up() {
        // add fields
        $fields = array(
            'exclude_online_booking_availability_status' => array(
                'type' => 'TINYINT',
				'constraint' => 1,
				'default' => 0,
                'null' => FALSE,
                'after' => 'exclude_online_booking_price_summary'
            )
        );
        $this->dbforge->add_column('lesson_types', $fields);
    }

    public function down() {
        // remove fields
        $this->dbforge->drop_column('lesson_types', 'exclude_online_booking_availability_status');
    }
}
