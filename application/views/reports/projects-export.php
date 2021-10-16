<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Type',
	'Contracted',
	'Sessional',
	'Total'
);

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
			'name' => $item->name
		);

		// contracted
		$booking_hours = 0;
		if (isset($report_data[$item->id]['contracted'])) {
			$booking_hours = $report_data[$item->id]['contracted'];
		}
		$hours += $booking_hours;
		$row['contracted'] = $booking_hours;

		// sessional
		$booking_hours = 0;
		if (isset($report_data[$item->id]['sessional'])) {
			$booking_hours = $report_data[$item->id]['sessional'];
		}
		$hours += $booking_hours;
		$row['sessional'] = $booking_hours;

		// total
		$row['hours'] = $hours;

		// add to csv
		$csv_data[] = $row;
	}

	// other
	if (in_array($search_fields['type'], array('activity-type', 'session-type'))) {
		$hours = 0;
		$row = array(
			'name' => 'Other'
		);

		// contracted
		$booking_hours = 0;
		if (isset($report_data['other']['contracted'])) {
			$booking_hours = $report_data['other']['contracted'];
		}
		$hours += $booking_hours;
		$row['contracted'] = $booking_hours;

		// sessional
		$booking_hours = 0;
		if (isset($report_data['other']['sessional'])) {
			$booking_hours = $report_data['other']['sessional'];
		}
		$hours += $booking_hours;
		$row['sessional'] = $booking_hours;

		// total
		$row['hours'] = $hours;

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'projectdelivery-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');