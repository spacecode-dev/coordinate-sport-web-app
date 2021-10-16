<?php
/**
 * @var $payroll_data CI_DB_result
 * @var $staff CI_DB_result
 * @var $quals array
 * @var $staff_list CI_DB_result
 * @var $payroll_data array
 * @var $search_fields array
 * @var $page_base string
 */

display_messages();
$end_year = date('Y');
$start_year = 2000;
if (time() > strtotime("last day of August " . date('Y'))) {
	$end_year += 1;
}

$options = ['' => 'Select'];

while ($start_year <= $end_year) {
	$options[$end_year] = $end_year;
	$end_year -= 1;
}
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'projects-contracts-report-search']); ?>
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
					<strong><label for="search_by">Search By</label></strong>
				</p>
				<?php
				echo form_dropdown('search_by', [
					'academic_year' => 'Academic Year',
					'dates_period' => 'Dates Period'
				], $search_fields['search_by'], 'id="search_by" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2 academic_year' <?php if ($search_fields['search_by'] != 'academic_year') { ?> style="display: none;" <?php } ?>>
				<p>
					<strong><label for="academic_year">Academic Year</label></strong>
				</p>
				<?php
				echo form_dropdown('search_academic_year', $options, $search_fields['search_academic_year'], 'id="search_academic_year" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2 dates_period' <?php if ($search_fields['search_by'] != 'dates_period') { ?> style="display: none;" <?php } ?>>
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
			<div class='col-sm-2 dates_period' <?php if ($search_fields['search_by'] != 'dates_period') { ?> style="display: none;" <?php } ?>>
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
					<strong><label for="search_by">Booking Type</label></strong>
				</p>
				<?php
				echo form_dropdown('search_booking_type', [
					'' => 'Select',
					'contracts' => 'Contracts',
					'projects' => 'Projects'
				], $search_fields['search_booking_type'], 'id="search_booking_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-1'>
				<p>
					<strong><label for="search_by">Show Blocks</label></strong>
				</p>
				<label>
					<?php
					$data = array(
						'name'          => 'show_blocks',
						'id'            => 'show_blocks',
						'value'         => 'show_blocks',
						'checked'       => $search_fields['show_blocks']
					);
					echo form_checkbox($data); ?>
				</label>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Customer</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($orgs as $org) {
					$options[$org->orgID] = $org->name;
				}

				echo form_dropdown('search_orgs', $options, $search_fields['search_orgs'], 'id="search_orgs" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Staff</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($staff_list as $staff) {
					$options[$staff->staffID] = $staff->first . ' ' . $staff->surname;
				}

				echo form_dropdown('search_staff', $options, $search_fields['search_staff'], 'id="search_staff" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Session Type</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($session_types as $type) {
					$options[$type->typeID] = $type->name;
				}

				echo form_dropdown('search_session_types', $options, $search_fields['search_session_types'], 'id="search_session_types" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Activity</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($activities as $activity) {
					$options[$activity->activityID] = $activity->name;
				}

				echo form_dropdown('search_activity', $options, $search_fields['search_activity'], 'id="search_activity" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Department</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($departments as $department) {
					$options[$department->brandID] = $department->name;
				}

				echo form_dropdown('search_department', $options, $search_fields['search_department'], 'id="search_department" class="select2 form-control"');
				?>
			</div>
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
if ($contracts->num_rows() == 0 ) {
?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?>

<?php echo $this->pagination_library->display($page_base); ?>

<div class='card'>
	<div class="fixed-scrollbar"></div>
	<div class='table-responsive'>
		<?php if ($search_fields['show_blocks']) {?>
			<table class='table table-striped table-bordered' id="project_contract">
				<thead>
				<tr>
					<th>Customer</th>
					<th>Project's Name</th>
					<th>Blocks</th>
					<th class="col-md-1">Start Date</th>
					<th class="col-md-1">End Date</th>
					<th>Staff</th>
					<th>Department</th>
					<th>Session Types</th>
					<th class="col-md-1">Activities</th>
					<th class="text-nowrap">Total number of hours<br>delivered for the customer</th>
					<th class="text-nowrap">Total number of hours delivered<br>in the Contract or Project</th>
					<th class="text-nowrap">Total number of Hours<br>delivered in the Block</th>
					<th>Total Contract Revenue</th>
					<th>Total Costs</th>
					<th>Profit/Loss</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($contracts->result() as $row) {
					if (!isset($booking_data[$row->bookingID]))
						continue;
					?>
					<?php foreach ($booking_data[$row->bookingID] as $block_id => $block_data) { ?>
						<tr>
							<td>
								<?php if (empty($booking_data[$row->bookingID][$block_id]['org_name'])) {
									echo $row->org;
								} else {
									echo $booking_data[$row->bookingID][$block_id]['org_name'];
								} ?>
							</td>
							<td>
								<?php echo $row->name; ?>
							</td>
							<td>
								<?php echo $booking_data[$row->bookingID][$block_id]['block_name']; ?>
							</td>
							<td>
								<?php if (!empty($booking_data[$row->bookingID][$block_id]['start_date'])) {
									echo date('d/m/Y', strtotime($booking_data[$row->bookingID][$block_id]['start_date']));
								}?>
							</td>
							<td>
								<?php if (!empty($booking_data[$row->bookingID][$block_id]['end_date'])) {
									echo date('d/m/Y', strtotime($booking_data[$row->bookingID][$block_id]['end_date']));
								}?>
							</td>
							<td>
								<?php if (isset($booking_data[$row->bookingID][$block_id]['staff'])) {
									echo $booking_data[$row->bookingID][$block_id]['staff'];
								} ?>
							</td>
							<td>
								<?php
								if (!empty($row->department)) {
									echo $row->department;
								}
								?>
							</td>
							<td>
								<?php if (!empty($booking_data[$row->bookingID][$block_id]['type_other'])) {
									echo $booking_data[$row->bookingID][$block_id]['type_other'];
								} else if (!empty($booking_data[$row->bookingID][$block_id]['session_types'])) {
									echo $booking_data[$row->bookingID][$block_id]['session_types'];
								} ?>
							</td>
							<td>
								<?php if (!empty($booking_data[$row->bookingID][$block_id]['activities_other'])) {
									echo $booking_data[$row->bookingID][$block_id]['activities_other'];
								} else if (!empty($booking_data[$row->bookingID][$block_id]['activities'])) {
									echo $booking_data[$row->bookingID][$block_id]['activities'];
								} ?>
							</td>
							<?php if (isset($customer_hours[$booking_data[$row->bookingID][$block_id]['block_org_id']])) { ?>
								<td>
									<?php echo number_format($customer_hours[$booking_data[$row->bookingID][$block_id]['block_org_id']], 2); ?>
								</td>
							<?php } else if (isset($customer_hours[$row->orgID])) { ?>
								<td>
									<?php echo number_format($customer_hours[$row->orgID], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
							<?php if (isset($costs[$row->bookingID]['hours'])) { ?>
								<td>
									<?php echo number_format($costs[$row->bookingID]['hours'], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
							<?php if (isset($costs[$row->bookingID]['block_hours'][$block_id])) { ?>
								<td>
									<?php echo number_format($costs[$row->bookingID]['block_hours'][$block_id], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
							<?php if (isset($income[$row->bookingID]['total'])) { ?>
								<td>
									<?php echo number_format($income[$row->bookingID]['total'], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
							<?php if (isset($costs[$row->bookingID]['total'])) { ?>
								<td>
									<?php echo number_format($costs[$row->bookingID]['total'], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
							<?php if (isset($total_profit[$row->bookingID])) { ?>
								<td>
									<?php echo number_format($total_profit[$row->bookingID], 2); ?>
								</td>
							<?php } else { ?>
								<td>
									0
								</td>
							<?php } ?>
						</tr>
					<?php } ?>

				<?php } ?>
				</tbody>
			</table>
		<?php } else { ?>
			<table class='table table-striped table-bordered' id="project_contract">
				<thead>
				<tr>
					<th>Customer</th>
					<th>Project's Name</th>
					<th class="col-md-1">Start Date</th>
					<th class="col-md-1">End Date</th>
					<th>Staff</th>
					<th>Department</th>
					<th>Session Types</th>
					<th class="col-md-1">Activities</th>
					<th class="text-nowrap">Total number of hours<br>delivered for the customer</th>
					<th class="text-nowrap">Total number of hours delivered<br>in the Contract or Project</th>
					<th>Total Contract Revenue</th>
					<th>Total Costs</th>
					<th>Profit/Loss</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($contracts->result() as $row) {
					?>
					<tr>
						<td>
							<?php echo $row->org; ?>
						</td>
						<td>
							<?php echo $row->name; ?>
						</td>
						<td>
							<?php echo $row->startDate; ?>
						</td>
						<td>
							<?php echo $row->endDate; ?>
						</td>
						<td class="text-nowrap">
							<span class="truncate">
							<?php if (isset($booking_data[$row->bookingID]['staff'])) {
								echo preg_replace('/(.+?),(.+?),/', '$1,$2,<br>', $booking_data[$row->bookingID]['staff']);
							} ?>
							</span>
						</td>
						<td>
							<?php
							if (!empty($row->department)) {
								echo $row->department;
							}
							?>
						</td>
						<td>
							<?php if (!empty($booking_data[$row->bookingID]['type_other'])) {
								echo $booking_data[$row->bookingID]['type_other'];
							} else if (!empty($booking_data[$row->bookingID]['session_types'])) {
								echo $booking_data[$row->bookingID]['session_types'];
							} ?>
						</td>
						<td>
							<?php if (!empty($booking_data[$row->bookingID]['activities_other'])) {
								echo $booking_data[$row->bookingID]['activities_other'];
							} else if (!empty($booking_data[$row->bookingID]['activities'])) {
								echo $booking_data[$row->bookingID]['activities'];
							} ?>
						</td>
						<?php if (isset($customer_hours[$row->orgID])) { ?>
							<td>
								<?php echo number_format($customer_hours[$row->orgID], 2); ?>
							</td>
						<?php } else { ?>
						<td>
							0
						</td>
						<?php } ?>
						<?php if (isset($costs[$row->bookingID]['hours'])) { ?>
							<td>
								<?php echo number_format($costs[$row->bookingID]['hours'], 2); ?>
							</td>
						<?php } else { ?>
							<td>
								0
							</td>
						<?php } ?>
						<?php if (isset($income[$row->bookingID]['total'])) { ?>
							<td>
								<?php echo number_format($income[$row->bookingID]['total'], 2); ?>
							</td>
						<?php } else { ?>
							<td>
								0
							</td>
						<?php } ?>
						<?php if (isset($costs[$row->bookingID]['total'])) { ?>
							<td>
								<?php echo number_format($costs[$row->bookingID]['total'], 2); ?>
							</td>
						<?php } else { ?>
							<td>
								0
							</td>
						<?php } ?>
						<?php if (isset($total_profit[$row->bookingID])) { ?>
							<td>
								<?php echo number_format($total_profit[$row->bookingID], 2); ?>
							</td>
						<?php } else { ?>
							<td>
								0
							</td>
						<?php } ?>
					</tr>
				<?php } ?>
				</tbody>
			</table>
		<?php } ?>
	</div>
</div>
<?php
}
