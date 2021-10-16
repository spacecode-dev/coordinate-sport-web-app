<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/TagsRepository.php';

class TagsModel extends Settings
{
	public function getList($accountID = null, $active = null, $orderBy = 'name asc')
	{
		return TagsRepository::getInstance()->getAllList($accountID, $active, $orderBy);
	}

	public function searchListByName($accountID = null, $name = null, $amount = 20, $start = 0, $order = 'tagID desc') {
		return TagsRepository::getInstance()->searchList([
			'accountID' => $accountID
		], !is_null($name) ? [
			'name' => $name
		] : [], $amount, $start, $order);
	}


	public function getById($id)
	{
		// TODO: Implement getById() method.
	}

}
