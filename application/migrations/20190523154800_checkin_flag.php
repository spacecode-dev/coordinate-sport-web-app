<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Checkin_flag extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand activities fields
            $fields = array(
                'not_checked_in' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'accuracy'
                ),
            );
			$this->dbforge->add_column('bookings_lessons_checkins', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('bookings_lessons_checkins', 'not_checked_in', TRUE);
        }
}
