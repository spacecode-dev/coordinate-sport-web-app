<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_notes_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {

			$fields = array(
			'notes' =>array(
				'type' => 'longtext',
				'default' => NULL,
				'after' => 'monitoring20'
			));

			$this->dbforge->add_column('bookings_cart_monitoring', $fields);

        }

        public function down() {
            $this->dbforge->drop_column('bookings_cart_monitoring', 'notes');
        }
}
