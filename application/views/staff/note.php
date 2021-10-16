<?php
display_messages();

if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'id="staff-note"');
 echo form_fieldset('', ['class' => 'card card-custom']);
	?>
	<div class='card-header'>
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
				if (isset($note_info->date)) {
					$date = date("d/m/Y", strtotime($note_info->date));
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
				echo form_label('Type <em>*</em>', 'type');
				$type = NULL;
				if (isset($note_info->type)) {
					$type = $note_info->type;
				}
				$options = array(
					'' => 'Select',
					'appraisal' => 'Appraisal',
					'disciplinary' => 'Disciplinary',
					'feedbacknegative' => 'Feedback (Negative)',
					'feedbackpositive' => 'Feedback (Positive)',
					'induction' => 'Induction',
					'late' => 'Late',
					'misc' => 'Miscellaneous ',
					'observation' => 'Observation',
					'payroll' => 'Payroll',
					'pupilassessment' => 'Pupil Assessment',
				);
				echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
			?></div>
			<div class='form-group'><?php
				echo form_label('Observation Score <em>*</em>', 'field_observation_score');
				$observation_score = NULL;
				if (isset($note_info->observation_score)) {
					$observation_score = $note_info->observation_score;
				}
				$data = array(
					'name' => 'observation_score',
					'id' => 'field_observation_score',
					'class' => 'form-control',
					'value' => set_value('observation_score', $this->crm_library->htmlspecialchars_decode($observation_score), FALSE),
					'maxlength' => 3,
					'step' => 1,
					'min' => 0,
					'max' => 100
				);
				?><div class="input-group">
					<?php echo form_number($data); ?>
					<div class="input-group-append"><span class="input-group-text">%</span></div>
				</div>
			</div>
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
					// if note created before wysiwyg, add line breaks
					if (strip_tags($content) == $content) {
						$content = nl2br($content);
					}
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
?><div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
	<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
</div>
<?php echo form_close();
