<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Booking Date',
	'Booking Amount',
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
	'Childcare Voucher',
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
		$childcare_voucher = 'No';

		// dont show amount if using childcare vouchers
		if ($row->childcarevoucher_providerID > 0) {
			$row->amount = '';
			$childcare_voucher = 'Yes';
		}

		// account holders
		$booking_contacts = explode('###', $row->booking_contact_names);
		$booking_contacts = array_filter($booking_contacts);
		sort($booking_contacts);

		// participants
		$participant_names = array_merge(explode('###', $row->children_names), explode('###', $row->contact_names));
		$participant_names = array_filter($participant_names);
		sort($participant_names);

		// payment types
		$payment_types = explode(',', $row->payment_types);
		$types = [];
		if (in_array('0', $payment_types)) {
			$types['external'] = 'External';
		}
		if (in_array('1', $payment_types)) {
			$types['internal'] = 'Internal';
			// get staff
			$staff = explode("###", $row->staff);
			$internal_staff = [];
			foreach ($staff as $name) {
				if ($pos = stripos($name, '@1')) {
					$internal_staff[] = substr($name, 0, $pos);
				}
			}
			if (count($internal_staff) > 0)  {
				sort($internal_staff);
				$types['internal'] .= ' (' . implode(', ', $internal_staff) . ')';
			}
		}

		// payment methods
		$payment_methods = explode('###', $row->payment_methods);
		$payment_methods = array_filter($payment_methods);
		$card = array_search('card', $payment_methods);
		if ($card !== FALSE) {
			$payment_methods[$card] = 'Credit/Debit Card';
		}
		$payment_methods = array_map('ucwords', $payment_methods);
		sort($payment_methods);

		// transaction refs
		$refs = [];
		$transaction_refs = explode(',', $row->transaction_refs);
		foreach ($transaction_refs as $ref) {
			$ref = trim($ref);
			$pos = stripos($ref, '@');
			if ($pos !== FALSE && $pos !== 0) {
				$refs[] = substr($ref, 0, $pos);
			}
		}

		// notes
		$notes = explode(',', $row->notes);
		$notes = array_filter($notes);

		$row = array(
			mysql_to_uk_date($row->booked),
			$row->amount_partial,
			implode(", ", $booking_contacts),
			implode(", ", $participant_names),
			$row->project_names,
			$row->project_codes,
			$row->blocks,
			$row->session_types,
			$row->departments,
			$row->session_count,
			implode(', ', $types),
			implode(', ', $payment_methods),
			$childcare_voucher,
			implode(', ', $refs),
			implode(', ', $notes)
		);

		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'booking-payments-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
