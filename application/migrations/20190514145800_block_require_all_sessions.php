<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Block_require_all_sessions extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'require_all_sessions' => array(
                    'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'provisional'
                )
            );
			$this->dbforge->add_column('bookings_blocks', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('bookings_blocks', 'require_all_sessions', TRUE);
        }
}
