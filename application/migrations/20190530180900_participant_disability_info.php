<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Participant_disability_info extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand activities fields
            $fields = array(
                'disability_info' => array(
                    'type' => 'TEXT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'medical'
                )
            );
			$this->dbforge->add_column('family_children', $fields);
			$this->dbforge->add_column('family_contacts', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('family_contacts', 'disability_info', TRUE);
			$this->dbforge->drop_column('family_children', 'disability_info', TRUE);
        }
}
