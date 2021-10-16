<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-server text-contrast'></i></span>
				<h3 class="card-label">Anonymise Account</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='form-group'><?php
				echo form_label('Keep Staff Names/Email With These Emails', 'keep_emails');
				$data = array(
					'name' => 'keep_emails',
					'id' => 'keep_emails',
					'class' => 'form-control',
					'value' => set_value('keep_emails'),
					'multiple' => 'multiple'
				);
				echo form_email($data);
			?><p class="help-block">
				<small class="text-muted">Comma separated. This is useful for keeping certain account login details intact.</small>
			</p></div>
			<div class='form-group'><?php
				echo form_label('Please write CONFIRM to proceed <em>*</em>', 'confirm');
				$data = array(
					'name' => 'confirm',
					'id' => 'confirm',
					'class' => 'form-control',
					'value' => set_value('confirm'),
				);
				echo form_input($data);
			?></div>
			<div class="alert alert-info">
				<p>This can be a long process depending on the account size. The page will refresh several times before completion.</p>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Anonymise
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
