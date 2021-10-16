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
					<strong><label for="field_brandID"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('search_brandID', $options, $search_fields['brandID'], 'id="field_brandID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_orgID">Customer</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($orgs->num_rows() > 0) {
					foreach ($orgs->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('search_orgID', $options, $search_fields['orgID'], 'id="field_orgID" class="select2 form-control"');
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
					<strong><label for="field_fromID">Staff</label></strong>
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
				echo form_dropdown('search_fromID', $options, $search_fields['fromID'], 'id="field_fromID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_completed">Completed</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_completed', $options, $search_fields['completed'], 'id="field_completed" class="select2 form-control"');
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
								<?php echo $this->settings_library->get_label('brand'); ?>
							</th>
							<th>
								Booking
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
									<span class="label label-inline" style="<?php echo label_style($row->brand_colour); ?>"><?php echo $row->brand; ?></span>
								</td>
								<td>
									<?php
									switch ($row->booking_type) {
										case "booking":
											echo anchor('bookings/edit/' . $row->bookingID, $row->org);
											break;
										case "event":
											echo $row->event;
											break;
									}
									?>
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
									} else if (empty($row->staffID) && $row->type == 'staffchange') {
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
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/exceptions/remove/' . $row->exceptionID); ?>/true' title="Remove">
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
		</div
	<?php echo form_close(); ?>
	<?php
	echo $this->pagination_library->display($page_base);
}
