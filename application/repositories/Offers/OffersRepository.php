<?php

require_once APPPATH.'repositories/Repository.php';

class OffersRepository extends Repository
{
	public function __construct() {
		$this->table = 'offer_accept';
		$this->idField = 'offerID';
		$this->CI = & get_instance();
	}
}
