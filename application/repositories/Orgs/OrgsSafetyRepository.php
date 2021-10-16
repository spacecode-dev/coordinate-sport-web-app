<?php

require_once APPPATH.'repositories/Repository.php';

class OrgsSafetyRepository extends Repository
{
	public function __construct() {
		$this->table = 'orgs_safety';
		$this->idField = 'docID';
		$this->CI = & get_instance();
	}
}
