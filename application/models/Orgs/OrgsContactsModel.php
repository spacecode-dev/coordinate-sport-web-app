<?php
require_once APPPATH.'repositories/Orgs/OrgsContactsRepository.php';

class OrgsContactsModel
{
	public function getMainList($accountID = null)
	{
		return OrgsContactsRepository::getInstance()->searchList([
			'accountID' => $accountID,
			'isMain' => 1
		]);
	}
}
