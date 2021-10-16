<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/RegionsRepository.php';

class RegionsModel extends Settings
{
	public function getList($accountID = null)
	{
		return RegionsRepository::getInstance()->getAllList($accountID);
	}

	public function getById($id)
	{
		// TODO: Implement getById() method.
	}
}
