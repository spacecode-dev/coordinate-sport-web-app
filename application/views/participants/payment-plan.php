<?php
display_messages();

if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'class="make_payment"');
?>
<div class="card card-custom">
	<div class="card-header">
		<div class="card-title">
			<div class="card-icon">
				<i class='far fa-calendar-check text-contrast'></i>
			</div>
			<div class='card-label'>Payment Plan</div>
		</div>
	</div>
	<div class="card-body">
	<?php
		echo form_fieldset();
	?>
		<div class='multi-columns'>
			<div class='form-group'><?php
				echo form_label('Contact <em>*</em>', 'contactID');
				$contactID = NULL;
				if (isset($plan_info->contactID)) {
					$contactID = $plan_info->contactID;
				}
				$options = array(
					'' => 'Select'
				);
				if ($contacts->num_rows() > 0) {
					foreach ($contacts->result() as $row) {
						$options[$row->contactID] = $row->first_name . ' ' . $row->last_name;
					}
				}
				if (empty($planID)) {
					echo form_dropdown('contactID', $options, set_value('contactID', $this->crm_library->htmlspecialchars_decode($contactID), FALSE), 'id="contactID" class="form-control select2"');
				} else {
					echo form_input('contactID', $this->crm_library->htmlspecialchars_decode($options[$plan_info->contactID], FALSE), 'class="form-control" readonly="readonly"');
				}
				?>
			</div>
			<div class='form-group'><?php
				echo form_label('Total Amount (' . currency_symbol() . ') <em>*</em>', 'field_amount');
				$amount = NULL;
				if (isset($plan_info->amount)) {
					$amount = $plan_info->amount;
				}
				$data = array(
					'name' => 'amount',
					'id' => 'field_amount',
					'class' => 'form-control',
					'value' => set_value('amount', $this->crm_library->htmlspecialchars_decode($amount), FALSE),
					'maxlength' => 10
				);
				if (empty($planID)) {
					echo form_input($data);
				} else {
					echo form_input('amount', $this->crm_library->htmlspecialchars_decode($plan_info->amount, FALSE), 'class="form-control" readonly="readonly"');
				}
				?></div>
			<div class='form-group'><?php
				echo form_label('Number of Payments <em>*</em>', 'field_interval_count');
				$interval_count = NULL;
				if (isset($plan_info->interval_count)) {
					$interval_count = $plan_info->interval_count;
				}
				$data = array(
					'name' => 'interval_count',
					'id' => 'field_interval_count',
					'class' => 'form-control',
					'value' => set_value('interval_count', $this->crm_library->htmlspecialchars_decode($interval_count), FALSE),
					'maxlength' => 10
				);
				if (empty($planID)) {
					echo form_input($data);
				} else {
					echo form_input('interval_count', $this->crm_library->htmlspecialchars_decode($plan_info->interval_count, FALSE), 'class="form-control" readonly="readonly"');
				}
				?><p class="help-block">
					<small class="text-muted">Note: The first payment will be taken immediately</small>
				</p>
			</div>
			<?php
			echo form_hidden(array('interval_length' => 1));
			?>
			<div class='form-group'><?php
				echo form_label('Period <em>*</em>', 'interval_unit');
				$interval_unit = 'month';
				if (isset($plan_info->interval_unit)) {
					$interval_unit = $plan_info->interval_unit;
				}
				$options = array(
					'' => 'Select',
					'month' => 'Monthly',
					'week' => 'Weekly'
				);
				if (empty($planID)) {
					echo form_dropdown('interval_unit', $options, set_value('interval_unit', $this->crm_library->htmlspecialchars_decode($interval_unit), FALSE), 'id="payment_method" class="form-control select2"');
				} else {
					echo form_input('interval_unit', $this->crm_library->htmlspecialchars_decode($options[$plan_info->interval_unit], FALSE), 'class="form-control" readonly="readonly"');
				}
				?>
			</div>
			<div class='form-group'><?php
				echo form_label('Note', 'field_note');
				$note = NULL;
				if (isset($plan_info->note)) {
					$note = $plan_info->note;
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
		</div><?php
		echo form_fieldset_close();
	?>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				Save
			</button>
			<a class='btn btn-default' href="<?php echo site_url($return_to); ?>">
				Cancel
			</a>
		</div>
	</div>
	<?php
	echo form_close();
	?>
</div>
