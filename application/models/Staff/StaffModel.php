<?php
require_once APPPATH.'repositories/Staff/StaffRepository.php';

class StaffModel
{
	public function getList($accountID = null, $active = null)
	{
		return StaffRepository::getInstance()->getAllList($accountID, $active);
	}

	public function search($strictArray = [], $likeArray = [], $amount = null, $start = null, $order = null)
	{
		return StaffRepository::getInstance()->searchList($strictArray, $likeArray, $amount, $start, $order);
	}

	public function getJobTitles($accountID = null) {
		return StaffRepository::getInstance()->getField('jobTitle', $accountID);
	}
}
