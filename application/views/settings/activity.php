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
				echo form_label('Name <em>*</em>', 'field_name');
				$name = NULL;
				if (isset($activity_info->name)) {
					$name = $activity_info->name;
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
			</div>
		</div><?php
	echo form_fieldset_close();
	if ($this->auth->has_features('online_booking')) {
		echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Functionality</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
				$data = array(
					'name' => 'exclude_online_booking_search',
					'id' => 'exclude_online_booking_search',
					'value' => 1
				);
				$exclude_online_booking_search = NULL;
				if (isset($activity_info->exclude_online_booking_search)) {
					$exclude_online_booking_search = $activity_info->exclude_online_booking_search;
				}
				if (set_value('exclude_online_booking_search', $this->crm_library->htmlspecialchars_decode($exclude_online_booking_search), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Hide from Search Dropdown on Bookings Site
						<span></span>
					</label>
				</div>
			</div>
		</div><?php
		echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
