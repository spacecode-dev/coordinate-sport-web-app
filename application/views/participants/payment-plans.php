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
				<strong><label for="field_amount">Total Amount (<?php echo currency_symbol(); ?>)</label></strong>
			</p>
			<?php
			$data = array(
				'name' => 'search_amount',
				'id' => 'field_amount',
				'class' => 'form-control',
				'value' => $search_fields['amount']
			);
			echo form_input($data);
			?>
		</div>
		<div class='col-sm-2'>
			<p>
				<strong><label for="field_contact_id">Contact</label></strong>
			</p>
			<?php
			$options = array(
				'' => 'Select'
			);
			if ($contacts->num_rows() > 0) {
				foreach ($contacts->result() as $row) {
					$options[$row->contactID] = $row->first_name . ' ' . $row->last_name;
				}
			}
			echo form_dropdown('search_contact_id', $options, $search_fields['contact_id'], 'id="field_contact_id" class="select2 form-control"');
			?>
		</div>
		<div class='col-sm-2'>
			<p>
				<strong><label for="field_note">Note</label></strong>
			</p>
			<?php
			$data = array(
				'name' => 'search_note',
				'id' => 'field_note',
				'class' => 'form-control',
				'value' => $search_fields['note']
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
if ($payments->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		<i class="far fa-info-circle"></i>
		No payment plans found.
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' >
				<thead>
				<tr>
					<th>
						Start Date
					</th>
					<th>
						Contact
					</th>
					<th>
						Total Amount
					</th>
					<th>
						Plan
					</th>
					<th>
						Status
					</th>
					<th>
						Note
					</th>
					<th></th>
				</tr>
				</thead>
				<tbody>
				<?php
				foreach ($payments->result() as $row) {
					?>
					<tr>
						<td class="name">
							<?php echo anchor('participants/payment-plans/view/' . $row->planID, mysql_to_uk_date($row->added)); ?>
						</td>
						<td>
							<?php echo $row->first_name . ' ' . $row->last_name; ?>
						</td>
						<td>
							<?php echo currency_symbol() . number_format($row->amount, 2); ?>
						</td>

						<td>
							<?php echo $row->interval_count . ' ' . ucwords($row->interval_unit) . 'ly Payments'; ?>
						</td>
						<td>
							<?php
							switch ($row->status) {
								case 'cancelled':
								default:
									$label_colour = 'danger';
									break;
								case 'inactive':
									$label_colour = 'warning';
									break;
								case 'active':
								case 'completed':
									$label_colour = 'success';
									break;
							}
							?>
							<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($row->status); ?></span>
						</td>
						<td>
							<?php echo $row->note; ?>
						</td>
						<td>
							<div class='text-right'>
								<a class='btn btn-success btn-sm' href='<?php echo site_url('participants/payment-plans/view/' . $row->planID); ?>' title="View">
									<i class='far fa-globe'></i>
								</a>
								<?php
								if ($valid_config === TRUE) {
									if (!empty($row->gc_subscription_id)) {
										$gocardless_url = 'https://manage';
										if ($this->settings_library->get('gocardless_environment') == 'sandbox') {
											$gocardless_url .= '-sandbox';
										}
										$gocardless_url .= '.gocardless.com/subscriptions/' . $row->gc_subscription_id;
										?><a class='btn btn-info btn-sm' href='<?php echo $gocardless_url; ?>' title="View Payment" target="_blank">
											<i class='far fa-sack-dollar'></i>
										</a> <?php
									}
									if (in_array($row->status, array('inactive', 'cancelled'))) {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('participants/payment-plans/remove/' . $row->planID); ?>' title="Delete">
											<i class='far fa-trash'></i>
										</a><?php
									} else if ($row->status != 'cancelled'){
										?><a class='btn btn-danger btn-sm confirm' href='<?php echo site_url('participants/payment-plans/cancel/' . $row->planID); ?>' title="Cancel" data-message="Are you sure you want to cancel this plan? This will stop any future payments.">
											<i class='far fa-ban'></i>
										</a><?php
									}
								}
								?>
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
}
