<?php

require_once APPPATH.'models/Settings/Settings.php';
require_once APPPATH.'repositories/Settings/LessonTypesRepository.php';

class LessonTypesModel extends Settings
{
	public function getList($accountID = null, $active = null)
	{
		return LessonTypesRepository::getInstance()->getAllList($accountID, $active);
	}

	public function getById($id)
	{
		// TODO: Implement getById() method.
	}
}
