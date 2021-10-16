<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/BrandsRepository.php';

class Brands extends Settings
{
	public function getList($accountID = null, $onlyActive = false, $orderBy = 'name asc')
	{
		return BrandsRepository::getInstance()->getAllList($accountID, $onlyActive, $orderBy);
	}

	public function getById($id)
	{
		// TODO: Implement getById() method.
	}

}
