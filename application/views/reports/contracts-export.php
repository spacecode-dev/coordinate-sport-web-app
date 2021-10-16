<?php
/**
 * @var $payroll_data CI_DB_result
 * @var $staff CI_DB_result
 * @var $staff_list CI_DB_result
 * @var $payroll_data array
 * @var $search_fields array
 */

// build data
$csv_data = array();

if ($search_fields['show_blocks']) {
	// set headings
	$headings = array(
		'Customer',
		'Project\'s Name',
		'Blocks',
		'Start Date',
		'End Date',
		'Staff',
		'Department',
		'Session Types',
		'Activities',
		'Total number of hours delivered for the customer',
		'Total number of hours delivered in the Contract or Project',
		'Total number of Hours delivered in the Block',
		'Total Contract Revenue',
		'Total Costs',
		'Profit/Loss'
	);

// add to csv
	$csv_data[] = $headings;

// check if any data
	if ($contracts->num_rows() == 0) {
		$csv_data[] = array(
			'No data'
		);
	} else {
		foreach ($contracts->result() as $row) {
			foreach ($booking_data[$row->bookingID] as $block_id => $block_data) {
				if (empty($booking_data[$row->bookingID][$block_id]['org_name'])) {
					$org = $row->org;
				} else {
					$org = $booking_data[$row->bookingID][$block_id]['org_name'];
				}

				$start_date = '';
				if (!empty($booking_data[$row->bookingID][$block_id]['start_date'])) {
					$start_date = date('d/m/Y', strtotime($booking_data[$row->bookingID][$block_id]['start_date']));
				}

				$end_date = '';
				if (!empty($booking_data[$row->bookingID][$block_id]['end_date'])) {
					$end_date = date('d/m/Y', strtotime($booking_data[$row->bookingID][$block_id]['end_date']));
				}

				$types = '';
				if (!empty($booking_data[$row->bookingID][$block_id]['type_other'])) {
					$types = $booking_data[$row->bookingID][$block_id]['type_other'];
				} else if (!empty($booking_data[$row->bookingID][$block_id]['session_types'])) {
					$types = $booking_data[$row->bookingID][$block_id]['session_types'];
				}

				$activities = '';
				if (!empty($booking_data[$row->bookingID][$block_id]['activities_other'])) {
					$activities = $booking_data[$row->bookingID][$block_id]['activities_other'];
				} else if (!empty($booking_data[$row->bookingID][$block_id]['activities'])) {
					$activities = $booking_data[$row->bookingID][$block_id]['activities'];
				}

				$customer_hours_csv = 0;
				if (isset($customer_hours[$booking_data[$row->bookingID][$block_id]['block_org_id']])) {
					$customer_hours_csv = number_format($customer_hours[$booking_data[$row->bookingID][$block_id]['block_org_id']], 2);
				} else if (isset($customer_hours[$row->orgID])) {
					$customer_hours_csv = number_format($customer_hours[$row->orgID], 2);
				}

				$block_hours_csv = 0;
				if (isset($costs[$row->bookingID]['block_hours'][$block_id])) {
					$block_hours_csv = number_format($costs[$row->bookingID]['block_hours'][$block_id], 2);
				}

				$hours = 0;
				if (isset($costs[$row->bookingID]['hours'])) {
					$hours = number_format($costs[$row->bookingID]['hours'], 2);
				}


				$activities_csv = '';

				$costs_csv = 0;
				$income_csv = 0;
				$profit = 0;

				if (isset($income[$row->bookingID]['total'])) {
					$income_csv = number_format($income[$row->bookingID]['total'], 2);
				}

				if (isset($costs[$row->bookingID]['total'])) {
					$costs_csv = number_format($costs[$row->bookingID]['total'], 2);
				}

				if (isset($total_profit[$row->bookingID])) {
					$profit = number_format($total_profit[$row->bookingID], 2);
				}

				$staff = '';
				if (isset($booking_data[$row->bookingID][$block_id]['staff'])) {
					$staff = $booking_data[$row->bookingID][$block_id]['staff'];
				}

				$csv = [
					$org,
					$row->name,
					$booking_data[$row->bookingID][$block_id]['block_name'],
					$start_date,
					$end_date,
					$staff,
					$row->department,
					$types,
					$activities,
					$customer_hours_csv,
					$hours,
					$block_hours_csv,
					$income_csv,
					$costs_csv,
					$profit
				];

				// add to csv
				$csv_data[] = $csv;
			}

		}
	}

	array_to_csv($csv_data, 'contracts-' . $search_fields['date_from'] . '-to-' . $search_fields['date_to'] . '.csv');

} else {
	// set headings
	$headings = array(
		'Customer',
		'Project\'s Name',
		'Start Date',
		'End Date',
		'Staff',
		'Department',
		'Session Types',
		'Activities',
		'Total number of hours delivered for the customer',
		'Total number of hours delivered in the Contract or Project',
		'Total Contract Revenue',
		'Total Costs',
		'Profit/Loss'
	);

// add to csv
	$csv_data[] = $headings;

// check if any data
	if ($contracts->num_rows() == 0) {
		$csv_data[] = array(
			'No data'
		);
	} else {
		foreach ($contracts->result() as $row) {
			$types = '';
			if (!empty($booking_data[$row->bookingID]['type_other'])) {
				$types = $booking_data[$row->bookingID]['type_other'];
			} else if (!empty($booking_data[$row->bookingID]['session_types'])) {
				$types = $booking_data[$row->bookingID]['session_types'];
			}

			$activities = '';
			if (!empty($booking_data[$row->bookingID]['activities_other'])) {
				$activities = $booking_data[$row->bookingID]['activities_other'];
			} else if (!empty($booking_data[$row->bookingID]['activities'])) {
				$activities = $booking_data[$row->bookingID]['activities'];
			}

			$customer_hours_csv = 0;
			if (isset($customer_hours[$row->orgID])) {
				$customer_hours_csv = number_format($customer_hours[$row->orgID], 2);
			}

			$hours = 0;
			if (isset($costs[$row->bookingID]['hours'])) {
				$hours = number_format($costs[$row->bookingID]['hours'], 2);
			}


			$activities_csv = '';

			$costs_csv = 0;
			$income_csv = 0;
			$profit = 0;

			if (isset($income[$row->bookingID]['total'])) {
				$income_csv = number_format($income[$row->bookingID]['total'], 2);
			}

			if (isset($costs[$row->bookingID]['total'])) {
				$costs_csv = number_format($costs[$row->bookingID]['total'], 2);
			}

			if (isset($total_profit[$row->bookingID])) {
				$profit = number_format($total_profit[$row->bookingID], 2);
			}

			$staff = '';
			if (isset($booking_data[$row->bookingID]['staff'])) {
				$staff = $booking_data[$row->bookingID]['staff'];
			}

			$csv = [
				$row->org,
				$row->name,
				$row->startDate,
				$row->startDate,
				$staff,
				$row->department,
				$types,
				$activities,
				$customer_hours_csv,
				$hours,
				$income_csv,
				$costs_csv,
				$profit
			];

			// add to csv
			$csv_data[] = $csv;
		}
	}

	array_to_csv($csv_data, 'contracts-' . $search_fields['date_from'] . '-to-' . $search_fields['date_to'] . '.csv');

}

