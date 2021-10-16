<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => '',
		'is_project' => $booking_info->project,
		'type' => $booking_info->type,
		'register_type' => $booking_info->register_type
	);
	$this->load->view('bookings/tabs.php', $data);
	$data['tab'] = 'history';
	$this->load->view('bookings/messaging-tabs.php', $data);
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
					<strong><label for="field_contact_id">To</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($contacts->num_rows() > 0) {
					foreach ($contacts->result() as $row) {
							$options[$row->contactID] = $row->name;
					}
				}
				echo form_dropdown('search_contact_id', $options, $search_fields['contact_id'], 'id="field_contact_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_message">Message</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_message',
					'id' => 'field_message',
					'class' => 'form-control',
					'value' => $search_fields['message']
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
if ($notifications->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No notifications found.
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
							To
						</th>
						<th>
							Subject
						</th>
						<th>
							Method
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($notifications->result() as $row) {
						?>
						<tr>
							<td>
								<?php echo mysql_to_uk_datetime($row->added); ?>
							</td>
							<td>
								<?php echo $row->name;
								if ($row->type == 'email' && !empty($row->destination)) {
									?> (<a href="mailto:<?php echo $row->destination; ?>"><?php echo $row->destination; ?></a>)<?php
								}
								?>
							</td>
							<td class="name">
								<?php
								if ($row->type == 'SMS') {
									$row->subject = $row->contentText;
									if (strlen($row->subject) > 50) {
										$row->subject = trim(substr($row->subject, 0, 47)) . '...';
									}
								}
								echo anchor('participants/notifications/view/' . $row->notificationID, $row->subject);
								?>
							</td>
							<td>
								<?php echo ucwords($row->type); ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-success btn-sm notification-slide-out-toggle' data-origin="<?php echo $row->origin; ?>" data-id="<?php echo $row->notificationID; ?>" href='javascript:void(0);' title="View">
										<i class='far fa-globe'></i>
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
	echo $this->pagination_library->display($page_base);
}?>
<!-- start::Notification Panel-->
<div id="view-notification-slide-out" class="offcanvas offcanvas-right booking p-5 slide-out-resizable">
	<div class="offcanvas-header d-flex justify-content-end mb-3 pl-5 pr-5 pt-5">
		<a href="javascript:void(0);" class="btn btn-xs btn-icon btn-primary" id="notification-slide-out-close">
			<i class="fas fa-times text-white"></i>
		</a>
	</div>
	<!--begin::Content-->
	<div class="offcanvas-content h-90 pl-5 pr-5 pr-5 ">
		<div class="spinner spinner-primary spinner-lg mt-5 spinner-center"></div>
	</div>
	<!--end::Content-->
</div>
<!-- end::User Panel-->

