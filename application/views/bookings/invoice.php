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
echo form_open_multipart($submit_to, 'id="invoice" data-booking="' . $bookingID . '"');
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details <small>Invoice Frequency: <?php
			if (!empty($org_info->invoiceFrequency)) {
				echo ucwords($org_info->invoiceFrequency);
			} else {
				echo 'Not Set';
			}
			?></small></h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Invoice Number <em>*</em>', 'invoiceNumber');
					$invoiceNumber = NULL;
					if (isset($invoice_info->invoiceNumber)) {
						$invoiceNumber = $invoice_info->invoiceNumber;
					}
					$data = array(
						'name' => 'invoiceNumber',
						'id' => 'invoiceNumber',
						'class' => 'form-control',
						'value' => set_value('invoiceNumber', $this->crm_library->htmlspecialchars_decode($invoiceNumber), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Invoice Date <em>*</em>', 'invoiceDate');
					$invoiceDate = NULL;
					if (isset($invoice_info->invoiceDate)) {
						$invoiceDate = mysql_to_uk_date($invoice_info->invoiceDate);
					}
					$data = array(
						'name' => 'invoiceDate',
						'id' => 'invoiceDate',
						'class' => 'form-control datepicker',
						'value' => set_value('invoiceDate', $this->crm_library->htmlspecialchars_decode($invoiceDate), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Type <em>*</em>', 'type');
					$type = NULL;
					if (isset($invoice_info->type)) {
						$type = $invoice_info->type;
					}
					$options = array(
						'' => 'Select',
						'booking' => 'Booking',
						'blocks' => 'Blocks',
						'contract pricing' => 'Contract Pricing',
						'participants per session' => $this->settings_library->get_label('participants') . ' Per Session',
						'participants per block' => $this->settings_library->get_label('participants') . ' Per Block',
						'other' => 'Other'
					);
					echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Amount <em>*</em>', 'field_amount');
					$amount = NULL;
					if (isset($invoice_info->amount)) {
						$amount = $invoice_info->amount;
					}
					$data = array(
						'name' => 'amount',
						'id' => 'field_amount',
						'class' => 'form-control',
						'value' => set_value('amount', $this->crm_library->htmlspecialchars_decode($amount), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
					?></div>
				<div class='form-group'><?php
					echo form_label('Note', 'field_note');
					$note = NULL;
					if (isset($invoice_info->note)) {
						$note = $invoice_info->note;
					}
					$data = array(
						'name' => 'note',
						'id' => 'field_note',
						'class' => 'form-control',
						'value' => set_value('note', $this->crm_library->htmlspecialchars_decode($note), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
					?></div>
				<div class="form-group">
					<?php
					echo form_label('Block(s) <em>*</em>', 'blocks');
					foreach ($blocks as $blockID => $details) {
						$data = array(
							'name' => 'blocks[]',
							'value' => $blockID
						);

						if ($this->input->post()) {
							$blocks_array = $this->input->post('blocks');
						}
						if (!is_array($blocks_array)) {
							$blocks_array = array();
						}
						if (in_array($blockID, $blocks_array)) {
							$data['checked'] = TRUE;
						}
						$data['data-type_count'] = intval($details['type_count']);
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								<?php echo $details['label']; ?>
								<span></span>
							</label>
						</div><?php
					}
					?>
				</div>
				<?php
				$desc = NULL;
				if (isset($invoice_info->desc)) {
					$desc = $invoice_info->desc;
				}
				$desc = set_value('desc', $this->crm_library->htmlspecialchars_decode($desc), FALSE);
				echo form_hidden(array('desc' => $desc))
				?>
				<div id="invoice_info"><?php echo $desc; ?></div>
			</div>
		</div><?php
	echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
