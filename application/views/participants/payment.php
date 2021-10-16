<?php
display_messages();

if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
echo form_open($submit_to, 'class="make_payment" id="payment_form" data-cc_processor="' . $this->settings_library->get('cc_processor') . '"', array(
	'accountID' => $this->auth->user->accountID,
	'familyID' => $familyID
));
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
				<h3 class="card-label">Payment</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('From <em>*</em>', 'contactID');
					$contactID = NULL;
					if (isset($payment_info->contactID)) {
						$contactID = $payment_info->contactID;
					}
					if (isset($payment_info->internal) && $payment_info->internal == 1) {
						$contactID = 'internal';
					}
					$flashdata_val = $this->session->flashdata('payment_contactID');
					if (!empty($flashdata_val)) {
						$contactID = $flashdata_val;
					}
					$options = array(
						'' => 'Select',
						'internal' => 'Internal'
					);
					if ($contacts->num_rows() > 0) {
						foreach ($contacts->result() as $row) {
							$options[$row->contactID] = $row->first_name . ' ' . $row->last_name;
						}
					}
					$extra_atts = NULL;
					if (isset($payment_info->locked) && $payment_info->locked == 1) {
						$extra_atts = ' disabled readonly';
					}
					echo form_dropdown('contactID', $options, set_value('contactID', $this->crm_library->htmlspecialchars_decode($contactID), FALSE), 'id="contactID" class="form-control select2" required' . $extra_atts);
					?>
				</div>
				<div class='form-group'><?php
					echo form_label('Amount (' . currency_symbol(). ') <em>*</em>', 'amount');
					$amount = NULL;
					if (isset($payment_info->amount)) {
						$amount = $payment_info->amount;
					}
					$flashdata_val = $this->session->flashdata('payment_amount');
					if (!empty($flashdata_val)) {
						$amount = $flashdata_val;
					}
					$data = array(
						'name' => 'amount',
						'id' => 'amount',
						'class' => 'form-control',
						'value' => set_value('amount', $this->crm_library->htmlspecialchars_decode($amount), FALSE),
						'maxlength' => 10,
						'required' => 'required'
					);
					if (isset($payment_info->locked) && $payment_info->locked == 1) {
						$data['readonly'] = 'readonly';
					}
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Payment Method <em>*</em>', 'method');
					$options = array(
						'' => 'Select',
						'credit note' => 'Credit Note',
						'other' => 'Other',
						'card' => 'Credit/Debit Card',
						'cash' => 'Cash',
						'cheque' => 'Cheque',
						'direct debit' => 'Direct Debit',
						'childcare voucher' => 'Childcare Voucher',
						'bacs' => 'BACS',
						'refund' => 'Refund',
					);
					$contactId = $this->input->post('contactID');
					$flashdata_val = $this->session->flashdata('payment_method');
					if(!empty($contactId) || (isset($payment_info->contactID) || isset($payment_info->internal)) || !empty($flashdata_val)) {
						if ($contactId == 'internal' || (isset($payment_info->internal) && $payment_info->internal == 1) || $flashdata_val == 'internal') {
							unset($options['card']);
							unset($options['cash']);
							unset($options['cheque']);
							unset($options['direct debit']);
							unset($options['childcare voucher']);
							unset($options['bacs']);
						} else {
							unset($options['credit note']);
							unset($options['other']);
							unset($options['refund']);
						}
					}
					$method = NULL;
					if (isset($payment_info->method)) {
						$method = $payment_info->method;
					}
					if (!empty($flashdata_val)) {
						$method = $flashdata_val;
					}
					if(!isset($options[$method])){
						$options[$method] = ucwords($method);
					}
					echo form_dropdown('method', $options, set_value('method', $this->crm_library->htmlspecialchars_decode($method), FALSE), 'id="method" class="form-control select2" required' . $extra_atts);
					switch ($this->settings_library->get('cc_processor')) {
						case 'sagepay':
							?><small class="text-muted form-text manual_payment"><a href="<?php echo site_url('participants/payments/info'); ?>" data-payment-link="https://live.sagepay.com/mysagepay/">Open Sage Pay</a></small><?php
							break;
						case 'stripe':
							?><small class="text-muted form-text manual_payment"><a href="<?php echo site_url('participants/payments/info'); ?>" data-payment-link="https://dashboard.stripe.com/payments">Open Stripe Dashboard</a></small><?php
							break;
					}
					?>
				</div>
				<div class="form-group" id="childcarevoucher_details" style="display: none;">
					<div class="form-group">
						<?php
						echo form_label('Childcare Voucher Provider <em>*</em>', 'childcarevoucher_providerID');
						$options = array(
							'' => 'Select',
						);
						if(is_array($childcarevoucher_providers) && count($childcarevoucher_providers) > 0) {
							foreach ($childcarevoucher_providers as $providerID => $name) {
								$options[$providerID] = $name;
							}
						}
						echo form_dropdown('childcarevoucher_providerID', $options, set_value('childcarevoucher_providerID', NULL, FALSE), 'id="childcarevoucher_providerID" class="form-control select2"');
						?>
					</div>
					<?php
					$childcare_voucher_instruction = trim($this->settings_library->get('childcare_voucher_instruction', $this->cart_library->accountID));
					if (!empty($childcare_voucher_instruction)) {
						echo '<p>' . nl2br($childcare_voucher_instruction) . '</p>';
					}
					if (is_array($childcarevoucher_provider_notices) && count($childcarevoucher_provider_notices) > 0) {
						?><div class="notices"><?php
						foreach ($childcarevoucher_provider_notices as $providerID => $notice) {
							?><div class="notice" style="display: none;" data-provider="<?php echo $providerID; ?>"><?php echo $notice; ?></div><?php
						}
						?></div><?php
					} ?>
				</div>
				<div class='form-group'><?php
					echo form_label('Transaction Reference', 'field_transaction_ref');
					$transaction_ref = NULL;
					if (isset($payment_info->transaction_ref)) {
						$transaction_ref = $payment_info->transaction_ref;
					}
					$data = array(
						'name' => 'transaction_ref',
						'id' => 'field_transaction_ref',
						'class' => 'form-control',
						'value' => set_value('transaction_ref', $this->crm_library->htmlspecialchars_decode($transaction_ref), FALSE),
						'maxlength' => 100
					);
					if (isset($payment_info->locked) && $payment_info->locked == 1) {
						$data['readonly'] = 'readonly';
					}
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Note', 'field_note');
					$note = NULL;
					if (isset($payment_info->note)) {
						$note = $payment_info->note;
					}
					$data = array(
						'name' => 'note',
						'id' => 'field_note',
						'class' => 'form-control',
						'value' => set_value('note', $this->crm_library->htmlspecialchars_decode($note), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
