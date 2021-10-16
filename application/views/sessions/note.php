<?php
display_messages();

if (isset($bookingID) && isset($lessonID) && $bookingID != NULL && $lessonID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $lesson_info->blockID,
		'lessonID' => $lessonID,
		'tab' => $tab
	);
	$this->load->view('sessions/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	if (isset($lessons) && is_array($lessons)) {
		foreach ($lessons as $lessonID => $lesson_info) {
			// store lesson
			echo form_hidden(array('lessons[]' => $lessonID));
		}
	}
	echo form_hidden(array('process' => 1));
	echo form_hidden(array('action' => 'note'));
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label"><?php echo ucwords($type); ?></h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if ($type != 'evaluation') {
					?><div class='form-group'><?php
						echo form_label('Title <em>*</em>', 'field_summary');
						echo '<p class="text-muted font-size-sm mb-2">The title will not be shown to delivery staff</p>';
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
					?></div><?php
				}
				?>
				<div class='form-group'><?php
					$label = 'Note';
					if ($type == 'evaluation') {
						$label = 'Evaluation';
					}
					echo form_label($label . ' <em>*</em>', 'field_content');
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
					if ($read_only === TRUE) {
						echo $content;
					} else {
						echo form_textarea($data);
					}
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	if ($read_only !== TRUE) {
		?><div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div><?php
	}
echo form_close();
