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
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'booking' => 'Booking',
					'blocks' => 'Blocks',
					'contract pricing' => 'Contract Pricing',
					'participants per session' => $this->settings_library->get_label('participants') . ' Per Session',
					'participants per block' => $this->settings_library->get_label('participants') . ' Per Block',
					'other' => 'Other'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
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
if ($invoices->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No invoices found. Do you want to <?php echo anchor('bookings/finances/invoices/'.$bookingID.'/new/', 'create one'); ?>?
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
							Invoice Number
						</th>
						<th>
							Invoice Date
						</th>
						<th>
							Type
						</th>
						<th>
							Amount
						</th>
						<th>
							Invoiced
						</th>
						<th>
							Note
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($invoices->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php echo anchor('bookings/finances/invoices/edit/' . $row->invoiceID, $row->invoiceNumber); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->invoiceDate); ?>
							</td>
							<td>
								<?php
								echo ucwords($row->type);
								if (array_key_exists($row->invoiceID, $blocks)) {
									$block_list = array();
									foreach ($blocks[$row->invoiceID] as $block_name) {
										$block_list[] = $block_name;
									}
									sort($block_list);
									if (count($block_list) > 0) {
										echo ' (' . implode(', ', $block_list) . ')';
									}
								}
								?>
							</td>
							<td>
								<?php echo currency_symbol() . $row->amount; ?>
							</td>
							<td class="has_icon">
								<?php
								if($row->is_invoiced == 1) {
									?><a href='<?php echo site_url('bookings/finances/invoices/uninvoice/' . $row->invoiceID); ?>' class='btn btn-success btn-sm' title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a href='<?php echo site_url('bookings/finances/invoices/invoice/' . $row->invoiceID); ?>' class='btn btn-danger btn-sm' title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<?php echo $row->note; ?>
							</td>
							<td>
								<div class='text-right'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('bookings/finances/invoices/edit/' . $row->invoiceID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/finances/invoices/remove/' . $row->invoiceID); ?>' title="Remove">
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
	echo $this->pagination_library->display($page_base);
}
