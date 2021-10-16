<?php

require_once APPPATH.'repositories/Repository.php';

class RegionsRepository extends Repository
{
	public function __construct() {
		$this->table = 'settings_regions';
		$this->idField = 'regionID';
		$this->CI = & get_instance();
	}
}
