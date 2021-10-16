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
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; } ?>
<div class="subscription-message"></div>
<?php echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
if ($subs->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No subscriptions found.
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
							Project
						</th>
						<th>
							Name
						</th>
						<th>
							Frequency
						</th>
						<th>
							Price
						</th>
						<th>
							Sessions
						</th>
						<th>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($subs->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('bookings/edit/' . $row->bookingID, $row->name); ?>
							</td>
							<td>
								<?php echo $row->subName; ?>
							</td>
							<td>
								<?php echo ucfirst($row->frequency); ?>
							</td>

							<td>
								<?php echo currency_symbol() . number_format($row->price, 2); ?>
							</td>
							<td>
								<?php
								if(isset($session_types[$row->subID]) && count($session_types[$row->subID]) > 0) {
									$session_type_data = "";
									foreach ($session_types[$row->subID] as $session) {
										$session_type_data .= $session.", ";
									}
									echo rtrim(trim($session_type_data), ",");
								}?>
							</td>
							<td>
								<div class='text-right'>
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
										} ?>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('booking/cart/edit/'.$row->cartID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>

										<?php if ($row->status == 'inactive'){ ?>
											<a class='btn font-weight-bold btn-sm btn-success activate-sub' data-permanent="1" data-id="<?php echo $row->id; ?>" href='javascript:void(0);' title="Activate">
												<i class='far fa-check-circle'></i>
											</a>
										<?php } ?>
										<?php if ($row->status == 'active'){
											?><a class='btn btn-danger btn-sm inactive-sub' data-permanent="1" data-provider="<?php echo $row->payment_provider; ?>" data-id="<?php echo $row->id; ?>" href='javascript:void(0);' title="Cancel" data-csrf-name="<?php echo $this->security->get_csrf_token_name();?>" data-csrf-hash="<?php echo $this->security->get_csrf_hash();?>">
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
