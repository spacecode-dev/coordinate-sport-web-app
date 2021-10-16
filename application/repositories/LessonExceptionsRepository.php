<?php

require_once APPPATH.'repositories/Repository.php';

class LessonExceptionsRepository extends Repository
{
	public function __construct() {
		$this->table = 'bookings_lessons_exceptions';
		$this->idField = 'exceptionID';
		$this->CI = & get_instance();
	}
}
