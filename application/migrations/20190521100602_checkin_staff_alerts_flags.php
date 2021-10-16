<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Checkin_staff_alerts_flags extends CI_Migration {

        public function __construct() {
            parent::__construct();

            // load db forge
            $this->load->dbforge();
        }

        public function up() {
            // define brand activities fields
            $fields = array(
                'checkin_email_sent' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
					'default' => 0,
					'null' => FALSE,
					'after' => 'comment'
                ),
                'checkout_email_sent' => array(
                    'type' => 'TINYINT',
                    'constraint' => 1,
                    'default' => 0,
                    'null' => FALSE,
                    'after' => 'checkin_email_sent'
                )
            );
			$this->dbforge->add_column('bookings_lessons_staff', $fields);
        }

        public function down() {
			// remove columns added above
			$this->dbforge->drop_column('bookings_lessons_staff', 'checkin_email_sent', TRUE);
			$this->dbforge->drop_column('bookings_lessons_staff', 'checkout_email_sent', TRUE);
        }
}
