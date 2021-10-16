<?php

require_once APPPATH.'repositories/Repository.php';

class ProjectCodesRepository extends Repository
{
	public function __construct() {
		$this->table = 'project_codes';
		$this->idField = 'codeID';
		$this->CI = & get_instance();
	}
}
