<?php
require_once APPPATH.'repositories/LessonExceptionsRepository.php';

class LessonsExceptionsModel
{
	public function getStaffChangeExceptionsByStaff($staffID)
	{
		return LessonExceptionsRepository::getInstance()->searchList([
			'staffID' => $staffID,
			'type' => 'staffchange'
		]);
	}

	public function getListByDates($dateFrom, $dateTo, $accountId) {
		return LessonExceptionsRepository::getInstance()->searchList([
			'accountID' => $accountId,
			'date >=' => $dateFrom,
			'date <=' => $dateTo,
		]);
	}
}
