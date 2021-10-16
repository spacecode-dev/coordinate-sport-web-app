<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Remove_duplicated_evaluations extends CI_Migration {

		public function __construct() {
			parent::__construct();

			// load db forge
			$this->load->dbforge();
		}

		public function up() {
		    if (ENVIRONMENT == 'production') {
				$this->db->delete('bookings_lessons_notes', [
					'noteID' => 20748,
					'lessonID' => 36579,
					'byID' => 298
				]);

				$this->db->delete('bookings_lessons_notes', [
					'noteID' => 20747,
					'lessonID' => 36584,
					'byID' => 298
				]);

				$this->db->delete('bookings_lessons_notes', [
					'noteID' => 20746,
					'lessonID' => 36585,
					'byID' => 298
				]);
            }
		}

		public function down() {

		}
}
