<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
if ($timesheets->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No timesheets found. Do you want to <?php echo anchor($generate_url, 'generate some'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Timesheets
						</th>
						<th>
							Unsubmitted
						</th>
						<th>
							Submitted
						</th>
						<th>
							Approved
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($timesheets->result() as $row) {
						$timesheet_statuses = array(
							'unsubmitted' => 0,
							'submitted' => 0,
							'approved' => 0
						);
						$statuses = explode(",", $row->timesheet_statuses);
						if (count($statuses) > 0) {
							foreach ($statuses as $status) {
								$status = trim($status);
								if (array_key_exists($status, $timesheet_statuses)) {
									$timesheet_statuses[$status]++;
								}
							}
						}
						?>
						<tr>
							<td class="name">
								<?php echo anchor('timesheets/date/' . $row->date, mysql_to_uk_date($row->date)); ?>
							</td>
							<td>
								<?php echo intval($row->timesheet_count); ?>
							</td>
							<td>
								<?php echo intval($timesheet_statuses['unsubmitted']); ?>
							</td>
							<td>
								<?php echo intval($timesheet_statuses['submitted']); ?>
							</td>
							<td>
								<?php echo intval($timesheet_statuses['approved']); ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-success btn-sm' href='<?php echo site_url('timesheets/date/' . $row->date); ?>' title="View">
										<i class='far fa-arrow-right'></i>
									</a>
								</div>
							</td>
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
echo $this->pagination_library->display($page_base);
