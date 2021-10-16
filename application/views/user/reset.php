<?php echo form_open('reset', ['class' => 'form', 'id' => 'kt_login_signin_form']); ?>
	<div class="text-left">
		<?php display_messages(); ?>
	</div>
	<div class="form-group mb-5">
		<?php
		$data = array(
			'name' => 'email',
			'id' => 'email',
			'class' => 'form-control h-auto form-control-solid py-4 px-8',
			'value' => $email,
			'autofocus' => 'autofocus',
			'placeholder' => 'Email',
			'required' => 'required'
		);
		echo form_email($data);
		?>
	</div>
	<div class="form-group d-flex flex-wrap justify-content-center align-items-center">
		<a href="<?php echo site_url('login'); ?>" id="kt_login_forgot" class="text-muted text-hover-primary">Already know your password?</a>
	</div>
	<button id="kt_login_signin_submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4">Request New Password</button>
	<?php
echo form_close();
