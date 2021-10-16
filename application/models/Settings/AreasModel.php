<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/AreasRepository.php';

class AreasModel extends Settings
{
	public function getList($accountID = null)
	{
		return AreasRepository::getInstance()->getAllList($accountID);
	}

	public function getById($id)
	{
		// TODO: Implement getById() method.
	}
}
