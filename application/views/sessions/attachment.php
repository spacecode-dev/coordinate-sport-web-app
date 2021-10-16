<?php
display_messages();
if ($bookingID != NULL && $lessonID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $lesson_info->blockID,
		'lessonID' => $lessonID,
		'tab' => $tab
	);
	$this->load->view('sessions/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-paperclip text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if ($this->auth->has_features('resources')) {
					?><div class='form-group'><?php
						echo form_label('Copy template', 'templateID');
						$options = array(
							'' => 'Select'
						);
						if ($templates->num_rows() > 0) {
							foreach ($templates->result() as $row) {
								$options[$row->attachmentID] = $row->name;
							}
						}
						echo form_dropdown('templateID', $options, set_value('templateID', NULL, FALSE), 'id="templateID" class="form-control select2"');
					?></div><?php
				}
				?>
				<div class='form-group'><?php
					if ($this->auth->has_features('resources')) {
						$label = 'Or upload file';
					} else {
						$label = 'Upload file';
					}
					echo form_label($label, 'file');
					$data = array(
						'name' => 'file',
						'id' => 'file',
						'class' => 'custom-file-input'
					);
					?><div class="custom-file">
						<?php echo form_upload($data); ?>
						<label class="custom-file-label" for="file">Choose file</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Comment <em>*</em>', 'comment');
					$comment = NULL;
					if (isset($attachment_info->comment)) {
						$comment = $attachment_info->comment;
					}
					$data = array(
						'name' => 'comment',
						'id' => 'comment',
						'class' => 'form-control',
						'value' => set_value('comment', $this->crm_library->htmlspecialchars_decode($comment), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
