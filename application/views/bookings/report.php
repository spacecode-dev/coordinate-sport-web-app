<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
if (count($blocks) == 0 || count($lessons) == 0) {
	?>
	<div class="alert alert-info">
		No data
	</div>
	<?php
} else {
	$prev_block = NULL;
	$prev_block_count = NULL;
	foreach ($blocks as $blockID => $block) {
		?><div class='card card-custom'>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label"><?php echo $block['name']; ?> <small><?php
					echo $block['dates'];
					if ($block['provisional'] == 1) {
						echo ' (Provisional)';
					}
					?></small></h3>
				</div>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th rowspan="2"></th>
							<?php
							foreach ($types_by_day as $day => $types) {
								?><th colspan="<?php echo count($types); ?>">
									<?php echo ucwords($day); ?>
								</th><?php
							}
							?>
							<th rowspan="2">
								Total
							</th>
						</tr>
						<tr>
							<?php
							foreach ($types_by_day as $day => $types) {
								foreach ($types as $type) {
									?><th>
										<?php echo $type; ?>
									</th><?php
								}
							}
							?>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>Targets</td>
							<?php
							$target_total = 0;
							foreach ($types_by_day as $day => $types) {
								foreach ($types as $type) {
									?><td>
										<?php
										$target = 0;
										if (isset($target_participants[$blockID][$day][$type])) {
											$target = $target_participants[$blockID][$day][$type];
										}
										$target_total += $target;
										echo $target;
										?>
									</td><?php
								}
							}
							?>
							<td><?php echo $target_total; ?></td>
						</tr>
						<tr>
							<td>Participants</td>
							<?php
							$count_total = 0;
							foreach ($types_by_day as $day => $types) {
								foreach ($types as $type) {
									?><td>
										<?php
										$count = 0;
										if (isset($participants[$blockID][$day][$type])) {
											$count = $participants[$blockID][$day][$type];
										}
										$count_total += $count;
										echo $count;
										?>
									</td><?php
								}
							}
							?>
							<td><?php echo $count_total; ?></td>
						</tr>
						<tr>
							<td>Target Difference</td>
							<?php
							foreach ($types_by_day as $day => $types) {
								foreach ($types as $type) {
									?><td>
										<?php
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
										$class = 'text-red';
										$prefix = NULL;
										if ($percent >= 0) {
											$class = 'text-green';
										}
										if ($percent > 0) {
											$prefix = '+';
										}
										echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
										?>
									</td><?php
								}
							}
							?>
							<td><?php
							$percent = 0;
							if ($target > 0) {
								$percent = ((100/$target_total) * $count_total) - 100;
							}
							$class = 'text-red';
							$prefix = NULL;
							if ($percent >= 0) {
								$class = 'text-green';
							}
							if ($percent > 0) {
								$prefix = '+';
							}
							echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
							?></td>
						</tr>
						<?php
						if (!empty($prev_block)) {
							?><tr>
								<td>Prev Block Difference</td>
								<?php
								foreach ($types_by_day as $day => $types) {
									foreach ($types as $type) {
										?><td>
											<?php
											$count = 0;
											$target = 0;
											if (isset($participants[$blockID][$day][$type])) {
												$count = $participants[$blockID][$day][$type];
											}
											if (isset($participants[$prev_block][$day][$type])) {
												$target = $participants[$prev_block][$day][$type];
											}
											$difference = $count - $target;
											$percent = 0;
											if ($target > 0) {
												$percent = ((100/$target) * $count) - 100;
											}
											$class = 'text-red';
											$prefix = NULL;
											if ($percent >= 0) {
												$class = 'text-green';
											}
											if ($percent > 0) {
												$prefix = '+';
											}
											echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
											?>
										</td><?php
									}
								}
								?>
								<td><?php
								$percent = 0;
								if ($prev_block_count > 0) {
									$percent = ((100/$prev_block_count) * $count_total) - 100;
								}
								$class = 'text-red';
								$prefix = NULL;
								if ($percent >= 0) {
									$class = 'text-green';
								}
								if ($percent > 0) {
									$prefix = '+';
								}
								echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
								?></td>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div><?php
		$prev_block = $blockID;
		$prev_block_count = $count_total;
	}
	?><div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Overall</h3>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th rowspan="2"></th>
						<?php
						foreach ($types_by_day as $day => $types) {
							?><th colspan="<?php echo count($types); ?>">
								<?php echo ucwords($day); ?>
							</th><?php
						}
						?>
						<th rowspan="2">
							Total
						</th>
					</tr>
					<tr>
						<?php
						foreach ($types_by_day as $day => $types) {
							foreach ($types as $type) {
								?><th>
									<?php echo $type; ?>
								</th><?php
							}
						}
						?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Targets</td>
						<?php
						$target_total = 0;
						foreach ($types_by_day as $day => $types) {
							foreach ($types as $type) {
								?><td>
									<?php
									$target = 0;
									foreach ($target_participants as $blockID => $block) {
										if (isset($target_participants[$blockID][$day][$type])) {
											$target += $target_participants[$blockID][$day][$type];
										}
									}
									$target_total += $target;
									echo $target;
									?>
								</td><?php
							}
						}
						?>
						<td><?php echo $target_total; ?></td>
					</tr>
					<tr>
						<td>Participants</td>
						<?php
						$count_total = 0;
						foreach ($types_by_day as $day => $types) {
							foreach ($types as $type) {
								?><td>
									<?php
									$count = 0;
									foreach ($participants as $blockID => $block) {
										if (isset($participants[$blockID][$day][$type])) {
											$count += $participants[$blockID][$day][$type];
										}
									}
									$count_total += $count;
									echo $count;
									?>
								</td><?php
							}
						}
						?>
						<td><?php echo $count_total; ?></td>
					</tr>
					<tr>
						<td>Target Difference</td>
						<?php
						foreach ($types_by_day as $day => $types) {
							foreach ($types as $type) {
								?><td>
									<?php
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
									$class = 'text-red';
									$prefix = NULL;
									if ($percent >= 0) {
										$class = 'text-green';
									}
									if ($percent > 0) {
										$prefix = '+';
									}
									echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
									?>
								</td><?php
							}
						}
						?>
						<td><?php
						$percent = 0;
						if ($target > 0) {
							$percent = ((100/$target_total) * $count_total) - 100;
						}
						$class = 'text-red';
						$prefix = NULL;
						if ($percent >= 0) {
							$class = 'text-green';
						}
						if ($percent > 0) {
							$prefix = '+';
						}
						echo '<span class="' . $class . '">' . $prefix . round($percent, 1) . '%</span>';
						?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><?php
}
