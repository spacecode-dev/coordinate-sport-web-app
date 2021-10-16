<?php
require_once APPPATH.'repositories/Lessons/LessonStaffRepository.php';

class LessonsStaffModel
{
	public function getListByDates($dateFrom, $dateTo, $accountId) {
		return LessonStaffRepository::getInstance()->searchList([
			'accountID' => $accountId,
			'startDate <=' => $dateTo,
			'endDate >=' => $dateFrom,
		]);
	}
}
