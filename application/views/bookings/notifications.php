<?php
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type,
		'register_type' => $booking_info->register_type
	);
	$this->load->view('bookings/tabs.php', $data);
	$data['tab'] = 'email_sms';
	$this->load->view('bookings/messaging-tabs.php', $data);
}
display_messages();
if ($this->auth->has_features('sms')) {
	echo form_open_multipart($submit_to);
	echo form_hidden(array('type' => 'sms'));
		echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-mobile text-contrast'></i></span>
					<h3 class="card-label">SMS <small>SMS messages are sent in bulk in the background every 15 minutes.</small></h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'><?php
						echo form_label('Message <em>*</em>', 'message');
						$data = array(
							'name' => 'message',
							'id' => 'message',
							'class' => 'form-control',
							'value' => set_value('message', NULL, FALSE),
							'maxlength' => 160
						);
						echo form_input($data);
						?><small class="text-muted form-text">Messages should be no longer than 160 characters. If using tags, this may cause the message to be cut off if the contact has a long name.<br>Available tags: {contact_first}, {contact_last}, {event_name}</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Blocks <em>*</em>', 'tags');
						$options = array();
						$options['all'] = "All";
						if (count($block_list) > 0) {
							foreach ($block_list as $key=>$block) {
								$options[$key] = $block;
							}
						}
						echo form_dropdown('blocks[]', $options, ($this->input->post('type') == 'sms')?set_value('blocks'):'', 'id="blocks-sms" multiple="multiple" class="form-control select2-tags"');
						?>
						<small class="text-muted form-text">Please select either All or a block name(s).</small>
					</div>
				</div>
			</div>
		<?php echo form_fieldset_close(); ?>
		<div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				Send
			</button>
		</div>
	<?php echo form_close();
}

echo form_open_multipart($submit_to);
	echo form_hidden(array('type' => 'email'));
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-envelope text-contrast'></i></span>
				<h3 class="card-label">Email</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Subject <em>*</em>', 'subject');
					$data = array(
						'name' => 'subject',
						'id' => 'subject',
						'class' => 'form-control',
						'value' => set_value('subject', NULL, FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Blocks <em>*</em>', 'tags');
					$options = array();
					$options['all'] = "All";
					if (count($block_list) > 0) {
						foreach ($block_list as $key=>$block) {
							$options[$key] = $block;
						}
					}
					echo form_dropdown('blocks[]', $options, ($this->input->post('type') == 'email')?set_value('blocks'):'', 'id="blocks-email" multiple="multiple" class="form-control select2-tags"');
					?>
					<small class="text-muted form-text">Please select either All or a block name(s).</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Message <em>*</em>', 'email');
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control wysiwyg',
						'value' => set_value('email', NULL, FALSE),
					);
					echo form_textarea($data);
					?><small class="text-muted form-text">Available tags: {contact_first}, {contact_last}, {event_name}</small>
				</div>
				<?php
				if ($attachments->num_rows() > 0) {
					?><div class='form-group'><?php
						echo form_label('Attachment', 'attachmentID');
						$options = array(
							'' => 'Select'
						);
						foreach ($attachments->result() as $row) {
							$options[$row->attachmentID] = $row->name;
						}
						echo form_dropdown('attachmentID', $options, set_value('attachmentID', NULL, FALSE), 'id="attachmentID" class="form-control select2"');
					?></div><?php
				}
				?>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Send
		</button>
	</div>
<?php echo form_close();
