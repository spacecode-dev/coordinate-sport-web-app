<?php

require_once APPPATH.'repositories/Repository.php';

class LessonStaffRepository extends Repository
{
	public function __construct() {
		$this->table = 'bookings_lessons_staff';
		$this->idField = 'recordID';
		$this->CI = & get_instance();
	}
}
