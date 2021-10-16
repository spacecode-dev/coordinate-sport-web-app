<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_pin_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {

			$fields = array(
			'pin' => array(
				'type' => ' int',
				'default' => NULL,
				'after' => 'medical'
			));

			$this->dbforge->add_column('family_children', $fields);

        }

        public function down() {
            $this->dbforge->drop_column('family_children', 'pin');
        }
}
