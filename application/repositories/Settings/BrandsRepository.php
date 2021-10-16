<?php

require_once APPPATH.'repositories/Repository.php';

class BrandsRepository extends Repository
{
	public function __construct() {
		$this->table = 'brands';
		$this->idField = 'brandID';
		$this->CI = & get_instance();
	}
}
