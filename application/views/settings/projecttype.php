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
			<div class='form-group'><?php
				echo form_label('Name <em>*</em>', 'field_name');
				$name = NULL;
				if (isset($type_info->name)) {
					$name = $type_info->name;
				}
				$data = array(
					'name' => 'name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
			?></div>
			<div class='form-group'><?php
					$data = array(
						'name' => 'exclude_from_participant_booking_lists',
						'id' => 'exclude_from_participant_booking_lists',
						'value' => 1
					);
					$exclude_from_participant_booking_lists = NULL;
					if (isset($type_info->exclude_from_participant_booking_lists)) {
						$exclude_from_participant_booking_lists = $type_info->exclude_from_participant_booking_lists;
					}
					if (set_value('exclude_from_participant_booking_lists', $this->crm_library->htmlspecialchars_decode($exclude_from_participant_booking_lists), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Do not show on participant booking course list
							<span></span>
						</label>
					</div>
				</div>
		</div><?php
	echo form_fieldset_close();
?><div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
	<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
</div>
<?php echo form_close();
