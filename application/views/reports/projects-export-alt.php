<?php
// build data
$csv_data = array();

// get staff
$staff = array();
foreach ($row_data->result() as $row) {
	$staff[$row->staffID] = array(
		'name' => $row->first . ' ' . $row->surname,
		'salaried' => $row->payments_scale_salaried
	);
}

// set headings
$headings = array(
	'Project Name',
	'Project Code',
	'Activity',
	'Session Type',
);
foreach ($staff as $staffID => $details) {
	$headings[] = $details['name'];
}
$headings[] = 'Contracted';
$headings[] = 'Sessional';
$headings[] = 'Total';

// add to csv
$csv_data[] = $headings;

// next row
$headings = array(
	'',
	'',
	'',
);
foreach ($staff as $staffID => $details) {
	if ($details['salaried'] == 1) {
		$headings[] = 'Contracted';
	} else {
		$headings[] = 'Sessional';
	}
}

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($bookings) == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($bookings as $bookingID => $booking) {
		if (isset($report_data[$bookingID])) {
			foreach ($report_data[$bookingID] as $activity => $types) {
				foreach ($types as $type => $staffIDs) {
					$contracted = 0;
					$sessional = 0;
					$hours = 0;
					$row = array(
						$booking['label'],
						$booking['code'],
						$activity,
						$type
					);
					foreach ($staff as $staffID => $details) {
						$booking_hours = 0;
						if (isset($staffIDs[$staffID])) {
							$booking_hours = $staffIDs[$staffID];
						}
						$hours += $booking_hours;
						if ($details['salaried'] == 1) {
							$contracted += $booking_hours;
						} else {
							$sessional += $booking_hours;
						}
						$row[] = $booking_hours;
					}
					$row['contracted'] = $contracted;
					$row['sessional'] = $sessional;
					$row['hours'] = $hours;

					// add to csv
					$csv_data[] = $row;
				}
			}
		}
	}
}

array_to_csv($csv_data, 'projectdelivery-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
