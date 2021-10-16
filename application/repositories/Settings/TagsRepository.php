<?php

require_once APPPATH.'repositories/Repository.php';

class TagsRepository extends Repository
{
	public function __construct() {
		$this->table = 'settings_tags';
		$this->idField = 'tagID';
		$this->CI = & get_instance();
	}
}
