<div class='row'>
	<div class='col-sm-4 col-sm-offset-4'>
		<h1 class='text-center title'><?php echo $title; ?></h1>
		<p>Enter your email and we'll send you instructions to reset your password.</p>
		<?php
		echo form_open();
			display_messages('fas');
			?><fieldset>
				<div class='form-group'>
					<?php
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', NULL, FALSE),
						'autofocus' => 'autofocus',
						'placeholder' => 'Email'
					);
					echo form_email($data);
					?>
				</div>
			</fieldset>
			<button class='btn btn-block'>Request New Password</button>
		<?php echo form_close(); ?>
		<div class="text-center">
			<br /><?php echo anchor('account/login', 'Already know your password?'); ?>
		</div>
	</div>
</div>
