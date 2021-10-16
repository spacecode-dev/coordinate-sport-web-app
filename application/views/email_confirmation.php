<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<?php
				if (isset($contacts)) {
					?>
					<div class='form-group'><?php
						echo form_label('Contact <em>*</em>', 'contactID');
						$options = array();
						if ($contacts->num_rows() > 0) {
							foreach ($contacts->result() as $row) {
								$options[$row->contactID] = $row->name;
								if ($row->isMain == 1) {
									$options[$row->contactID] .= ' (Main)';
								}
							}
						}
						echo form_dropdown('contactID', $options, set_value('contactID'), 'id="contactID" class="form-control select2"');
						?>
					</div>

					<div class='form-group'><?php
						echo form_label('CC', 'cc');
						$options = array();
						if ($contacts->num_rows() > 0) {
							foreach ($contacts->result() as $row) {
								$options[$row->contactID] = $row->name;
								if ($row->isMain == 1) {
									$options[$row->contactID] .= ' (Main)';
								}
							}
						}
						echo form_multiselect('cc[]',
							$options,
							[],
							'id="cc" class="form-control select2"');
						?>
						<p class="add_email_text" id="add_cc" onclick="addAdditionalEmail('cc')">Add CC</p>
					</div>

					<div class='form-group'><?php
						echo form_label('BCC', 'bcc');
						$options = array();
						if ($contacts->num_rows() > 0) {
							foreach ($contacts->result() as $row) {
								$options[$row->contactID] = $row->name;
								if ($row->isMain == 1) {
									$options[$row->contactID] .= ' (Main)';
								}
							}
						}
						echo form_multiselect('bcc[]',
							$options,
							[],
							'id="bcc" class="form-control select2"');
						?>
						<p class="add_email_text" id="add_bcc" after="bcc" onclick="addAdditionalEmail('bcc')">Add BCC</p>
					</div>


					<?php
				}
				?>
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
					if (isset($contacts)) {
						?><p class="help-block">
							<small class="text-muted">Available tags: {contact_name}</small>
						</p><?php
					}
				?></div>
			</div>
		</div>
	<?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Add Staff Attachments</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<?php
				foreach ($qualifications as $id => $name) {
					$field = 'qual[' . $id . ']';
					$data = array(
						'name' => $field,
						'id' => 'qual-' . $id,
						'qual' => $id,
						'value' => 1,
						'class' => 'qual-email-attachment',
						'onclick' => "attachDataToEmail(". $bookingID .",'". $id ."');"
					);
					$val = NULL;
					/*if (isset($staff_info->$field)) {
						$val = $staff_info->$field;
					}*/
					if (set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?>
					<div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							<?php echo $name; ?>
							<span></span>
						</label>
					</div>
					<?php
				}
				?>
			</div>
			<?php if (isset($attachment_field) && $attachment_field === TRUE) {
				?><br>
				<div class="multi-columns">
					<div class='form-group'>
						<?php
						echo form_label('Attachment', 'field_file'); ?>
						<div class="email-attachments">
							<?php foreach ($quals_attachment as $id => $attachments) { ?>
								<?php foreach ($attachments as $attachment) { ?>
									<p class="email-attachment-<?php echo($id); ?>">
										<a href="/attachment/staff/<?php echo $attachment->path; ?>"><?php echo $attachment->name; ?></a>
									</p>
									<input type="hidden" class="email-attachment-<?php echo($id); ?>" name="addition_attachment[]" value="<?php echo($attachment->attachmentID); ?>">
								<?php } ?>
							<?php } ?>
						</div>
						<?php
						$data = array(
							'name' => 'files[]',
							'id' => 'field_file',
							'multiple' => 'multiple',
							'class' => 'custom-file-input'
						);
						?><div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="file">Choose file</label>
						</div>
					</div>
				</div><?php
			}
			?></div><?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Send
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
