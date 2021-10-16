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
				$name = $original_levels[$department];
				if (isset($level_info->name) && !empty($level_info->name)) {
					$name = $level_info->name;
				}
				$data = array(
					'name' => 'name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
				?><small class="text-muted form-text">Original name: <?php echo $original_levels[$department]; ?></small>
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
