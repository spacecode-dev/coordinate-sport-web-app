<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'utilisation-report-search']); ?>
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
				$options = [];
				if ($staff_list->num_rows() > 0) {
					foreach ($staff_list->result() as $row) {
						$options[$row->staffID] = [
							'name' => $row->first . ' ' .$row->surname,
							'extras' => [
								'data-active="' . $row->active . '"'
							]
						];
					}
				}
				echo form_dropdown_advanced('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
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
					<strong><label for="field_job_title">Job Title</label></strong>
				</p>
				<?php
				$options = [
					'' => 'Select'
				];
				foreach ($job_titles as $title) {
					if (!empty($title)) {
						$options[$title] = $title;
					}
				}

				echo form_dropdown('search_job_title', $options, $search_fields['job_title'], 'id="field_job_title" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_permission_level">Permission Level</label></strong>
				</p>
				<?php
				$options = [
					'' => 'Select'
				];
				foreach ($permission_levels as $key => $title) {
					if (!empty($title)) {
						$options[$key] = $title;
					}
				}

				echo form_dropdown('search_permission_level', $options, $search_fields['permission_level'], 'id="field_permission_level" class="select2 form-control"');
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
if ($staff->num_rows() == 0) {
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
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th rowspan="2">
							Staff
						</th>
						<th colspan="<?php echo $brands->num_rows(); ?>">
							<?php echo $this->settings_library->get_label('brands'); ?>
						</th>
						<th rowspan="2">
							Total
						</th>
						<th rowspan="2">
							Of Which Provisional
						</th>
						<th rowspan="2">
							Salaried Hours
						</th>
						<th rowspan="2">
							Utilisation Target
						</th>
						<th rowspan="2">
							Actual Utilisation
						</th>
					</tr>
					<tr>
						<?php
						if ($brands->num_rows() > 0) {
							foreach ($brands->result() as $brand) {
								?><th><?php echo $brand->name; ?></th><?php
							}
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$column_totals = array();
					foreach ($staff->result() as $row) {
						$hours = 0;
						?>
						<tr>
							<td>
								<?php echo $row->first . ' ' . $row->surname; ?>
							</td>
							<?php
							if ($brands->num_rows() > 0) {
								foreach ($brands->result() as $brand) {
									$brand_hours = 0;
									if (isset($utilisation_data[$row->staffID][$brand->brandID])) {
										$brand_hours = $utilisation_data[$row->staffID][$brand->brandID];
									}
									$hours += $brand_hours;
									?><td><?php echo format_decimal_hours($brand_hours); ?></td><?php
									if (!array_key_exists($brand->brandID, $column_totals)) {
										$column_totals[$brand->brandID] = 0;
									}
									$column_totals[$brand->brandID] += $brand_hours;
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
							<td>
								<?php
								$provisional_hours = 0;
								if (isset($utilisation_data[$row->staffID]['provisional'])) {
									$provisional_hours = $utilisation_data[$row->staffID]['provisional'];
								}
								echo format_decimal_hours($provisional_hours);
								if (!array_key_exists('provisional', $column_totals)) {
									$column_totals['provisional'] = 0;
								}
								$column_totals['provisional'] += $provisional_hours;
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
								<?php echo $row->target_utilisation . '%';
								if (!array_key_exists('target_utilisation', $column_totals)) {
									$column_totals['target_utilisation'] = 0;
								}
								$column_totals['target_utilisation'] += $row->target_utilisation;
								?>
							</td>
							<td>
								<?php
								if ($row->target_hours > 0) {
									$utilisation = round(($hours/$row->target_hours)*100, 1);
								} else {
									$utilisation = 0;
								}
								if (!array_key_exists('utilisation', $column_totals)) {
									$column_totals['utilisation'] = 0;
								}
								$column_totals['utilisation'] += $utilisation;
								echo '<span class="label label-';
								if ($utilisation >= $row->target_utilisation) {
									echo 'green';
								} else {
									echo 'red';
								}
								echo '">' . $utilisation . '%</span>';
								?>
							</td>
						</tr>
						<?php
					}
					?>
					<tr>
						<td><strong>Totals</strong></td>
						<?php
						if ($brands->num_rows() > 0) {
							foreach ($brands->result() as $brand) {
								echo '<td>' . format_decimal_hours($column_totals[$brand->brandID]) . '</td>';
							}
						}
						?>
						<td><?php echo format_decimal_hours($column_totals['hours']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['provisional']); ?></td>
						<td><?php echo format_decimal_hours($column_totals['target_hours']); ?></td>
						<td><?php
						$target_utilisation = round($column_totals['target_utilisation']/$staff->num_rows(), 1);
						echo $target_utilisation . '%';
						?></td>
						<td><?php
						$utilisation = round($column_totals['utilisation']/$staff->num_rows(), 1);
						echo '<span class="label label-';
						if ($utilisation > $target_utilisation) {
							echo 'green';
						} else {
							echo 'red';
						}
						echo '">' . $utilisation . '%</span>';
						?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<p><small class='text-muted'>Excludes hours as observers and participants</small></p>
	<?php
}
