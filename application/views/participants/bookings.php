<?php
display_messages();
if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
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
					<strong><label for="field_child_id">Child</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($children->num_rows() > 0) {
					foreach ($children->result() as $row) {
						$options[$row->childID] = $row->first_name . ' ' . $row->last_name;
					}
				}
				echo form_dropdown('search_child_id', $options, $search_fields['child_id'], 'id="field_child_id" class="select2 form-control"');
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
if ($bookings->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No bookings found.
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
							Booking Date
						</th>
						<th>
							Participants
						</th>
						<th>
							Subscriptions
						</th>
						<th>
							Project Names
						</th>
						<th>
							Amount
						</th>
						<th>
							Balance Due
						</th>
						<th>
							Booked By
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($bookings->result() as $row) {
						?>
						<tr>
							<td>
								<a class='btn btn-success btn-sm mr-2 view-bookings-toggle' data-id='<?php echo $row->cartID;?>' href="javascript:void(0);" title="View">
									<i class='far fa-globe'></i>
								</a>
								<?php
								//href='<?php echo site_url('participants/bookings/view/' . $row->cartID);'
								echo mysql_to_uk_datetime($row->booked);
								?>
							</td>
							<td>
								<?php
								$participants = array_merge((array)explode(",", $row->child_names), (array)explode(",", $row->individual_names));
								$participants = array_filter($participants);
								sort($participants);
								echo implode(', ', $participants);
								?>
							</td>
							<td>
								<?php echo empty($row->subscriptions)?"None":preg_replace('/(?<!\d),|,(?!\d{3})/', ', ', ucfirst($row->subscriptions)); ?>
							</td>
							<td>
								<?php echo empty($row->project_name)?"None":preg_replace('/(?<!\d),|,(?!\d{3})/', ', ', ucfirst($row->project_name)); ?>
							</td>
							<td>
								<?php
								if ($row->total > 0) {
									echo currency_symbol() . number_format($row->total, 2);
								} else {
									echo 'Free';
								}
								if ($row->childcarevoucher_providerID > 0) {
									echo ' (Childcare Voucher)';
								}
								?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row->balance, 2); ?>
							</td>
							<td>
								<?php echo $row->contact_first . ' ' . $row->contact_last; ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('booking/cart/edit/' . $row->cartID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('participants/bookings/remove/' . $row->cartID); ?>' title="Remove">
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
	<!-- begin::User Panel-->
	<div id="view-bookings" class="offcanvas offcanvas-right pl-15 p-10 booking slide-out-resizable">
		<div class="offcanvas-header pr-5 mr-n5 mb-3">
			<a href="javascript:void(0);" class="btn btn-icon btn-custom btn-circle float-right btn-sm" id="view-bookings-toggle-close">
				<i class="fas fa-times text-white"></i>
			</a>
		</div>
		<!--begin::Content-->
		<div class="offcanvas-content pr-5 mr-n5">
			<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>
		</div>
		<!--end::Content-->
	</div>
	<!-- end::User Panel-->
	<?php
	echo $this->pagination_library->display($page_base);
}
