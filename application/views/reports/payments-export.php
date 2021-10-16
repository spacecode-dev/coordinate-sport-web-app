<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Transaction Date',
	'Transaction Amount',
	'Applicable Amount',
	'Account Holder',
	'Participant Names',
	'Project Names',
	'Project Codes',
	'Blocks',
	'Session Types',
	'Departments',
	'Session Count',
	'Payment Type',
	'Payment Method',
	'Transaction Reference',
	'Note'
);

// add to csv
$csv_data[] = $headings;

// check if any data
if ($payments->num_rows() == 0) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($payments->result() as $row) {
		$participant_names = array_merge(explode('###', $row->children_names), explode('###', $row->contact_names));
		$participant_names = array_filter($participant_names);
		sort($participant_names);

		if ($search_fields['by_project'] == 1) {
			$row->amount = '-';
		}

		$account_holder = $row->payment_contact;
		if ($row->internal == 1) {
			$booking_contacts = explode('###', $row->booking_contact_names);
			$booking_contacts = array_filter($booking_contacts);
			sort($booking_contacts);
			$account_holder = implode(", ", $booking_contacts);
		}

		$payment_type = 'External';
		if ($row->internal == 1) {
			$payment_type = 'Internal';
			if (!empty($row->staff_first_name) && !empty($row->staff_last_name)) {
				$payment_type .= ' (' . $row->staff_first_name . ' ' . $row->staff_last_name . ')';
			}
		}

		switch ($row->method) {
			case 'card':
				$method = 'Credit/Debit Card';
				break;
			default:
				$method = ucwords($row->method);
				break;
		}
		$amount_partial = $row->amount_partial;
		if($row->is_sub == "0"){
			$amount_partial = '-';
		}

		$row = array(
			mysql_to_uk_date($row->added),
			$row->amount,
			$amount_partial,
			$account_holder,
			implode(", ", $participant_names),
			$row->project_names,
			$row->project_codes,
			$row->blocks,
			$row->session_types,
			$row->departments,
			$row->session_count,
			$payment_type,
			$method,
			$row->transaction_ref,
			$row->note
		);

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, strtolower($this->settings_library->get_label('participant')) . '-billing-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
