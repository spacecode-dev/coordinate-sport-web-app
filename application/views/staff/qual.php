<?php
display_messages();

if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($qual_info->name)) {
						$name = $qual_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Level', 'level');
					$level = NULL;
					if (isset($qual_info->level)) {
						$level = $qual_info->level;
					}
					$data = array(
						'name' => 'level',
						'id' => 'level',
						'class' => 'form-control',
						'value' => set_value('level', $this->crm_library->htmlspecialchars_decode($level), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Qualification No.', 'reference');
					$reference = NULL;
					if (isset($qual_info->reference)) {
						$reference = $qual_info->reference;
					}
					$data = array(
						'name' => 'reference',
						'id' => 'reference',
						'class' => 'form-control',
						'value' => set_value('reference', $this->crm_library->htmlspecialchars_decode($reference), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Issue Date', 'issue_date');
					$issue_date = NULL;
					if (isset($qual_info->issue_date)) {
						$issue_date = mysql_to_uk_date($qual_info->issue_date);
					}
					$data = array(
						'name' => 'issue_date',
						'id' => 'issue_date',
						'class' => 'form-control datepicker',
						'value' => set_value('issue_date', $this->crm_library->htmlspecialchars_decode($issue_date), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Expiry Date', 'expiry_date');
					$expiry_date = NULL;
					if (isset($qual_info->expiry_date)) {
						$expiry_date = mysql_to_uk_date($qual_info->expiry_date);
					}
					$data = array(
						'name' => 'expiry_date',
						'id' => 'expiry_date',
						'class' => 'form-control datepicker',
						'value' => set_value('expiry_date', $this->crm_library->htmlspecialchars_decode($expiry_date), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
					?><p class="help-block">
						<small class="text-muted">If entered, expiration will be tracked on the dashboard</small>
					</p>
				</div>
				<div class='form-group form-under-gray-text'><?php
					echo form_label('Attachment', 'file');
					if ($attachment_info) {
						echo '<div>' . anchor('attachment/staff/' . $attachment_info->path, $attachment_info->name, 'target="_blank"') . '<a class=\'confirm-delete red-text ml-5\' href='. site_url('staff/attachments/remove/' . $attachment_info->attachmentID . '/quals_edit') . ' title="Remove"><i class=\'far fa-times remove-icon\'></i></a></div>';
					} else {
						$data = array(
							'name' => 'file',
							'id' => 'file',
							'class' => 'custom-file-input'
						);
						?><div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="file">Choose file</label>
						</div><?php
					}
					?>
				</div>
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
	<?php
echo form_close();
