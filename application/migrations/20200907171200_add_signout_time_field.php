<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_signout_time_field extends CI_Migration {

        public function __construct() {
            parent::__construct();

		    // load db forge
		    $this->load->dbforge();
        }

        public function up() {
           
			$fields = array(
			'signout' => array(
				'type' => ' tinyint(1)',
				'default' => NULL,
				'after' => 'attended'
			),
			'attend_time' => array(
				'type' => 'datetime',
				'default' => NULL,
				'after' => 'attended'
			),
			'signout_time' =>array(
				'type' => 'datetime',
				'default' => NULL,
				'after' => 'signout'
			));
			
			$this->dbforge->add_column('bookings_cart_sessions', $fields);
			
        }

        public function down() {
            $this->dbforge->drop_column('bookings_cart_sessions', 'signout');
            $this->dbforge->drop_column('bookings_cart_sessions', 'attend_time');
            $this->dbforge->drop_column('bookings_cart_sessions', 'signout_time');
        }
}
