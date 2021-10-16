<?php
display_messages();

if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Note</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='form-group'><?php
				echo form_label('Summary <em>*</em>', 'field_summary');
				$summary = NULL;
				if (isset($note_info->summary)) {
					$summary = $note_info->summary;
				}
				$data = array(
					'name' => 'summary',
					'id' => 'field_summary',
					'class' => 'form-control',
					'value' => set_value('summary', $this->crm_library->htmlspecialchars_decode($summary), FALSE),
					'maxlength' => 255
				);
				echo form_input($data);
			?></div>
			<div class='form-group'><?php
				echo form_label('Details <em>*</em>', 'field_content');
				$content = NULL;
				if (isset($note_info->content)) {
					$content = $note_info->content;
				}
				// convert pre-wysiwyg fields to html
				if ($content == strip_tags($content)) {
					$content = '<p>' . nl2br($content) . '</p>';
				}
				$data = array(
					'name' => 'content',
					'id' => 'field_content',
					'class' => 'form-control wysiwyg',
					'value' => set_value('content', $this->crm_library->htmlspecialchars_decode($content), FALSE)
				);
				echo form_textarea($data);
			?></div><?php
		?></div><?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
