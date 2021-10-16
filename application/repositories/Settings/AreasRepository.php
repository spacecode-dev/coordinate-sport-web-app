<?php

require_once APPPATH.'repositories/Repository.php';

class AreasRepository extends Repository
{
	public function __construct() {
		$this->table = 'settings_areas';
		$this->idField = 'areaID';
		$this->CI = & get_instance();
	}
}
