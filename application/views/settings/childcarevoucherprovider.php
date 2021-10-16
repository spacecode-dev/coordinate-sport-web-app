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
					if (isset($provider_info->name)) {
						$name = $provider_info->name;
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
					echo form_label('Reference <em>*</em>', 'field_reference');
					$reference = NULL;
					if (isset($provider_info->reference)) {
						$reference = $provider_info->reference;
					}
					$data = array(
						'name' => 'reference',
						'id' => 'field_reference',
						'class' => 'form-control',
						'value' => set_value('reference', $this->crm_library->htmlspecialchars_decode($reference), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Comment', 'field_comment');
					$comment = NULL;
					if (isset($provider_info->comment)) {
						$comment = $provider_info->comment;
					}
					$data = array(
						'name' => 'comment',
						'id' => 'field_comment',
						'class' => 'form-control',
						'value' => set_value('comment', $this->crm_library->htmlspecialchars_decode($comment), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Voucher Information Notice', 'field_information');
					$information = NULL;
					if (isset($provider_info->information)) {
						$information = $provider_info->information;
					}
					$data = array(
						'name' => 'information',
						'id' => 'field_information',
						'class' => 'form-control wysiwyg',
						'value' => set_value('information', $this->crm_library->htmlspecialchars_decode($information), FALSE)
					);
					echo form_textarea($data);
					?><small class="text-muted form-text">Text entered into this box will be visible to customers when using the bookings site</small><?php
				?></div>
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
