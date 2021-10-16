<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Project_type_exclusions extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand activities fields
            $fields = array(
                'exclude_from_participant_booking_lists' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'name'
                )
            );
			$this->dbforge->add_column('project_types', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('project_types', 'exclude_from_participant_booking_lists', TRUE);
        }
}
