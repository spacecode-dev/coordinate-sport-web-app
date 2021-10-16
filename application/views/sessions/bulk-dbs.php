<?php
display_messages();

if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $blockID,
		'tab' => $tab,
		'type' => $booking_info->type,
		'is_project' => $booking_info->project
	);
	$this->load->view('bookings/tabs.php', $data);
}

echo form_open_multipart($submit_to);
	if (isset($lessons) && is_array($lessons)) {
		foreach ($lessons as $lessonID => $lesson_info) {
			// store lesson
			echo form_hidden(array('lessons[]' => $lessonID));
		}
	}
	echo form_hidden(array('process' => 1));
	echo form_hidden(array('action' => 'dbs'));
	echo form_hidden(array('bulk_contactID' => $bulk_contactID));
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<div class='form-group'><?php
					echo form_label('Subject <em>*</em>', 'field_subject');
					$data = array(
						'name' => 'subject',
						'id' => 'field_subject',
						'class' => 'form-control',
						'value' => set_value('subject', $this->crm_library->htmlspecialchars_decode($subject), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Content <em>*</em>', 'field_content');
					$data = array(
						'name' => 'content',
						'id' => 'field_content',
						'class' => 'form-control wysiwyg',
						'value' => set_value('content', $this->crm_library->htmlspecialchars_decode($content), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Send
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
