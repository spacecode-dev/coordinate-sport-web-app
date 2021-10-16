<?php
display_messages();
echo form_open_multipart($submit_to, 'class="resource"');
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='form-group'><?php
				echo form_label('Date <em>*</em>', 'date');
				$data = array(
					'name' => 'date',
					'id' => 'date',
					'class' => 'form-control datepicker datepicker-past',
					'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
					'maxlength' => 10,
					'data-onlyday' => 'monday'
				);
				echo form_input($data);
			?></div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Generate
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div><?php
echo form_close();
