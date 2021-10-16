<?php
// build data
$csv_data = array();

// set headings
$headings = array(
	'Staff',
	'Payroll Number',
	'Permission Level'
);
if (count($brands) > 0 && $search_fields['filter_by_brand']) {
	foreach ($brands as $brand) {
		$headings[] = $brand->name;
	}
}
if (count($activities) > 0 && $search_fields['filter_by_activity']) {
	foreach ($activities as $activity) {
		$headings[] = $activity->name;
	}
}
if (count($roles) > 0 && $search_fields['filter_by_role']) {
	foreach ($roles as $key => $label) {
		$headings[] = $label;
	}
}
$headings[] = 'Total';
$headings[] = 'Salaried Hours';
$headings[] = 'Hours Up/Down';
if ($this->auth->has_features('expenses')) {
	$headings[] = 'Expenses';
}
if($mileage_section == 1){
	$headings[] = "Mileage";
}

// add to csv
$csv_data[] = $headings;

// check if any data
if (count($staff) == 0 || (count($timesheet_data) == 0 && count($expense_data) == 0)) {
	$csv_data[] = array(
		'No data'
	);
} else {
	foreach ($staff as $item) {
		// skip if no data
		if (!array_key_exists($item->staffID, $timesheet_data) && !array_key_exists($item->staffID, $expense_data)) {
			continue;
		}
		$hours = 0;
		$row = array(
			'name' => $item->first . ' ' . $item->surname,
			'payroll_number' => $item->payroll_number,
			'permission_level' => $this->settings_library->get_permission_level_label($item->department)
		);
		if (count($brands) > 0 && $search_fields['filter_by_brand']) {
			foreach ($brands as $brand) {
				$brand_hours = 0;
				if (isset($timesheet_data[$item->staffID][$brand->brandID])) {
					$brand_hours = $timesheet_data[$item->staffID][$brand->brandID];
				}
				$hours += $brand_hours;
				$row['brand_' . $brand->brandID] = $brand_hours;
			}
		}
		if (count($activities) > 0 && $search_fields['filter_by_activity']) {
			foreach ($activities as $activity) {
				$activity_hours = 0;
				if (isset($timesheet_data[$item->staffID]['activity'][$activity->activityID])) {
					$activity_hours = $timesheet_data[$item->staffID]['activity'][$activity->activityID];
				}
				$row['activity_' . $activity->activityID] = $activity_hours;
			}
		}
		if (count($roles) > 0 && $search_fields['filter_by_role']) {
			foreach ($roles as $key => $label) {
				$role_hours = 0;
				if (isset($timesheet_data[$item->staffID][$key])) {
					$role_hours = $timesheet_data[$item->staffID][$key];
				}
				$row[$key] = $role_hours;
			}
		}
		$item->target_hours = ($item->target_hours/7)*$days;
		$row['hours'] = $hours;
		$row['target_hours'] = $item->target_hours;
		$row['hours_up_down'] = $hours - $item->target_hours;
		if ($this->auth->has_features('expenses')) {
			$expenses = 0;
			if (isset($expense_data[$item->staffID])) {
				$expenses = $expense_data[$item->staffID];
			}
			$row['expenses'] = $expenses;
		}
		if ($this->auth->has_features('mileage')) {
			$mileage = 0;
			if (isset($mileage_data[$item->staffID])) {
				$mileage = $mileage_data[$item->staffID];
			}
			$row['mileage'] = $mileage;
		}
		// add to csv
		$csv_data[] = $row;
	}
}

array_to_csv($csv_data, 'timesheets-' . uk_to_mysql_date($search_fields['date_from']) . '-to-' . uk_to_mysql_date($search_fields['date_to']) . '.csv');
