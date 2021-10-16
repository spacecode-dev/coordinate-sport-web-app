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
				<div class='form-group'><?php
					echo form_label('Task <em>*</em>', 'task');
					$task = NULL;
					if (isset($task_info->task)) {
						$task = $task_info->task;
					}
					$data = array(
						'name' => 'task',
						'id' => 'task',
						'class' => 'form-control',
						'value' => set_value('task', $this->crm_library->htmlspecialchars_decode($task), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Staff', 'staffID');
					$staffID = NULL;
					if (isset($task_info->staffID)) {
						$staffID = $task_info->staffID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' . $row->surname;
						}
					}
					echo form_dropdown('staffID', $options, set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE), 'id="staffID" class="form-control select2"');
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
