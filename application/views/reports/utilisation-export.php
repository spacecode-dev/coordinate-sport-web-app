<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Staff'
);
if ($brands->num_rows() > 0) {
	foreach ($brands->result() as $brand) {
		$headings[] = $brand->name;
	}
}
$headings[] = 'Total';
$headings[] = 'Of Which Provisional';
$headings[] = 'Salaried Hours';
$headings[] = 'Utilisation Target';
$headings[] = 'Actual Utilisation';

// add to csv
$csv_data[] = $headings;

// check if any data
if ($staff->result() == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($staff->result() as $item) {
		$hours = 0;
		$row = array(
			'name' => $item->first . ' ' . $item->surname
		);
		if ($brands->num_rows() > 0) {
			foreach ($brands->result() as $brand) {
				$brand_hours = 0;
				if (isset($utilisation_data[$item->staffID][$brand->brandID])) {
					$brand_hours = $utilisation_data[$item->staffID][$brand->brandID];
				}
				$hours += $brand_hours;
				$row[$brand->brandID] = $brand_hours;
			}
		}
		$item->target_hours = ($item->target_hours/7)*$days;
		$row['hours'] = $hours;
		$provisional_hours = 0;
		if (isset($utilisation_data[$item->staffID]['provisional'])) {
			$provisional_hours = $utilisation_data[$item->staffID]['provisional'];
		}
		$row['provisional_hours'] = $provisional_hours;
		$row['target_hours'] = $item->target_hours;
		$row['target_utilisation'] = $item->target_utilisation . '%';
		$row['utilisation'] = (($hours/$item->target_hours)*100) . '%';

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'utilisation-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');