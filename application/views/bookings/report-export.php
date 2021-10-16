<?php
// build data
$csv_data = array();

if (count($blocks) > 0 && count($lessons) > 0) {
	$prev_block = NULL;
	$prev_block_count = NULL;
	foreach ($blocks as $blockID => $block) {
		// intro
		$csv_data[] = array(
			$block['name']
		);
		$sub_heading = $block['dates'];
		if ($block['provisional'] == 1) {
			$sub_heading .= ' (Provisional)';
		}
		$csv_data[] = array(
			$sub_heading
		);
		// blank row
		$csv_data[] = array();

		// day headings
		$headings = array();
		$headings[] = 'Day';
		foreach ($types_by_day as $day => $types) {
			$i = 0;
			foreach ($types as $type) {
				if ($i == 0) {
					$headings[] = ucwords($day);
				} else {
					$headings[] = NULL;
				}
				$i++;
			}
		}
		$headings[] = NULL;
		$csv_data[] = $headings;

		// type headings
		$headings = array();
		$headings[] = 'Type';
		foreach ($types_by_day as $day => $types) {
			foreach ($types as $type) {
				$headings[] = $type;
			}
		}
		$headings[] = 'Total';
		$csv_data[] = $headings;

		// targets
		$target_total = 0;
		$row = array(
			'Targets'
		);
		foreach ($types_by_day as $day => $types) {
			foreach ($types as $type) {
				$target = 0;
				if (isset($target_participants[$blockID][$day][$type])) {
					$target = $target_participants[$blockID][$day][$type];
				}
				$target_total += $target;
				$row[] = $target;
			}
		}
		$row[] = $target_total;
		$csv_data[] = $row;

		// participants
		$count_total = 0;
		$row = array(
			'Participants'
		);
		foreach ($types_by_day as $day => $types) {
			foreach ($types as $type) {
				$count = 0;
				if (isset($participants[$blockID][$day][$type])) {
					$count = $participants[$blockID][$day][$type];
				}
				$count_total += $count;
				$row[] = $count;
			}
		}
		$row[] = $count_total;
		$csv_data[] = $row;

		// target difference
		$row = array(
			'Target Difference'
		);
		foreach ($types_by_day as $day => $types) {
			foreach ($types as $type) {
				$count = 0;
				$target = 0;
				if (isset($participants[$blockID][$day][$type])) {
					$count = $participants[$blockID][$day][$type];
				}
				if (isset($target_participants[$blockID][$day][$type])) {
					$target = $target_participants[$blockID][$day][$type];
				}
				$percent = 0;
				if ($target > 0) {
					$percent = ((100/$target) * $count) - 100;
				}
				$row[] = round($percent, 1) . '%';
			}
		}
		$percent = 0;
		if ($target > 0) {
			$percent = ((100/$target_total) * $count_total) - 100;
		}
		$row[] = round($percent, 1) . '%';
		$csv_data[] = $row;

		// prev block difference
		$row = array(
			'Prev Block Difference'
		);
		foreach ($types_by_day as $day => $types) {
			foreach ($types as $type) {
				$count = 0;
				$target = 0;
				if (isset($participants[$blockID][$day][$type])) {
					$count = $participants[$blockID][$day][$type];
				}
				if (isset($participants[$prev_block][$day][$type])) {
					$target = $participants[$prev_block][$day][$type];
				}
				$percent = 0;
				if ($target > 0) {
					$percent = ((100/$target) * $count) - 100;
				}
				$row[] = round($percent, 1) . '%';
			}
		}
		$percent = 0;
		if ($prev_block_count > 0) {
			$percent = ((100/$prev_block_count) * $count_total) - 100;
		}
		$row[] = round($percent, 1) . '%';
		$csv_data[] = $row;

		// blank row
		$csv_data[] = array();

		$prev_block = $blockID;
		$prev_block_count = $count_total;
	}

	// overall
	$csv_data[] = array(
		'Overall'
	);
	$csv_data[] = array();

	// overall day headings
	$headings = array();
	$headings[] = 'Day';
	foreach ($types_by_day as $day => $types) {
		$i = 0;
		foreach ($types as $type) {
			if ($i == 0) {
				$headings[] = ucwords($day);
			} else {
				$headings[] = NULL;
			}
			$i++;
		}
	}
	$headings[] = NULL;
	$csv_data[] = $headings;

	// overall type headings
	$headings = array();
	$headings[] = 'Type';
	foreach ($types_by_day as $day => $types) {
		foreach ($types as $type) {
			$headings[] = $type;
		}
	}
	$headings[] = 'Total';
	$csv_data[] = $headings;

	// overall targets
	$target_total = 0;
	$row = array(
		'Targets'
	);
	foreach ($types_by_day as $day => $types) {
		foreach ($types as $type) {
			$target = 0;
			foreach ($target_participants as $blockID => $block) {
				if (isset($target_participants[$blockID][$day][$type])) {
					$target += $target_participants[$blockID][$day][$type];
				}
			}
			$target_total += $target;
			$row[] = $target;
		}
	}
	$row[] = round($target_total, 2);
	$csv_data[] = $row;

	// overall participants
	$count_total = 0;
	$row = array(
		'Participants'
	);
	foreach ($types_by_day as $day => $types) {
		foreach ($types as $type) {
			$count = 0;
			foreach ($participants as $blockID => $block) {
				if (isset($participants[$blockID][$day][$type])) {
					$count += $participants[$blockID][$day][$type];
				}
			}
			$count_total += $count;
			$row[] = $count;
		}
	}
	$row[] = round($count_total, 2);
	$csv_data[] = $row;

	// overall target difference
	$row_total = 0;
	$row = array(
		'Target Difference'
	);
	foreach ($types_by_day as $day => $types) {
		foreach ($types as $type) {
			$count = 0;
			$target = 0;
			foreach ($participants as $blockID => $block) {
				if (isset($participants[$blockID][$day][$type])) {
					$count += $participants[$blockID][$day][$type];
				}
			}
			foreach ($target_participants as $blockID => $block) {
				if (isset($target_participants[$blockID][$day][$type])) {
					$target += $target_participants[$blockID][$day][$type];
				}
			}
			$percent = 0;
			if ($target > 0) {
				$percent = ((100/$target) * $count) - 100;
			}
			$row[] = round($percent, 1) . '%';
		}
	}
	$percent = 0;
	if ($target > 0) {
		$percent = ((100/$target_total) * $count_total) - 100;
	}
	$row[] = round($percent, 1) . '%';
	$csv_data[] = $row;
}

// export
array_to_csv($csv_data, 'project-report.csv');