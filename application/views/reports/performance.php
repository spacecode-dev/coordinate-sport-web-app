<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'performance-report-search']); ?>
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
					<strong><label for="field_brand_id"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_teamleader_id">Team Leader</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($team_leaders->num_rows() > 0) {
					foreach ($team_leaders->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('search_teamleader_id', $options, $search_fields['teamleader_id'], 'id="field_teamleader_id" class="select2 form-control"');
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
					<strong><label for="field_exclude_non_delivery">Exclude Non-delivery Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_exclude_non_delivery', $options, $search_fields['exclude_non_delivery'], 'id="field_exclude_non_delivery" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_order_by">Order By</label></strong>
				</p>
				<?php
				$options = array(
					'performance' => 'Performance',
					'name' => 'Name'
				);
				echo form_dropdown('search_order_by', $options, $search_fields['order_by'], 'id="field_order_by" class="select2 form-control"');
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
if (count($staff_data) == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	?><div class='card'>
		<div class="fixed-scrollbar"></div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Staff
						</th>
						<th>
							<?php echo $this->settings_library->get_label('brand'); ?>
						</th>
						<th>
							Team Leader
						</th>
						<?php
						foreach ($columns as $key => $label) {
							?><th>
								<?php echo $label; ?>
							</th><?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($staff_data as $staffID => $row) {
						?>
						<tr>
							<?php
							foreach ($row as $val) {
								?><td><?php
								if (is_numeric($val) && $val < 0) {
									echo '<span class="text-red">';
								}
								echo $val;
								if (is_numeric($val) && $val < 0) {
									echo '</span>';
								}
								?></td><?php
							}
							?>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
