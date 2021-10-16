<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_staff_exception_id extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
			// Add Field in bookings_lessons_exceptions
			$fields = array(
				'staff_exceptionID' => array(
					'type' => 'INT',
					'constraint' => 11,
					'default' => NULL,
					'after' => 'lessonID'
				)
			);
			$this->dbforge->add_column('bookings_lessons_exceptions', $fields);
			$this->dbforge->add_key('staff_exceptionID');
			// set foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions') . '` ADD CONSTRAINT `fk_bookings_lessons_exceptions_staff_exceptionID` FOREIGN KEY (`staff_exceptionID`) REFERENCES `' . $this->db->dbprefix('staff_availability_exceptions') . '`(`exceptionsID`) ON DELETE CASCADE ON UPDATE CASCADE');
			
			
		}

		public function down() {
			// remove foreign keys
			$this->db->query('ALTER TABLE `' . $this->db->dbprefix('bookings_lessons_exceptions') . '` DROP FOREIGN KEY `fk_bookings_lessons_exceptions_staff_exceptionID`');
			
			$this->dbforge->drop_column('bookings_lessons_exceptions', 'staff_exceptionID');
		}
}
