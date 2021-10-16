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
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($category_info->name)) {
						$name = $category_info->name;
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
					$permission_level = NULL;
					if (isset($category_info->permissionLevel)) {
						$permission_level = $category_info->permissionLevel;
					}
					$options = array(
						'' => 'Select'
					);
					foreach ($permission_levels as $key => $value) {
						$options[$key] = $value;
					}

					echo form_label('Permission Level <em>*</em>', 'permission_level');
					echo form_dropdown('permission_level', $options, set_value('permission_level',$this->crm_library->htmlspecialchars_decode($permission_level)), 'id="permission_level" class="select2 form-control"');
				?></div>
				<div class='form-group'><?php
					$checkboxes = [
						'policies' => 'Show on Dashboard Policies',
						'customer_attachments' => 'Show in Customer Attachments Templates',
						'booking_attachments' => 'Show in Booking Attachments Templates',
						'session_attachments' => 'Show in Session Attachments Templates',
						'staff_attachments' => 'Show in Staff Attachments Templates',
					];
					foreach ($checkboxes as $field => $label) {
						$data = array(
							'name' => $field,
							'id' => $field,
							'value' => 1
						);
						$val = NULL;
						if (isset($category_info->$field)) {
							$val = $category_info->$field;
						}
						if (set_value('val', $this->crm_library->htmlspecialchars_decode($val), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								<?php echo $label; ?>
								<span></span>
							</label>
						</div><?php
					}
					?>
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
