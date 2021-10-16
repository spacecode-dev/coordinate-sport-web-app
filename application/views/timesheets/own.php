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
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_status">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'unsubmitted' => 'Unsubmitted',
					'submitted' => 'Submitted',
					'approved' => 'Approved'
				);
				echo form_dropdown('search_status', $options, $search_fields['status'], 'id="field_status" class="select2 form-control"');
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
		No timesheets found.
	</div>
	<?php
} else {
	echo form_open('finance/timesheets/own', 'id="timesheets"');
	echo form_hidden('invoice', 'true');
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes'>
				<thead>
					<tr>
						<?php
						if ($this->auth->has_features('staff_invoices')) {
							?><th class="bulk-checkbox">
								<input type="checkbox" />
							</th><?php
						}
						?>
						<th>
							Date
						</th>
						<th>
							Total Time
						</th>
						<th>
							Total Expenses
						</th>
						<th>
							Status
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($timesheets->result() as $row) {
						?><tr>
							<?php
							if ($this->auth->has_features('staff_invoices')) {
								?><td class="center"><?php
									if ($row->invoiced != 1 && $row->status == 'approved') {
										?><input name="timesheets[]" value="<?php echo $row->timesheetID; ?>" type="checkbox" /><?php
									}
								?></td><?php
							}
							?>
							<td class="name">
								<?php echo anchor('timesheets/view/' . $row->timesheetID, mysql_to_uk_date($row->date)); ?>
							</td>
							<td>
								<?php echo substr($row->total_time, 0, 5); ?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row->total_expenses, 2); ?>
							</td>
							<td>
								<?php
								switch ($row->status) {
									case 'unsubmitted':
									default:
										$label_colour = 'danger';
										break;
									case 'submitted':
										$label_colour = 'warning';
										break;
									case 'approved':
										$label_colour = 'success';
										break;
								}
								?>
								<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($row->status); ?></span>
							</td>
							<td>
								<div class='text-right'>
									<?php
									if ($row->status == 'unsubmitted') {
										?><a class='btn btn-warning btn-sm' href='<?php echo site_url('timesheets/view/' . $row->timesheetID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a><?php
									} else {
										?><a class='btn btn-success btn-sm' href='<?php echo site_url('timesheets/view/' . $row->timesheetID); ?>' title="View">
											<i class='far fa-arrow-right'></i>
										</a><?php
									}
									?>
								</div>
							</td>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
	if ($this->auth->has_features('staff_invoices')) {
		?>
		<div class="row pb-5">
			<div class="col-sm-2">
				<button class='btn btn-primary btn-submit' type="submit">
					Invoice Selected
				</button>
			</div>
		</div><?php
	}
	echo form_close();
}
echo $this->pagination_library->display($page_base);
