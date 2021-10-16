<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Subsection_settings extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'subsection' => array(
                    'type' => 'TEXT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'section'
                )
            );
			$this->dbforge->add_column('settings', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('settings', 'subsection', TRUE);
        }
}
