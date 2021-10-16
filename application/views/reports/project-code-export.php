<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Project Code',
	'Total Spend',
);

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($total) == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($total as $key => $value) {
		$row = array(
			'code' => $key,
			'total' => number_format($value['total_pay'], 2)
		);

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'project-code-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
