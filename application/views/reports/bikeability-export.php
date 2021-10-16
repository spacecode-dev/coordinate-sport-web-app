<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Instructor',
	'Date',
	'Start Time',
	'End Time',
	'Session Type',
	'Trainee Name',
	'Trainee Email',
	'Trainee Postcode',
	//'Level at Start of Session',
	'Level at End of Session',
	//'Session Outcome',
	'Gender',
	'Age'
);

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($report_data) == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($report_data as $item) {
		$row = array(
			'instructor' => $item['instructor'],
			'date' => $item['date'],
			'startTime' => $item['startTime'],
			'endTime' => $item['endTime'],
			'session_type' => $item['session_type'],
			'trainee_name' => $item['trainee_name'],
			'trainee_email' => $item['trainee_email'],
			'trainee_postcode' => $item['trainee_postcode'],
			//'level_at_start' => $item['level_at_start'],
			'level_at_end' => $item['level_at_end'],
			//'lesson_outcome' => $item['lesson_outcome'],
			'gender' => $item['gender'],
			'age' => $item['age']
		);

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'bikeability-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');