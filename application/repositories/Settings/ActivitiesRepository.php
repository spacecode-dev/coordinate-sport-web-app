<?php

require_once APPPATH.'repositories/Repository.php';

class ActivitiesRepository extends Repository
{
	public function __construct() {
		$this->table = 'activities';
		$this->idField = 'activityID';
		$this->CI = & get_instance();
	}
}
