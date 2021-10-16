<?php
display_messages();
if ($bookingID != NULL && !in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
echo form_open();
?><div class='card card-custom'>
	<div class='responsive-table fixed-<?php if ($register_type == 'bikeability') { echo '2 with-checkboxes'; } else { echo '1'; } ?>'>
		<div class="scrollable-area">
			<table class='table table-striped table-bordered bulk-checkboxes' id='names_register' data-block="<?php echo $blockID; ?>" data-registertype="<?php echo $register_type; ?>">
				<thead>
					<tr>
						<?php
						if ($register_type == 'bikeability') {
							?><th></th><?php
						}
						?>
						<th></th>
						<?php
						$headings = array();
						$date = $block_info->startDate;
						$multiplier = 1;
						// if bikeability or shape up, allow twice the number of columns per lesson
						if (in_array($register_type, array('bikeability', 'shapeup'))) {
							$multiplier = 2;
						}
						while (strtotime($date) <= strtotime($block_info->endDate)) {
							if (array_key_exists($date, $lesson_data)) {
								$headings[$date] = '<th scope="col" colspan="' . count($lesson_data[$date])*$multiplier . '">' . date("l", strtotime($date)) . '<br />' . mysql_to_uk_date($date) . '</th>';
							}
							$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
						}
						// sort and show
						ksort($headings);
						echo implode("\n", $headings);
						if ($register_type == 'bikeability') {
							?><th></th><?php
						}
						if ($register_type == 'shapeup') {
							?><th colspan="2">5% Weight Loss</th>
							<th colspan="2">Target Weight</th>
							<th colspan="2">Current Weight Loss</th>
							<th>% Weight Lost</th><?php
						}
						if (count($monitoring_fields) > 0) {
							?><th scope="col" colspan="<?php echo count($monitoring_fields); ?>">Monitoring</th><?php
						}
						?>
						<th></th>
					</tr>
					<tr>
						<?php
						$cols = 2;
						if ($register_type == 'bikeability') {
							$cols++;
							?><th class="bulk-checkbox">
								<input type="checkbox" />
							</th><?php
						}
						?>
						<th>Name</th>
						<?php
						foreach ($lesson_data as $date => $lessons) {
							foreach ($lessons as $lessonID=>$name) {
								?><th scope="col"><?php echo $name; ?></th><?php
								if ($register_type == 'bikeability') {
									?><th scope="col">Level</th><?php
									$cols++;
								} else if ($register_type == 'shapeup') {
									?><th scope="col">Weight</th><?php
									$cols++;
								}
								$cols++;
							}
						}
						if ($register_type == 'bikeability') {
							?><th scope="col">Overall Level</th><?php
							$cols++;
						}
						if ($register_type == 'shapeup') {
							?><th>kg</th>
							<th>lbs</th>
							<th>kg</th>
							<th>lbs</th>
							<th>kg</th>
							<th>lbs</th>
							<th></th><?php
							$cols += 7;
						}
						if (count($monitoring_fields) > 0) {
							foreach ($monitoring_fields as $key => $label) {
								?><th scope="col" data-monitoring="<?php echo $key; ?>"><?php echo $label; ?></th><?php
								$cols++;
							}
						}
						?>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<tr class="nodata">
						<?php
						if ($register_type == 'bikeability') {
							?><td></td>
							<td class="loading">Loading...</td>
							<td colspan="<?php echo $cols-2; ?>">&nbsp;</td><?php
						} else {
							?><td class="loading">Loading...</td>
							<td colspan="<?php echo $cols-1; ?>">&nbsp;</td><?php
						}
						?>
					</tr>
				</tbody>
				<?php
				if ($register_type == 'bikeability') {
					?><tfoot class="bulk-actions">
						<tr>
							<td></td>
							<td></td>
							<?php
							foreach ($lesson_data as $date => $lessons) {
								foreach ($lessons as $lessonID=>$name) {
									?><td></td>
									<td><select class="bulk">
										<option value="">Bulk Level</option>
										<?php
										foreach ($bikeability_levels as $key => $value) {
											?><option value="<?php echo $key; ?>"><?php echo $key . ' ' . $value; ?></option><?php
										}
										?>
										<option value="remove">Remove Level</option>
									</select></td><?php
								}
							}
							?><td><select class="bulk">
								<option value="">Bulk Level</option>
								<?php
								foreach ($bikeability_levels_overall as $key => $value) {
									?><option value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
								}
								?>
								<option value="remove">Remove Level</option>
							</select></td><?php
							if (count($monitoring_fields) > 0) {
								foreach ($monitoring_fields as $key => $label) {
									?><td></td><?php
								}
							}
							?>
							<td></td>
						</tr>
					</tfoot><?php
				}
				?>
			</table>
		</div>
	</div>
</div>
<br />
<div class="row">
	<div class="col-sm-12">
		<button class='btn btn-primary add-row'>
			Add Row
		</button>
		<button class='btn btn-primary save'>
			Save
		</button>
	</div>
</div>
<?php echo form_close(); ?>
<script>
	var lesson_data = <?php echo json_encode($lesson_data); ?>;
	var monitoring_fields = <?php echo json_encode($monitoring_fields); ?>;
	var bikeability_levels = <?php echo json_encode($bikeability_levels); ?>;
	var bikeability_levels_overall = <?php echo json_encode($bikeability_levels_overall); ?>;
	var attendance_data = <?php echo json_encode($attendance_data); ?>;
</script>
