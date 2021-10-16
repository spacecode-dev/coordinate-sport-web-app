<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Staff'
);
if (count($activities) > 0) {
	foreach ($activities as $label) {
		$headings[] = $label;
	}
}
$headings[] = 'Total';

// add to csv
$csv_data[] = $headings;

// check if any data
if ($row_data->result() == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($row_data->result() as $item) {
		$hours = 0;
		$row = array(
			'name' => $item->first . ' ' . $item->surname
		);
		if (count($activities) > 0) {
			foreach ($activities as $activityID => $label) {
				$activity_hours = 0;
				if (isset($report_data[$item->staffID][$activityID])) {
					$activity_hours = $report_data[$item->staffID][$activityID];
				}
				$hours += $activity_hours;
				$row[$activityID] = $activity_hours;
			}
		}
		$row['hours'] = $hours;

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'activities-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');