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
					<strong><label for="field_amount">Amount (<?php echo currency_symbol(); ?>)</label></strong>
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
					<strong><label for="field_transaction_ref">Trans. Ref.</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_transaction_ref',
					'id' => 'field_transaction_ref',
					'class' => 'form-control',
					'value' => $search_fields['transaction_ref']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_method">Method</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'card' => 'Credit/Debit Card',
					'cash' => 'Cash',
					'cheque' => 'Cheque',
					'online' => 'Online',
					'direct debit' => 'Direct Debit',
					'childcare voucher' => 'Childcare Voucher',
					'other' => 'Other'
				);
				echo form_dropdown('search_method', $options, $search_fields['method'], 'id="field_method" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_contact_id">From</label></strong>
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
		No payments found. Do you want to <?php echo anchor('participants/payments/'.$familyID.'/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
		<div class='scrollable-area'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Amount
						</th>
						<th>
							Received From
						</th>
						<th>
							Payment Method
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
								<?php echo anchor('participants/payments/edit/' . $row->paymentID, mysql_to_uk_datetime($row->added)); ?>
							</td>
							<td>
								<?php echo currency_symbol() . number_format($row->amount, 2); ?>
							</td>
							<td>
								<?php
								if ($row->internal == 1) {
									echo 'Internal';
									if (!empty($row->staff_first) && !empty($row->staff_last)) {
										echo ' (' . $row->staff_first . ' ' . $row->staff_last . ')';
									}
								} else {
									if(empty($row->staff_first)){
										echo $row->first_name . ' ' . $row->last_name;
									}else {
										echo $row->first_name . ' ' . $row->last_name . ' (' . $row->staff_first . ' ' . $row->staff_last . ')';
									}
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->method) {
									case 'card':
										echo 'Credit/Debit Card';
										break;
									default:
										echo ucwords($row->method);
										break;
								}
								if (!empty($row->reference)) {
									echo '<br />Provider: '.$row->reference;
								}
								if (!empty($row->transaction_ref)) {
									echo '<br />Ref: ' . $row->transaction_ref;
								}
								?>
							</td>
							<td>
								<?php echo $row->note; ?>
							</td>
							<td>
								<div class='text-right'>
									<?php
									$class = ""; $title ='Edit';$link = site_url('participants/payments/edit/' . $row->paymentID);
									if(($row->method == "online" || $row->method == "direct debit") && empty($row->byID)){
										$link = "javascript:void(0);";
										$title = "This payment cannot be changed, as it was made through the online bookings site";
										$class = "btn-dark-warning";
									}
									?>
									<a class='btn btn-warning btn-sm <?php echo $class;?>' href='<?php echo $link ; ?>' title="<?php echo $title;?>">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($row->locked == 1) {
										?><span class='btn btn-danger btn-sm no-action'>
											<i class='far fa-lock'></i>
										</span><?php
									} else {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('participants/payments/remove/' . $row->paymentID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a><?php
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
