<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
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
					<strong><label for="field_blockID">Block</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($block_list->num_rows() > 0) {
					foreach ($block_list->result() as $row) {
						$options[$row->blockID] = $row->name;
					}
				}
				echo form_dropdown('search_blockID', $options, $search_fields['blockID'], 'id="field_blockID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'cancellation' => 'Cancellation',
					'staffchange' => 'Staff Change'
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
		No exceptions found.
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<?php echo form_open(site_url($page_base)); ?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th>
								Date
							</th>
							<th>
								Block
							</th>
							<th>
								Type
							</th>
							<th>
								Staff
							</th>
							<th>
								Replacement
							</th>
							<th>
								Reason
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($exceptions->result() as $row) {
							?>
							<tr>
								<td class="name">
									<?php echo anchor('sessions/exceptions/edit/' . $row->exceptionID, mysql_to_uk_date($row->date)); ?>
								</td>
								<td>
									<?php echo $row->block; ?>
								</td>
								<td>
									<?php
									switch ($row->type) {
										case "cancellation":
											echo "Cancellation";
											break;
										case "staffchange":
											echo "Staff Change";
											break;
									}
									?>
								</td>
								<td>
									<?php
									if (!empty($row->fromID)) {
										echo $row->first . ' ' . $row->surname;
									}
									?>
								</td>
								<td>
									<?php
									if (!empty($row->staffID)) {
										echo $row->replacement_first . ' ' . $row->replacement_surname;
									} else if (empty($row->staffID) && $row->type == 'staffchange'){
										echo 'None Required';
									}
									?>
								</td>
								<td>
									<?php
									if ($row->reason_select == 'other') {
										echo $row->reason;
									} else {
										echo ucwords($row->reason_select);
									}
									?>
								</td>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('sessions/exceptions/edit/' . $row->exceptionID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete exception' href='<?php echo site_url('bookings/exceptions/remove/' . $row->exceptionID); ?>' title="Remove">
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
	<?php echo form_close(); ?>
	<?php
	echo $this->pagination_library->display($page_base);
}
