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
							Name
						</th>
						<th>
							Total Time
						</th>
						<?php
						if ($this->auth->has_features('expenses')) {
							?><th>
								Total Expenses
							</th><?php
						}
						?>
						<?php if($mileage_section == 1){ ?>
						<th>
							Total Mileage
						</th>
						<?php } ?>
						<th>
							Status
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($timesheets->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('timesheets/view/' . $row->timesheetID, $row->first . ' ' . $row->surname); ?>
							</td>
							<td>
								<?php echo substr($row->total_time, 0, 5); ?>
							</td>
							<?php
							if ($this->auth->has_features('expenses')) {
								?><td>
									<?php echo currency_symbol() . number_format($row->total_expenses, 2); ?>
								</td><?php
							}
							?>
							<?php if($mileage_section == 1){ 
								$subtotal_mileage = isset($mileageArray[$row->timesheetID])?($mileageArray[$row->timesheetID]):0;
								$cnt  = isset($dateCount[$row->timesheetID])?count(array_unique($dateCount[$row->timesheetID])):0;
								$exclude_mileages = 0;
								if($row->mileage_activate_fuel_cards == 1 && $exclude_mileage != NULL)
									$exclude_mileages = $exclude_mileage;
								else if($row->mileage_activate_fuel_cards != 1 && $excluded_mileage_without_fuel_card != NULL)
									$exclude_mileages = $excluded_mileage_without_fuel_card;
								if($subtotal_mileage != 0){
									$total_mileage = $subtotal_mileage - ($cnt * $exclude_mileages);
								}else{
									$total_mileage = 0.00;
								}
							?>
							<td> <?php echo number_format($total_mileage,2); ?> mi</td>
							<?php } ?>
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
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('timesheets/view/' . $row->timesheetID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('timesheets/remove/' . $row->timesheetID); ?>' title="Remove">
										<i class='far fa-trash'></i>
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
