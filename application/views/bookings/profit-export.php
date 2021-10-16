<?php
// build data
$csv_data = array();

// headings
$headings = array();
$headings[] = NULL;
if($week == 0){
	foreach ($blocks as $blockID => $name) {
		$headings[] = $name;
	}
}else{
	$dt = new DateTime;
	$headings[] = "Week ".$week." (".$dt->setISODate($year, $week, 1)->format('jS M').")";
}
$headings[] = 'Total';
$csv_data[] = $headings;

// revenue heading
$row = array(
	'Revenue'
);
$csv_data[] = $row;

// track income for col totals
$block_income = array();

// session income
if (count($session_income) > 0) {
	ksort($session_income);
	foreach ($session_income as $type => $block_details) {
		$row_total = 0;
		$row = array(
			$type
		);
		foreach ($blocks as $blockID => $name) {
			$val = 0;
			if (isset($session_income[$type][$blockID])) {
				$val = $session_income[$type][$blockID];
			}
			if($week == 0){
				$row[] = round($val, 2);
			}
			// add to row total
			$row_total += $val;
			// add to col total
			if (!isset($block_income[$blockID])) {
				$block_income[$blockID] = 0;
			}
			$block_income[$blockID] += $val;
		}
		if($week != 0){
			$row[] = round($row_total, 2);
		}
		$row[] = round($row_total, 2);
		$csv_data[] = $row;
	}
}

// misc income
$row_total = 0;
$row = array(
	'Misc'
);
foreach ($blocks as $blockID => $name) {
	$val = 0;
	if (isset($misc_income[$blockID])) {
		$val = $misc_income[$blockID];
	}
	if($week == 0){
		$row[] = round($val, 2);
	}
	// add to row total
	$row_total += $val;
	// add to col total
	if (!isset($block_income[$blockID])) {
		$block_income[$blockID] = 0;
	}
	$block_income[$blockID] += $val;
}
if($week != 0){
	$row[] = round($row_total, 2);
}
$row[] = round($row_total, 2);
$csv_data[] = $row;

// contract income
if (count($contract_income) > 0) {
	ksort($contract_income);
	$row = array(
		'Contract Revenue'
	);
	$csv_data[] = $row;
	foreach ($contract_income as $type => $val) {
		$row = array(
			$type
		);
		if($week == 0){
			foreach ($blocks as $blockID => $name) {
				$row[] = NULL;
			}
		}else{
			$row[] = NULL;
		}
		$row[] = round($val, 2);
		$csv_data[] = $row;
	}
}

// total income
$row_total = 0;
$row = array(
	'Total Revenue'
);
foreach ($blocks as $blockID => $name) {
	$val = 0;
	if (isset($block_income[$blockID])) {
		$val = $block_income[$blockID];
	}
	if($week == 0){
		$row[] = round($val, 2);
	}
	// add to row total
	$row_total += $val;
}
$row_total += array_sum($contract_income);
if($week != 0){
	$row[] = round($row_total, 2);
}
$row[] = round($row_total, 2);
$csv_data[] = $row;

// cost heading
$row = array(
	'Costs'
);
$csv_data[] = $row;

// track costs for col totals
$block_costs = array();

// staff costs
if (count($staff_costs) > 0) {
	ksort($staff_costs);
	foreach ($staff_costs as $type => $block_details) {
		$row = array(
			'Staff - ' . $type
		);
		$row_total = 0;
		foreach ($blocks as $blockID => $name) {
			$val = 0;
			if (isset($staff_costs[$type][$blockID])) {
				$val = $staff_costs[$type][$blockID];
			}
			if($week == 0){
				$row[] = round($val, 2);
			}
			// add to row total
			$row_total += $val;
			// add to col total
			if (!isset($block_costs[$blockID])) {
				$block_costs[$blockID] = 0;
			}
			$block_costs[$blockID] += $val;
		}
		if($week != 0){
			$row[] = round($row_total, 2);
		}
		$row[] = round($row_total, 2);
		$csv_data[] = $row;
	}
}

// costs
if (count($costs) > 0) {
	foreach ($costs as $category => $block_details) {
		$row = array(
			$category
		);
		$row_total = 0;
		foreach ($blocks as $blockID => $name) {
			$val = 0;
			if (isset($costs[$category][$blockID])) {
				$val = $costs[$category][$blockID];
			}
			if($week == 0){
				$row[] = round($val, 2);
			}
			// add to row total
			$row_total += $val;
			// add to col total
			if (!isset($block_costs[$blockID])) {
				$block_costs[$blockID] = 0;
			}
			$block_costs[$blockID] += $val;
		}
		if($week != 0){
			$row[] = round($row_total, 2);
		}
		$row[] = round($row_total, 2);
		$csv_data[] = $row;
	}
}

// total costs
$row_total = 0;
$row = array(
	'Total Costs'
);
foreach ($blocks as $blockID => $name) {
	$val = 0;
	if (isset($block_costs[$blockID])) {
		$val = $block_costs[$blockID];
	}
	if($week == 0){
		$row[] = round($val, 2);
	}
	// add to row total
	$row_total += $val;
}
if($week != 0){
	$row[] = round($row_total, 2);
}
$row[] = round($row_total, 2);
$csv_data[] = $row;

// profit/loss
$row_total = 0;
$row = array(
	'Profit/Loss'
);
foreach ($blocks as $blockID => $name) {
	$val = 0;
	if (isset($block_income[$blockID])) {
		$val = $block_income[$blockID];
	}
	if (isset($block_costs[$blockID])) {
		$val -= $block_costs[$blockID];
	}
	if($week == 0){
		$row[] = round($val, 2);
	}
	// add to row total
	$row_total += $val;
}
$row_total += array_sum($contract_income);
if($week != 0){
	$row[] = round($row_total, 2);
}
$row[] = round($row_total, 2);
$csv_data[] = $row;

// export
array_to_csv($csv_data, 'profit-loss.csv');