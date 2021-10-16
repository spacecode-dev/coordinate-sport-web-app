<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'activities-report-search']); ?>
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
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_job_title">Job Title</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_job_title',
					'id' => 'field_job_title',
					'class' => 'form-control',
					'value' => $search_fields['job_title']
				);
				echo form_input($data);
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
if ($row_data->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?>
	<div class='card'>
		<div class="fixed-scrollbar"></div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th rowspan="2">
							Staff
						</th>
						<?php
						foreach ($activities as $label) {
							?><th><?php echo $label; ?></th><?php
						}
						?>
						<th rowspan="2">
							Total
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$column_totals = array();
					foreach ($row_data->result() as $row) {
						$hours = 0;
						?><tr>
							<td>
								<?php echo $row->first . ' ' . $row->surname; ?>
							</td>
							<?php
							if (count($activities) > 0) {
								foreach ($activities as $activityID => $label) {
									$actviity_hours = 0;
									if (isset($report_data[$row->staffID][$activityID])) {
										$actviity_hours = $report_data[$row->staffID][$activityID];
									}
									$hours += $actviity_hours;
									?><td><?php echo format_decimal_hours($actviity_hours); ?></td><?php
									if (!array_key_exists($activityID, $column_totals)) {
										$column_totals[$activityID] = 0;
									}
									$column_totals[$activityID] += $actviity_hours;
								}
							}
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
						</tr><?php
					}
					?>
					<tr>
						<td><strong>Totals</strong></td>
						<?php
						if (count($activities) > 0) {
							foreach ($activities as $activityID => $label) {
								echo '<td>' . format_decimal_hours($column_totals[$activityID]) . '</td>';
							}
						}
						?><td>
							<?php echo format_decimal_hours($column_totals['hours']); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<p><small class='text-muted'>Excludes hours as observers and participants</small></p>
	<?php
}
