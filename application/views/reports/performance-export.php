<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Staff',
	$this->settings_library->get_label('brand'),
	'Team Leader'
);
foreach ($columns as $key => $label) {
	$headings[] = $label;
}

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($staff_data) == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($staff_data as $staffID => $row) {
		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'performance-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');