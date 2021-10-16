<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_sendwithconfirmation_block_attachments extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {

            // rename field
            $fields = array(
                'sendwithconfirmation' => array(
                    'name' => 'showonbookingssite',
                    'type' => 'TINYINT',
					'constraint' => 1,
                    'null' => FALSE,
					'default' => 0
                )
            );
            $this->dbforge->modify_column('bookings_attachments', $fields);
        }

        public function down() {
			// rename field
            $fields = array(
                'showonbookingssite' => array(
                    'name' => 'sendwithconfirmation',
                    'type' => 'TINYINT',
					'constraint' => 1,
                    'null' => FALSE,
					'default' => 0
                )
            );
            $this->dbforge->modify_column('bookings_attachments', $fields);
        }
}
