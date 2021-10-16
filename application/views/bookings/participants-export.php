<?php
// shape up vars
$target = 0.05;
$lbs = 2.20462;

// build data
$csv_data = array();

// set headings
$headings = array(
	'Name',
	'DOB',
	'Age'
);
if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
	$headings[] = 'Contact';
	$headings[] = 'Relationship';
}
$headings[] = 'Address 1';
$headings[] = 'Address 2';
$headings[] = 'Address 3';
$headings[] = 'Town';
$headings[] = localise('county');
$headings[] = 'Post Code';
$headings[] = 'Mobile';
$headings[] = 'Other Phone';
$headings[] = 'Work Phone';
$headings[] = 'Email';
if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
	$headings[] = 'Photo Consent?';
}
$headings[] = 'Medical';
for ($i=1; $i <= 20; $i++) {
	$field = 'monitoring' . $i;
	if (!empty($booking_info->$field)) {
		$headings[] = $booking_info->$field;
	}
}

if ($booking_info->type == 'booking') {
	foreach ($lesson_ids as $date=>$lessons) {
		foreach ($lessons as $lessonID=>$name) {
			// if cancelled
			if (isset($cancellations[$lessonID][$date])) {
				// skip
			} else {
				$headings[] = mysql_to_uk_date($date) . ' ' . str_replace('&#8203;', '', $name);
				if (substr($booking_info->register_type, -12) == '_bikeability') {
					$headings[] = 'Level';
				} else if (substr($booking_info->register_type, -8) == '_shapeup') {
					$headings[] = 'Weight';
				}
			}
		}
	}
} else {
	foreach ($lesson_ids as $day=>$lessons) {
		foreach ($lessons as $lessonID=>$name) {
			if ($booking_info->type == 'booking') {
				$date = $block_info->startDate;
				while (strtotime($date) <= strtotime($block_info->endDate)) {
					if (strtolower(date("l", strtotime($date))) == $day) {
						// if cancelled
						if (isset($cancellations[$lessonID][$date])) {
							// skip
						} else {
							if (in_array($day, $days)) {
								if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
									$headings[] = mysql_to_uk_date($date) . ' ' . str_replace('&#8203;', '', $name);
								}
							}
							foreach ($days as $day) {
								$headings[] = mysql_to_uk_date($date) . ' ' . str_replace('&#8203;', '', $name);
							}
							if (substr($booking_info->register_type, -12) == '_bikeability') {
								$headings[] = 'Level';
							} else if (substr($booking_info->register_type, -8) == '_shapeup') {
								$headings[] = 'Weight';
							}
						}
					}
					$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
				}
			} else {
				$headings[] = ucwords($lesson_days[$lessonID]) . ' ' . str_replace('&#8203;', '', $name);
				if (substr($booking_info->register_type, -12) == '_bikeability') {
					$headings[] = 'Level';
				}
				if (substr($booking_info->register_type, -8) == '_shapeup') {
					$headings[] = 'Weight';
				}
			}
		}
	}
}

if (substr($booking_info->register_type, -12) == '_bikeability') {
	$headings[] = 'Overall Level';
}
if (substr($booking_info->register_type, -8) == '_shapeup') {
	$headings[] = '5% Weight Loss (kg)';
	$headings[] = '5% Weight Loss (lbs)';
	$headings[] = 'Target Weight (kg)';
	$headings[] = 'Target Weight (lbs)';
	$headings[] = 'Current Weight Loss (kg)';
	$headings[] = 'Current Weight Loss (lbs)';
	$headings[] = '% Weight Lost';
}

