<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Staff'
);
if (count($bookings) > 0) {
	foreach ($bookings as $booking) {
		$headings[] = $booking['label'];
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
		if (count($bookings) > 0) {
			foreach ($bookings as $bookingID => $label) {
				$booking_hours = 0;
				if (isset($report_data[$item->staffID][$bookingID])) {
					$booking_hours = $report_data[$item->staffID][$bookingID];
				}
				$hours += $booking_hours;
				$row[$bookingID] = $booking_hours;
			}
		}
		$row['hours'] = $hours;

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'projectdelivery-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
