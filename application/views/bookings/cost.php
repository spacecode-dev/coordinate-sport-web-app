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
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Date <em>*</em>', 'date');
					$date = NULL;
					if (isset($cost_info->date)) {
						$date = mysql_to_uk_date($cost_info->date);
					}
					$data = array(
						'name' => 'date',
						'id' => 'date',
						'class' => 'form-control datepicker',
						'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Item <em>*</em>', 'field_note');
					$note = NULL;
					if (isset($cost_info->note)) {
						$note = $cost_info->note;
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
				<div class='form-group'><?php
					echo form_label('Block Name <em>*</em>', 'blockID');
					$blockID = NULL;
					if (isset($cost_info->blockID)) {
						$blockID = $cost_info->blockID;
					}
					$options = array(
						'' => 'Select'
					);
					foreach ($block_info->result() as $row) {
						$options[$row->blockID] = $row->name;
					}
					echo form_dropdown('blockID', $options, set_value('blockID', $this->crm_library->htmlspecialchars_decode($blockID), FALSE), 'id="blockID" class="form-control select2"');
					?></div>
				<div class='form-group'><?php
					echo form_label('Category <em>*</em>', 'category');
					$category = NULL;
					if (isset($cost_info->category)) {
						$category = $cost_info->category;
					}
					$options = array(
						'' => 'Select',
						'Venue Hire' => 'Venue Hire',
						'Marketing' => 'Marketing',
						'Prizes' => 'Prizes',
						'Supplies' => 'Supplies',
						'Misc.' => 'Misc.'
					);
					echo form_dropdown('category', $options, set_value('category', $this->crm_library->htmlspecialchars_decode($category), FALSE), 'id="category" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Amount <em>*</em>', 'field_amount');
					$amount = NULL;
					if (isset($cost_info->amount) && $cost_info->amount > 0) {
						$amount = $cost_info->amount;
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
