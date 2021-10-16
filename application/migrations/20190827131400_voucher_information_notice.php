<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Voucher_information_notice extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define fields
            $fields = array(
                'information' => array(
                    'type' => 'TEXT',
					'default' => NULL,
					'null' => TRUE,
					'after' => 'comment'
                )
            );
			$this->dbforge->add_column('settings_childcarevoucherproviders', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('settings_childcarevoucherproviders', 'information', TRUE);
        }
}