$headings[] = 'Payment Status';
$headings[] = 'Amount Paid';
$headings[] = 'Balance';

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($items) == 0) {
	$csv_data[] = array(
		'No participants'
	);
} else {
	foreach ($items as $item) {
		// shape up vars
		$target_loss_kg = 0;
		$target_loss_lbs = 0;
		$target_weight_kg = 0;
		$target_weight_lbs = 0;
		$current_loss_kg = 0;
		$current_loss_lbs = 0;
		$percent_lost = 0;
		$first_weight = 0;
		$last_weight = 0;

		$contact = $item->contact_first . ' ' . $item->contact_last;
		if (!empty($item->contact_title)) {
			$contact .= ' (' . ucwords($item->contact_title) . ')';
		}
		if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
			$booker = $item->booker_first . ' ' . $item->booker_last;
			if (!empty($item->booker_title)) {
				$booker .= ' (' . ucwords($item->booker_title) . ')';
			}
			$row = array(
				'name' => $item->child_first . ' ' . $item->child_last,
				'dob' => mysql_to_uk_date($item->dob),
				'age' => calculate_age($item->dob),
				'contact' => $booker,
				'relationship' => ucwords($item->relationship),
				'address1' => $item->booker_address1,
				'address2' => $item->booker_address2,
				'address3' => $item->booker_address3,
				'town' => $item->booker_town,
				'county' => $item->booker_county,
				'postcode' => $item->booker_postcode,
				'mobile' => $item->booker_mobile,
				'phone' => $item->booker_phone,
				'work' => $item->booker_workPhone,
				'email' => $item->booker_email,
			);
		} else {
			$row = array(
				'name' => $contact,
				'dob' => mysql_to_uk_date($item->contact_dob),
				'age' => calculate_age($item->contact_dob),
				'address1' => $item->address1,
				'address2' => $item->address2,
				'address3' => $item->address3,
				'town' => $item->town,
				'county' => $item->county,
				'postcode' => $item->postcode,
				'mobile' => $item->mobile,
				'phone' => $item->phone,
				'work' => $item->workPhone,
				'email' => $item->email,
			);
		}
		if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
			if ($item->photoConsent == 1) {
				$row['photoconsent'] = 'Yes';
			} else {
				$row['photoconsent'] = 'No';
			}
			$row['medical'] = $item->medical;
		} else {
			$row['medical'] = $item->contact_medical;
		}
		if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
			for ($i=1; $i <= 20; $i++) {
				$field = 'monitoring' . $i;
				$db_field = 'child_monitoring' . $i;
				if (!empty($booking_info->$field)) {
					$row[$field] = $item->$db_field;
				}
			}
		} else {
			for ($i=1; $i <= 20; $i++) {
				$field = 'monitoring' . $i;
				$db_field = 'contact_monitoring' . $i;
				if (!empty($booking_info->$field)) {
					$row[$field] = $item->$db_field;
				}
			}
		}

		if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
			$field = 'contactID';
		} else {
			$field = 'childID';
		}

		if ($booking_info->type == 'booking') {
			$i = 0;
			foreach ($lesson_ids as $date=>$lessons) {
				foreach ($lessons as $lessonID=>$name) {
					// if cancelled
					if (isset($cancellations[$lessonID][$date])) {
						// skip
					} else {
						if ((in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup')) && isset($booked_lessons[$item->cartID][$item->contactID][$lessonID][$date])) || (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) && isset($booked_lessons[$item->cartID][$item->childID][$lessonID][$date]))) {
							$row[] = 'Yes';
							if (substr($booking_info->register_type, -12) == '_bikeability') {
								$row[] = $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['bikeability_level'];
							} else if (substr($booking_info->register_type, -8) == '_shapeup') {
								$weight = $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['shapeup_weight'];
								$row[] = $weight;
								if ($i == 0) {
				                    $first_weight = $weight;
				                } else if ($weight > 0){
				                    $last_weight = $weight;
				                }
							}
						} else {
							$row[] = 'No';
							if (substr($booking_info->register_type, -12) == '_bikeability') {
								$row[] = NULL;
							} else if (substr($booking_info->register_type, -8) == '_shapeup') {
								$row[] = NULL;
							}
						}
					}
					$i++;
				}
			}
		} else {
			$i = 0;
			foreach ($lesson_ids as $day=>$lessons) {
				foreach ($lessons as $lessonID=>$name) {
					if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
						$row[] = 'Yes';
						if (substr($booking_info->register_type, -12) == '_bikeability') {
							$row[] = $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['bikeability_level'];
						}
						if (substr($booking_info->register_type, -8) == '_shapeup') {
							$weight = $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['shapeup_weight'];
							$row[] = $weight;
							if ($i == 0) {
								$first_weight = $weight;
							} else if ($weight > 0){
								$last_weight = $weight;
							}
						}
					} else {
						$row[] = 'No';
						if (substr($booking_info->register_type, -12) == '_bikeability') {
							$row[] = NULL;
						} else if (substr($booking_info->register_type, -8) == '_shapeup') {
							$row[] = NULL;
						}
					}
					$i++;
				}
			}
		}

		if (substr($booking_info->register_type, -12) == '_bikeability') {
			$row[] = $item->bikeability_level_overall;
		}

		if (substr($booking_info->register_type, -8) == '_shapeup') {
			if ($first_weight > 0) {
				$target_loss_kg = $first_weight*$target;
				$target_loss_lbs = $target_loss_kg*$lbs;
				$target_weight_kg = $first_weight*(1-$target);
				$target_weight_lbs = $target_weight_kg*$lbs;
				if ($last_weight > 0) {
	                $current_loss_kg = $last_weight-$first_weight;
	                $current_loss_lbs = $current_loss_kg*$lbs;
	                $percent_lost = ($current_loss_kg/$first_weight)*100;
				}
			}
			$row[] = round($target_loss_kg, 1);
            $row[] = round($target_loss_lbs, 1);
            $row[] = round($target_weight_kg, 1);
            $row[] = round($target_weight_lbs, 1);
            $row[] = round($current_loss_kg, 1);
            $row[] = round($current_loss_lbs, 1);
			$row[] = round($percent_lost, 1);
		}

		if ($item->participant_balance == 0) {
			$row['payment_status'] = 'Paid';
		} else {
			$row['payment_status'] = 'Due';
		}

		if (!empty($item->childcarevoucher_providerID)) {
			$row['payment_status'] .= ' (Childcare Voucher)';
		}

		$row['amount_paid'] = number_format($item->participant_total, 2);
		$row['balance'] = number_format($item->participant_balance, 2);

		// add to csv
		$csv_data[] = $row;
	}
}

$filename = !in_array($booking_info->register_type, array('children', 'individuals', 'individuals_bikeability', 'children_bikeability')) ? "export" : $booking_info->name."-".$block_info->name;
array_to_csv($csv_data, $filename.'.csv');
