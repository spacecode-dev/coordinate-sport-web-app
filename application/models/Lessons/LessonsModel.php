<?php

require_once APPPATH.'repositories/Lessons/LessonsRepository.php';

class LessonsModel extends CI_Model
{
	public function getListWithDetails($strictArray, $likeArray, $customQuery)
	{
		$lessons = LessonsRepository::getInstance()->getDetailedList(
			$strictArray, $likeArray, $customQuery
		);

		return $lessons;
	}
}
