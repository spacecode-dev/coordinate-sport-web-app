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
if (count($approvals) == 0) {
	?>
	<div class="alert alert-info">
		No pending approvals found.
	</div>
	<?php
} else {
	$form_action = 'finance/approvals';
	if ($show_all !== TRUE) {
		$form_action .= '/own';
	}
	echo form_open($form_action, 'id="approvals"');
	$hidden_fields = array(
		'bulk' => 1
	);
	echo form_hidden($hidden_fields);
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes' id="approvals">
				<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
						<th>
							Date
						</th>
						<th>
							Staff
						</th>
						<th>
							<?php echo $this->settings_library->get_label('customer'); ?>
						</th>
						<th>
							<?php echo $this->settings_library->get_label('brand'); ?>
						</th>
						<th>
							Activity
						</th>
						<th>
							Details
						</th>
						<?php
						if ($show_all === TRUE) {
							?><th>
								Approver
							</th><?php
						}
						?>
						<th>
							Reason
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($approvals as $row) {
						?><tr <?php
						if (isset($row->reason) && $row->reason == 'travel' && $row->type == 'item') {
							if (isset($row->postcode) && !empty($row->postcode)) {
								echo ' data-postcode="' . $row->postcode . '"';
							}
							// check for timesheet item before/after
							if (isset($row->prev_postcode) && !empty($row->prev_postcode)) {
								echo ' data-postcode-prev="' . $row->prev_postcode . '"';
							} else {
								// assume home postcode
								echo ' data-postcode-prev="' . $row->staff_postcode . '"';
							}
							if (isset($row->next_postcode) && !empty($row->next_postcode)) {
								echo ' data-postcode-next="' . $row->next_postcode . '"';
							} else {
								// assume home postcode
								echo ' data-postcode-next="' . $row->staff_postcode . '"';
							}
							echo 'data-date="' . mysql_to_uk_date($row->date) . '"';
							echo 'data-start_time="' . substr($row->start_time, 0, 5) . '"';
							echo 'data-end_time="' . substr($row->end_time, 0, 5) . '"';
						}
						?>>
							<td class="center">
								<input name="selected_approvals[<?php echo $row->itemID; ?>]" value="<?php echo $row->type; ?>"<?php if (array_key_exists($row->itemID, $selected_approvals)) { echo " checked=\"checked\""; } ;?> type="checkbox" />
							</td>
							<td class="name">
								<?php if($row->type == 'fuel_card'){ echo "Week Commencing ".mysql_to_uk_date($row->timesheet_date); }
									else{ echo mysql_to_uk_date($row->date); } ?>
							</td>
							<td>
								<?php echo $row->staff_first . ' ' . $row->staff_last; ?>
							</td>
							<td>
								<?php if ($row->type === 'item' || $row->type === 'expense') {
									echo $row->venue;
								}else{
									echo "-";
								}
								?>
							</td>
							<td>
								<?php if ($row->type === 'item' || $row->type === 'expense') { ?>
									<span class="label label-inline" style="<?php echo label_style($row->brand_colour); ?>"><?php echo $row->brand; ?></span>
								<?php } else{ 
									echo "-";
								} ?>
							</td>
							<td>
								<?php
								if ($row->type === 'item') {
									if (!empty($row->activity)) {
										echo $row->activity;
									} else {
										echo 'Other/Unknown';
									}
								} else {
									echo '-';
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->type) {
									case 'item':
										echo '<strong>Start:</strong> ';
										if (!empty($row->original_start_time) && $row->original_start_time != $row->start_time) {
											echo '<span class="old_value">' . substr($row->original_start_time, 0, 5) . '</span> ';
										}
										echo substr($row->start_time, 0, 5);
										echo '<br /><strong>End:</strong> ';
										if (!empty($row->original_end_time) && $row->original_end_time != $row->end_time) {
											echo '<span class="old_value">' . substr($row->original_end_time, 0, 5) . '</span> ';
										}
										echo substr($row->end_time, 0, 5);
										break;
									case 'expense':
										echo '<strong>Item:</strong> ' . $row->item;
										echo '<br /><strong>Amount:</strong> ' . currency_symbol() . number_format($row->amount, 2);
										echo '<br /><strong>Receipt:</strong> ';
										if (!empty($row->receipt_path)) {
											echo anchor('attachment/expense/' . $row->receipt_path, 'View', 'target="_blank"');
										} else {
											echo 'No receipt';
										}
										break;
									case 'mileage':
										echo '<strong>Total Mileage:</strong>' . number_format($row->total_mileage, 2);
										echo '<br /><strong>Total Cost:</strong> '. currency_symbol(). number_format($row->total_cost, 2);
										break;
									case 'fuel_card':
										echo '<strong>Start Mileage:</strong> ' . number_format($row->start_mileage, 2);
										echo '<br /><strong>End Mileage:</strong> '. number_format($row->end_mileage, 2);
										echo '<br /><strong>Attachment:</strong> ';
										if (!empty($row->receipt_path)) {
											echo anchor('attachment/fuelcard/' . $row->receipt_path, 'View', 'target="_blank"');
										} else {
											echo 'No Attachment';
										}
										break;
										
								}
								?>
							</td>
							<?php
							if ($show_all === TRUE) {
								?><td>
									<?php echo $row->approver_first . ' ' . $row->approver_last; ?>
								</td><?php
							}
							?>
							<td>
								<?php
								echo ucwords($row->reason);
								if (!empty($row->reason_desc)) {
									echo ': ' . $row->reason_desc;
								}
								if ($row->reason == 'travel') {
									?><span class="travel_from"></span><span class="travel_to"></span><?php
								}
								?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									if ($this->auth->user->department != 'headcoach') {
										?><a class='btn btn-info btn-sm' href='<?php echo site_url('timesheets/view/' . $row->timesheetID . '/' . mysql_to_uk_date($row->date)); ?>' title="View Timesheet">
											<i class='far fa-clock'></i>
										</a><?php
									}
									?>
									<a class='btn btn-success btn-sm confirm' href='<?php echo site_url('timesheets/approve/' . $row->type . '/' . $row->itemID); ?>' title="Approve">
										<i class='far fa-check'></i>
									</a>
								   <a class='btn btn-danger btn-sm confirm' href='<?php echo site_url('timesheets/decline/' . $row->type . '/' . $row->itemID); ?>' title="Decline">
										<i class='far fa-trash'></i>
									</a>
								</div>
							</td>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="col-sm-2">
			<?php
			$options = array(
				'' => 'Select Action',
				'approve' => 'Approve',
				'decline' => 'Decline'
			);

			if (!array_key_exists($action, $options)) {
				$action = NULL;
			}
			echo form_dropdown('action', $options, $action, 'id="action" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2">
			<button class='btn btn-primary btn-submit' type="submit">
				Go
			</button>
		</div>
	<?php
	echo form_close();
}
