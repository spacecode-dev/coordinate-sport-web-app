<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * check a date is valid
 * @param  string $date
 * @return bool
 */
function check_uk_date($date) {
	// check if parameters
	if (empty($date)) {
		return FALSE;
	}

	// seperate day, month and year
	$date_parts = explode('/', $date);

	// remove empty parts
	$date_parts = array_filter($date_parts);

	// should be 3 parts
	if (count($date_parts) != 3) {
		return FALSE;
	}

	// check if correct
	if (!checkdate($date_parts[1], $date_parts[0], $date_parts[2]) === TRUE) {
		return FALSE;
	}

	return TRUE;
}

/**
 * check a mysql date is valid
 * @param  string $date
 * @return bool
 */
function check_mysql_date($date) {
	// check if parameters
	if (empty($date)) {
		return FALSE;
	}

	// seperate day, month and year
	$date_parts = explode('-', $date);

	// remove empty parts
	$date_parts = array_filter($date_parts);

	// should be 3 parts
	if (count($date_parts) != 3) {
		return FALSE;
	}

	// check if correct
	if (!checkdate((int)$date_parts[1], (int)$date_parts[2], (int)$date_parts[0]) === TRUE) {
		return FALSE;
	}

	return TRUE;
}

/**
 * convert a uk date to a mysql date
 * @param  string $date
 * @return string
 */
function uk_to_mysql_date($date) {
	if (empty($date)) {
		return FALSE;
	}

	// check valid
	if (!check_uk_date($date)) {
		return FALSE;
	}

	// seperate day, month and year
	$date_parts = explode('/', $date);

	return $date_parts[2] . '-' . $date_parts[1] . '-' . $date_parts[0];
}

/**
 * convert a db date to uk format
 * @param  string  $date
 * @param  boolean $time
 * @return string
 */
function mysql_to_uk_date($date, $time = FALSE) {

	if (empty($date)) {
		return NULL;
	}

	if (date("I") == 1) {
		$offset = 0; // should be 3600?
	} else {
		$offset = 0;
	}

	$format = 'd/m/Y';

	if ($time === TRUE) {
		$format .= ' H:i';
	}

	return date($format, strtotime($date) + $offset);
}

/**
 * convert a db date/time to uk format
 * @param  string $date
 * @return string
 */
function mysql_to_uk_datetime($date) {
	return mysql_to_uk_date($date, TRUE);
}

/**
 * calculate age from mysql date
 * @param  string $date
 * @return mixed
 */
function calculate_age($date, $at = 'today') {

	// check params
	if (!check_mysql_date($date) || empty($at)) {
		return FALSE;
	}

	// create objects
	$from = new DateTime($date);
	$to = new DateTime($at);

	// get difference
	return intval($from->diff($to)->y);
}

/**
 * format decimal hours as xxhxxm
 * @param  integer $decimal_hours
 * @return string
 */
function format_decimal_hours($decimal_hours = 0) {
	$negative = FALSE;
	if ($decimal_hours < 0) {
		$decimal_hours = $decimal_hours*-1;
		$negative = TRUE;
	}
	$hours = floor($decimal_hours);
	$minutes = round(60*($decimal_hours-$hours));
	$return = $hours . 'h';
	if ($minutes > 0) {
		$return .= $minutes . 'm';
	}
	if ($negative) {
			return '-' . $return;
	} else {
		return $return;
	}
}

/**
 * convert seconds to time format
 * @param  integer $time
 * @return string
 */
function seconds_to_time($time) {
	return sprintf("%02d%s%02d%s%02d", floor($time/3600), ':', ($time/60)%60, ':', $time%60);
}

/**
 * convert time format to seconds
 * @param  string $time
 * @return integer
 */
function time_to_seconds($time) {
	return strtotime("1970-01-01 $time UTC");
}

/**
 * get start date of week from week and year
 * @param integer $week
 * @param integer $year
 * @return integer
 */
function get_date_from_week($week, $year) {
	$dto = new DateTime();
	$dto->setISODate($year, $week);
	return $dto->format('Y-m-d');
}

/**
 * get number of weeks between 2 dates
 * @param string $date1
 * @param string $date2
 * @return integer
 */
function weeks_between_two_dates($date1, $date2) {
	// calc diff
	$diff = date_diff(new DateTime($date1), new DateTime($date2));

	// get weeks
	$weeks = floor($diff->days/7);

	// if result negative, return as negative
	if ($diff->invert == 1) {
		$weeks = $weeks*-1;
	}
	return $weeks;
}
