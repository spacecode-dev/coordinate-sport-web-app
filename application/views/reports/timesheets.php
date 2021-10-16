<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'timesheets-report-search']); ?>
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
				if (count($staff_list) > 0) {
					foreach ($staff_list as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_department">Permission Level</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'directors' => $this->settings_library->get_permission_level_label('directors'),
					'management' => $this->settings_library->get_permission_level_label('management'),
					'office' => $this->settings_library->get_permission_level_label('office'),
					'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
					'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
					'coaching' => $this->settings_library->get_permission_level_label('coaching')
				);
				echo form_dropdown('search_department', $options, $search_fields['department'], 'id="field_department" class="select2 form-control"');
				?>
			</div>
		</div>
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_filter_by">Filter By</label></strong>
				</p>
				<?php
				foreach ($filter_by as $field => $name) {
					$data = array(
						'name' => 'filter_by_' . $field,
						'value' => 1,
						'id' => 'filter_by_' . $field . '_id'
					);
					if ($search_fields['filter_by_' . $field] == 1) {
						$data['checked'] = true;
					}

					?>
					<div>
						<?php echo form_checkbox($data); ?>
						<label for="filter_by_<?= $field ?>_id">
							<?= $name ?>
						</label>
					</div>
				<?php }
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
$has_results = FALSE;
if (count($staff) > 0) {
	foreach ($staff as $row) {
		// check if has data for this staff member
		if (array_key_exists($row->staffID, $timesheet_data) || array_key_exists($row->staffID, $expense_data)) {
			$has_results = TRUE;
			break;
		}
	}
}
if ($has_results !== TRUE || (count($timesheet_data) == 0 && count($expense_data) == 0)) {
	?>
	<div class="alert alert-info">
		<i class="far fa-info-circle"></i>
		No data found.
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card'>
	<div class="fixed-scrollbar"></div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th rowspan="2">
							Staff
						</th>
						<th rowspan="2">
							Payroll Number
						</th>
						<th rowspan="2">
							Permission Level
						</th>
						<?php if($search_fields['filter_by_brand']) : ?>
						<th colspan="<?php echo count($brands); ?>">
							<?php echo $this->settings_library->get_label('brands'); ?>
						</th>
						<?php endif; ?>
						<?php if($search_fields['filter_by_activity']) : ?>
						<th colspan="<?php echo count($activities); ?>">
							Activities
						</th>
						<?php endif; ?>
						<?php if($search_fields['filter_by_role']) : ?>
						<th colspan="<?php echo count($roles); ?>">
							Role
						</th>
						<?php endif; ?>
						<th colspan="2">
							Pay Types
						</th>
						<th rowspan="2">
							Total
						</th>
						<th rowspan="2">
							Target Salaried Hours
						</th>
						<th rowspan="2">
							Hours Up/Down
						</th>
						<?php
						if ($this->auth->has_features('expenses')) {
							?><th rowspan="2">
								Expenses
							</th><?php
						}
						?>
						<?php if($mileage_section == 1){
							?><th rowspan="2">
								Mileage Cost
							</th><?php
						}
						?>
					</tr>
					<tr>
						<?php
						if (count($brands) > 0 && $search_fields['filter_by_brand']) {
							foreach ($brands as $brand) {
								?><th><?php echo $brand->name; ?></th><?php
							}
						}
						if (count($activities) > 0 && $search_fields['filter_by_activity']) {
							foreach ($activities as $activity) {
								?><th><?php echo $activity->name; ?></th><?php
							}
						}
						if (count($roles) > 0 && $search_fields['filter_by_role']) {
							foreach ($roles as $key => $label) {
								?><th><?php echo $label; ?></th><?php
							}
						}
						?>
						<th>Salaried</th>
						<th>Non-Salaried</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$column_totals = [
						'activities' => []
					];

					foreach ($staff as $row) {
						// skip if no data
						if (!array_key_exists($row->staffID, $timesheet_data) && !array_key_exists($row->staffID, $expense_data)) {
							continue;
						}
						$hours = 0;
						?>
						<tr>
							<td>
								<?php echo $row->first . ' ' . $row->surname; ?>
							</td>
							<td>
								<?php echo $row->payroll_number; ?>
							</td>
							<td>
								<?php echo $this->settings_library->get_permission_level_label($row->department); ?>
							</td>
							<?php
							if (count($brands) > 0 && $search_fields['filter_by_brand']) {
								foreach ($brands as $brand) {
									$brand_hours = 0;
									if (isset($timesheet_data[$row->staffID][$brand->brandID])) {
										$brand_hours = $timesheet_data[$row->staffID][$brand->brandID];
									}
									$hours += $brand_hours;
									?><td><?php echo format_decimal_hours($brand_hours); ?></td><?php
									if (!array_key_exists($brand->brandID, $column_totals)) {
										$column_totals[$brand->brandID] = 0;
									}
									$column_totals[$brand->brandID] += $brand_hours;
								}
							}
							if (count($activities) > 0 && $search_fields['filter_by_activity']) {
								foreach ($activities as $activity) {
									$activity_hours = 0;
									if (isset($timesheet_data[$row->staffID]['activity'][$activity->activityID])) {
										$activity_hours = $timesheet_data[$row->staffID]['activity'][$activity->activityID];
									}
									?><td><?php echo format_decimal_hours($activity_hours); ?></td><?php
									if (!array_key_exists($activity->activityID, $column_totals['activities'])) {
										$column_totals['activities'][$activity->activityID] = 0;
									}
									$column_totals['activities'][$activity->activityID] += $activity_hours;
								}
							}
							if (count($roles) > 0 && $search_fields['filter_by_role']) {
								foreach ($roles as $key => $label) {
									$role_hours = 0;
									if (isset($timesheet_data[$row->staffID][$key])) {
										$role_hours = $timesheet_data[$row->staffID][$key];
									}
									?><td><?php echo format_decimal_hours($role_hours); ?></td><?php
									if (!array_key_exists($key, $column_totals)) {
										$column_totals[$key] = 0;
									}
									$column_totals[$key] += $role_hours;
								}
							}
							?>

							<?php
							$salaried_hours = 0;
							if (isset($salaried[$row->staffID])) {
								$salaried_hours = $salaried[$row->staffID];
							}
							?><td><?php echo format_decimal_hours($salaried_hours); ?></td><?php
							if (!array_key_exists("salaried", $column_totals)) {
								$column_totals["salaried"] = 0;
							}
							$column_totals["salaried"] += $salaried_hours;
							?>


							<?php
							$nonsalaried_hours = 0;
							if (isset($nonsalaried[$row->staffID])) {
								$nonsalaried_hours = $nonsalaried[$row->staffID];
							}
							?><td><?php echo format_decimal_hours($nonsalaried_hours); ?></td><?php
							if (!array_key_exists("nonsalaried", $column_totals)) {
								$column_totals["nonsalaried"] = 0;
							}
							$column_totals["nonsalaried"] += $nonsalaried_hours;
							?>
							<td>
								<?php
								echo format_decimal_hours($hours);
								if (!array_key_exists('hours', $column_totals)) {
									$column_totals['hours'] = 0;
								}
								$column_totals['hours'] += $hours;
								?>
							</td>
							<td>
								<?php
								$row->target_hours = ($row->target_hours/7)*$days;
								echo format_decimal_hours($row->target_hours);
								if (!array_key_exists('target_hours', $column_totals)) {
									$column_totals['target_hours'] = 0;
								}
								$column_totals['target_hours'] += $row->target_hours;
								?>
							</td>
							<td>
								<?php
								$hours_diff = $hours - $row->target_hours;
								echo format_decimal_hours($hours_diff);
								if (!array_key_exists('hours_diff', $column_totals)) {
									$column_totals['hours_diff'] = 0;
								}
								$column_totals['hours_diff'] += $hours_diff;
								?>
							</td>
							<?php
							if ($this->auth->has_features('expenses')) {
								?><td>
									<?php
									$expenses = 0;
									if (isset($expense_data[$row->staffID])) {
										$expenses = $expense_data[$row->staffID];
									}
									echo currency_symbol() . number_format($expenses, 2);
									if (!array_key_exists('expenses', $column_totals)) {
										$column_totals['expenses'] = 0;
									}
									$column_totals['expenses'] += $expenses;
									?>
								</td><?php
							}
							?>
							<?php
							if($mileage_section == 1){
							?>
								<td>
									<?php
									$mileage = 0;
									if (isset($mileage_data[$row->staffID])) {
										$mileage = $mileage_data[$row->staffID];
									}
									echo  currency_symbol() .number_format($mileage, 2);
									if (!array_key_exists('mileage', $column_totals)) {
										$column_totals['mileage'] = 0;
									}
									$column_totals['mileage'] += $mileage;
									?>
								</td>
							<?php } ?>
						</tr>
						<?php
					}
					?>
					<tr>
						<td colspan="3"><strong>Totals</strong></td>
						<?php
						if (count($brands) > 0 && $search_fields['filter_by_brand']) {
							foreach ($brands as $brand) {
								echo '<td>' . format_decimal_hours($column_totals[$brand->brandID]) . '</td>';
							}
						}
						if (count($activities) > 0 && $search_fields['filter_by_activity']) {
							foreach ($activities as $activity) {
								echo '<td>' . format_decimal_hours($column_totals['activities'][$activity->activityID]) . '</td>';
							}
						}
						if (count($roles) > 0 && $search_fields['filter_by_role']) {
							foreach ($roles as $key => $label) {
								echo '<td>' . format_decimal_hours($column_totals[$key]) . '</td>';
							}
						}
						?>
						<td><?php echo format_decimal_hours($column_totals['salaried']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['nonsalaried']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['hours']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['target_hours']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['hours_diff']); ?></td>
						<?php
						if ($this->auth->has_features('expenses')) {
							?><td><?php echo currency_symbol() . number_format($column_totals['expenses'], 2); ?></td><?php
						}
						?>
						<?php
						if($mileage_section == 1){
						?>
						<td><?php echo currency_symbol() .number_format($column_totals['mileage'], 2); ?></td>
						<?php } ?>
					</tr>
				</tbody>
			</table>
		</div>
	</div><?php
}
