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
					echo form_label('Code <em>*</em>', 'field_code');
					$code = NULL;
					if (isset($code_info->code)) {
						$code = $code_info->code;
					}
					$data = array(
						'name' => 'code',
						'id' => 'field_code',
						'class' => 'form-control',
						'value' => set_value('code', $this->crm_library->htmlspecialchars_decode($code), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
					?></div>
				<div class='form-group'><?php
					echo form_label('Description', 'field_desc');
					$desc = NULL;
					if (isset($code_info->desc)) {
						$desc = $code_info->desc;
					}
					$data = array(
						'name' => 'desc',
						'id' => 'field_desc',
						'class' => 'form-control',
						'value' => set_value('desc', $this->crm_library->htmlspecialchars_decode($desc), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
					?></div>
			</div>
			<div class='form-group'><?php
				echo form_label('Active', 'field_active');
				$active = NULL;
				if (isset($code_info->active)) {
					$active = $code_info->active;
				}
				$data = array(
					'name' => 'active',
					'id' => 'code_active_checkbox',
					'value' => 1
				);
				if ($active) {
					$data['checked'] = TRUE;
				}
				?>
				<div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Yes
						<span></span>
					</label>
				</div>
			</div>
		<?php
		echo form_fieldset_close();
		?><div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div>
	<?php echo form_close();
