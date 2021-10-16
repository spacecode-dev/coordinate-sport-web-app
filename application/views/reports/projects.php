<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'projects-report-search']); ?>
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Search</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_type">Report Type</label></strong>
				</p>
				<?php
				$options = array(
					'project-type' => 'Project Type Summary',
					'session-type' => 'Session Type Summary',
					'activity-type' => 'Activity Type Summary',
					'full' => 'Full Report',
					'alt' => 'Projects by Session Type/Activity',
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_from',
					'id' => 'field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_staff_id">Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($staff_list->num_rows() > 0) {
					foreach ($staff_list->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_is_active">Active Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_is_active', $options, $search_fields['is_active'], 'id="field_is_active" class="select2 form-control"');
				?>
			</div><?php
			if ($project_codes->num_rows() > 0) {
				?><div class='col-sm-2'>
					<p>
						<strong><label for="field_project_code_id">Project Code</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					foreach ($project_codes->result() as $row) {
						$options[$row->codeID] = $row->code;
					}
					echo form_dropdown('search_project_code_id', $options, $search_fields['project_code_id'], 'id="field_project_code_id" class="select2 form-control"');
					?>
				</div><?php
			}
			?>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-search'></i> Search
			</button>
			<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
				Cancel
			</a>
		</div>
	</div>
	<?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if ($row_data->num_rows() == 0 || ($search_fields['type'] == 'alt' && count($bookings) == 0)) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<?php
				switch ($search_fields['type']) {
					case 'alt':
						$staff = array();
						foreach ($row_data->result() as $row) {
							$staff[$row->staffID] = array(
								'name' => $row->first . ' ' . $row->surname,
								'salaried' => $row->payments_scale_salaried
							);
						}
						?><thead>
							<tr>
								<th rowspan="2" class="wide">Project Name</th>
								<th rowspan="2">Project Code</th>
								<th rowspan="2">Activity</th>
								<th rowspan="2">Session Type</th>
								<?php
								foreach ($staff as $staffID => $details) {
									?><th><?php echo $details['name']; ?></th><?php
								}
								?>
								<th rowspan="2">Contracted</th>
								<th rowspan="2">Sessional</th>
								<th rowspan="2">Total</th>
							</tr>
							<tr>
								<?php
								foreach ($staff as $staffID => $details) {
									?><th><?php
									if ($details['salaried'] == 1) {
										echo 'Contracted';
									} else {
										echo 'Sessional';
									}
									?></th><?php
								}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							$column_totals = array(
								'contracted' => 0,
								'sessional' => 0,
								'hours' => 0
							);
							if (count($bookings) > 0) {
								foreach ($bookings as $bookingID => $booking) {
									if (isset($report_data[$bookingID])) {
										foreach ($report_data[$bookingID] as $activity => $types) {
											foreach ($types as $type => $staffIDs) {
												$contracted = 0;
												$sessional = 0;
												$hours = 0;
												?><tr>
													<td><?php echo $booking['label']; ?></td>
													<td><?php echo $booking['code']; ?></td>
													<td><?php echo $activity; ?></td>
													<td><?php echo $type; ?></td>
													<?php
													foreach ($staff as $staffID => $details) {
														$booking_hours = 0;
														if (isset($staffIDs[$staffID])) {
															$booking_hours = $staffIDs[$staffID];
														}
														$hours += $booking_hours;
														if ($details['salaried'] == 1) {
															$contracted += $booking_hours;
														} else {
															$sessional += $booking_hours;
														}
														?><td><?php echo format_decimal_hours($booking_hours); ?></td><?php
														if (!array_key_exists($staffID, $column_totals)) {
															$column_totals[$staffID] = 0;
														}
														$column_totals[$staffID] += $booking_hours;
													}
													$column_totals['contracted'] += $contracted;
													$column_totals['sessional'] += $sessional;
													$column_totals['hours'] += $hours;
													?>
													<td><?php echo format_decimal_hours($contracted); ?></td>
													<td><?php echo format_decimal_hours($sessional); ?></td>
													<td><?php echo format_decimal_hours($hours); ?></td>
												</tr><?php
											}
										}
									}
								}
							}
							?>
							<tr>
								<td colspan="3"><strong>Totals</strong></td>
								<?php
								foreach ($staff as $staffID => $details) {
									if (!isset($column_totals[$staffID])) {
										$column_totals[$staffID] = 0;
									}
									echo '<td>' . format_decimal_hours($column_totals[$staffID]) . '</td>';
								}
								?><td>
									<?php echo format_decimal_hours($column_totals['contracted']); ?>
								</td>
								<td>
									<?php echo format_decimal_hours($column_totals['sessional']); ?>
								</td>
								<td>
									<?php echo format_decimal_hours($column_totals['hours']); ?>
								</td>
							</tr>
						</tbody><?php
						break;
					default:
						?><thead>
							<tr>
								<?php
								if ($search_fields['type'] == 'full') {
									?><th rowspan="2">
										Staff
									</th>
									<?php
									if (count($bookings) > 0) {
										?><th colspan="<?php echo count($bookings); ?>">
											Projects
										</th><?php
									}
									?>
									<th rowspan="2">
										Total
									</th><?php
								} else {
									?><th>
										Type
									</th>
									<th>
										Contracted Hours
									</th>
									<th>
										Sessional Hours
									</th>
									<th>
										Total
									</th><?php
								}
								?>
							</tr>
							<?php
							if ($search_fields['type'] == 'full' && count($bookings) > 0) {
								?><tr><?php
									foreach ($bookings as $booking) {
										?><th><?php echo $booking['label']; ?></th><?php
									}
								?></tr><?php
							}
							?>
						</thead>
						<tbody>
							<?php
							$column_totals = array();
							foreach ($row_data->result() as $row) {
								$hours = 0;
								?><tr><?php
									if ($search_fields['type'] == 'full') {
										?><td>
											<?php echo $row->first . ' ' . $row->surname; ?>
										</td>
										<?php
										if (count($bookings) > 0) {
											foreach ($bookings as $bookingID => $label) {
												$booking_hours = 0;
												if (isset($report_data[$row->staffID][$bookingID])) {
													$booking_hours = $report_data[$row->staffID][$bookingID];
												}
												$hours += $booking_hours;
												?><td><?php echo format_decimal_hours($booking_hours); ?></td><?php
												if (!array_key_exists($bookingID, $column_totals)) {
													$column_totals[$bookingID] = 0;
												}
												$column_totals[$bookingID] += $booking_hours;
											}
										}
									} else {
										?><td><?php echo $row->name; ?></td>
										<td><?php
											$booking_hours = 0;
											if (isset($report_data[$row->id]['contracted'])) {
												$booking_hours = $report_data[$row->id]['contracted'];
											}
											$hours += $booking_hours;
											echo format_decimal_hours($booking_hours);
											if (!array_key_exists('contracted', $column_totals)) {
												$column_totals['contracted'] = 0;
											}
											$column_totals['contracted'] += $booking_hours;
										?></td>
										<td><?php
											$booking_hours = 0;
											if (isset($report_data[$row->id]['sessional'])) {
												$booking_hours = $report_data[$row->id]['sessional'];
											}
											$hours += $booking_hours;
											echo format_decimal_hours($booking_hours);
											if (!array_key_exists('sessional', $column_totals)) {
												$column_totals['sessional'] = 0;
											}
											$column_totals['sessional'] += $booking_hours;
										?></td><?php
									}
									?><td>
										<?php
										echo format_decimal_hours($hours);
										if (!array_key_exists('hours', $column_totals)) {
											$column_totals['hours'] = 0;
										}
										$column_totals['hours'] += $hours;
										?>
									</td>
								</tr><?php
							}
							// other type
							if (in_array($search_fields['type'], array('session-type', 'activity-type'))) {
								$hours = 0;
								?><tr>
									<td>Other</td>
									<td><?php
										$booking_hours = 0;
										if (isset($report_data['other']['contracted'])) {
											$booking_hours = $report_data['other']['contracted'];
										}
										$hours += $booking_hours;
										echo format_decimal_hours($booking_hours);
										if (!array_key_exists('contracted', $column_totals)) {
											$column_totals['contracted'] = 0;
										}
										$column_totals['contracted'] += $booking_hours;
									?></td>
									<td><?php
										$booking_hours = 0;
										if (isset($report_data['other']['sessional'])) {
											$booking_hours = $report_data['other']['sessional'];
										}
										$hours += $booking_hours;
										echo format_decimal_hours($booking_hours);
										if (!array_key_exists('sessional', $column_totals)) {
											$column_totals['sessional'] = 0;
										}
										$column_totals['sessional'] += $booking_hours;
									?></td>
									<td>
										<?php
										echo format_decimal_hours($hours);
										if (!array_key_exists('hours', $column_totals)) {
											$column_totals['hours'] = 0;
										}
										$column_totals['hours'] += $hours;
										?>
									</td>
								</tr><?php
							}
							?>
							<tr>
								<td><strong>Totals</strong></td>
								<?php
								if ($search_fields['type'] == 'full') {
									if (count($bookings) > 0) {
										foreach ($bookings as $bookingID => $label) {
											echo '<td>' . format_decimal_hours($column_totals[$bookingID]) . '</td>';
										}
									}
								} else {
									?><td>
										<?php echo format_decimal_hours($column_totals['contracted']); ?>
									</td>
									<td>
										<?php echo format_decimal_hours($column_totals['sessional']); ?>
									</td><?php
								}
								?><td>
									<?php echo format_decimal_hours($column_totals['hours']); ?>
								</td>
							</tr>
						</tbody><?php
					break;
				}
				?>
			</table>
		</div>
	</div>
	<p><small class='text-muted'>Excludes hours as observers and participants</small></p>
	<?php
}
