<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Enforce_positive_dashboard_triggers extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'positive_only' => array(
                    'type' => 'TINYINT',
					'constraint' => 1,
					'default' => 1,
					'null' => FALSE,
					'after' => 'value_red'
                )
            );
			$this->dbforge->add_column('settings_dashboard', $fields);

			// allow outstanding to have negative
			$where = array(
				'key' => 'families_outstanding'
			);
			$data = array(
				'positive_only' => 0,
				'modified' => mdate('%Y-%m-%d %H:%i:%s')
			);
			$this->db->update('settings_dashboard', $data, $where, 1);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('settings_dashboard', 'positive_only', TRUE);
        }
}
