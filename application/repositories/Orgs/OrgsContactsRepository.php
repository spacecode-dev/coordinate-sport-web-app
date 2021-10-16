<?php

require_once APPPATH.'repositories/Repository.php';

class OrgsContactsRepository extends Repository
{
	public function __construct() {
		$this->table = 'orgs_contacts';
		$this->idField = 'contactID';
		$this->CI = & get_instance();
	}
}
