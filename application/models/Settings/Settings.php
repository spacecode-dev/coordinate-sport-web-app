<?php

abstract class Settings extends CI_Model
{
	private $CI;

	public function __construct() {
		// get CI instance
		$this->CI =& get_instance();
	}

	abstract function getList($accountID);

	abstract function getById($id);
}
