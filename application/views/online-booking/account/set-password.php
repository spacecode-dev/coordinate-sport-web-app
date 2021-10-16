<div class='row'>
	<div class='col-sm-4 col-sm-offset-4'>
		<h1 class='text-center title'><?php echo $title; ?></h1>
		<p>Enter a new password below.</p>
		<?php
		echo form_open();
			display_messages('fas');
			?><fieldset>
				<div class='form-group'>
					<?php
					$data = array(
						'name' => 'password',
						'id' => 'password',
						'class' => 'form-control',
						'value' => set_value('password', NULL, FALSE),
						'autofocus' => 'autofocus',
						'placeholder' => 'Password'
					);
					echo form_password($data);
					?>
				</div>
				<div class='form-group'>
					<?php
					$data = array(
						'name' => 'password_confirm',
						'id' => 'password_confirm',
						'class' => 'form-control',
						'value' => set_value('password_confirm', NULL, FALSE),
						'placeholder' => 'Confirm Password'
					);
					echo form_password($data);
					?>
				</div>
			</fieldset>
			<button class='btn btn-block'>Set New Password</button>
		<?php echo form_close(); ?>
		<div class="text-center">
			<br /><?php echo anchor('account/login', 'Already know your password?'); ?>
		</div>
	</div>
</div>
