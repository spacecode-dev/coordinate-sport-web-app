<?php

require_once APPPATH.'repositories/Repository.php';

class LessonTypesRepository extends Repository
{
	public function __construct() {
		$this->table = 'lesson_types';
		$this->idField = 'typeID';
		$this->CI = & get_instance();
	}
}
