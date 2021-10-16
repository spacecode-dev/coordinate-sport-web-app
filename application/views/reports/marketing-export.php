<?php
// build data
$csv_data = array();
$headings = [
	'Name',
	'Marketing Consent',
	'Privacy Policy',
	'Newsletters',
	'Referral Data',
	'Email',
	'Mobile',
];

// add to csv
$csv_data[] = $headings;

// check if any data
if ($row_data->result() == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($row_data->result() as $row) {
		$hours = 0;
		$row = [
			'name' => $row->first_name . ' ' . $row->last_name,
			'marketing_consent' => ($row->marketing_consent == 1? 'Yes' : 'No')
				. ($row->marketing_consent_date ? ' ' .  (new DateTime($row->marketing_consent_date))->format('m/d/Y H:i:s') : ''),
			'privacy_agreed' => ($row->privacy_agreed == 1? 'Yes' : 'No')
				. ($row->privacy_agreed_date ? ' ' . (new DateTime($row->privacy_agreed_date))->format('m/d/Y H:i:s') : ''),
			'newsletters' => $row->newsletters ?: '-',
			'source' => $row->source == 'Other' ? ($row->source_other ? : '-') : ($row->source ? : '-'),
			'email' => $row->marketing_consent == 1 ? ($row->email ?: '-') : '-',
			'mobile' => $row->marketing_consent == 1 ? ($row->mobile ?: '-') : '-',
		];

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'marketing-' . (new DateTime())->format("Y-m-d_G:i:s") . '.csv');
