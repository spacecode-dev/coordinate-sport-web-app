<?php

require_once APPPATH.'repositories/Repository.php';

class StaffRepository extends Repository
{
	public function __construct() {
		$this->table = 'staff';
		$this->idField = 'staffID';
		$this->CI = & get_instance();
	}
}
