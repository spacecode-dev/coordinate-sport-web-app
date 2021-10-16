<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/ProjectCodesRepository.php';

class ProjectCodesModel extends Settings
{
	public function search($strictArray = [], $likeArray = [], $amount = null, $start = null, $order = null)
	{
		return ProjectCodesRepository::getInstance()->searchList($strictArray, $likeArray, $amount, $start, $order);
	}

	public function getList($accountID = null, $active = null)
	{
		return ProjectCodesRepository::getInstance()->getAllList($accountID, $active);
	}

	public function getById($id)
	{
		return ProjectCodesRepository::getInstance()->getById($id);
	}

	public function createNew($data)
	{
		return ProjectCodesRepository::getInstance()->create($data);
	}

	public function update($id, $data, $accountId = null) {
		return ProjectCodesRepository::getInstance()->update($id, $data, $accountId);
	}

}
