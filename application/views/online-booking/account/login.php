<div class='row'>
	<div class='col-sm-4 col-sm-offset-4'>
		<h1 class='text-center title'><?php echo $title; ?></h1>
		<?php
		$action = (isset($requests["SAMLRequest"]) && isset($requests["RelayState"]))?"account/sso_login":"";
		echo form_open($action);
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
				<div class='form-group'>
					<?php
					$data = array(
						'name' => 'password',
						'id' => 'password',
						'class' => 'form-control',
						'value' => NULL,
						'placeholder' => 'Password'
					);
					echo form_password($data);
					?>
					<div class="help-text text-right">
						<?php echo anchor('account/reset', 'Forgot your password?'); ?>
					</div>
				</div>
				<input type="hidden" name="SAMLRequest" value="<?php print isset($requests["SAMLRequest"])?$requests["SAMLRequest"]:""; ?>">
				<input type="hidden" name="RelayState" value="<?php print isset($requests["RelayState"])?$requests["RelayState"]:""; ?>">
			</fieldset>
			<button class='btn btn-block'>Sign in</button>
		<?php echo form_close(); ?>
		<div class="text-center">or</div>
		<a href="<?php echo site_url('account/register'); ?>" class='btn btn-block btn-hollow'>Register</a>
	</div>
</div>
