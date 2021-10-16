<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'School',
	'Date(s)',
	'Block',
	//'Level Taught',
	'Girls Participating',
	'Girls SEND',
	'Girls Achieved Level 1',
	'Girls Achieved Level 2',
	'Girls Achieved Level 3',
	'Boys Participating',
	'Boys SEND',
	'Boys Achieved Level 1',
	'Boys Achieved Level 2',
	'Boys Achieved Level 3'
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
			'school' => $item['school'],
			'dates' => $item['dates'],
			'block' => $item['block'],
			//'level_taught' => $item['level_taught'],
			'girls_participating' => $item['girls_participating'],
			'girls_send' => $item['girls_send'],
			'girls_achieved_l1' => $item['girls_achieved_l1'],
			'girls_achieved_l2' => $item['girls_achieved_l2'],
			'girls_achieved_l3' => $item['girls_achieved_l3'],
			'boys_participating' => $item['boys_participating'],
			'boys_send' => $item['boys_send'],
			'boys_achieved_l1' => $item['boys_achieved_l1'],
			'boys_achieved_l2' => $item['boys_achieved_l2'],
			'boys_achieved_l3' => $item['boys_achieved_l3'],
		);

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'bikeability-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');