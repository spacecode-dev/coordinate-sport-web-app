<?php echo form_open('terms', ['class' => 'form', 'id' => 'kt_login_signin_form']);
	echo form_hidden(array(
		'redirect_to' => $redirect_to
	));
	?>
	<div class="text-left">
		<?php display_messages(); ?>
	</div>
	<div class="terms_box mb-5">
		<?php
		$policies = array();
		$account_policy = $this->settings_library->get('staff_privacy');
		if (!empty($account_policy)) {
			echo '<h3>' . $this->auth->account->company . '</h3>';
			echo '<p>' . nl2br($account_policy) . '</p>';
			$policies[] = $this->auth->account->company;
		}
		$company_policy = $this->settings_library->get('company_privacy', 'default');
		if (!empty($company_policy)) {
			echo '<h3>' . $this->settings_library->get('company', 'default') . '</h3>';
			echo '<p>' . nl2br($company_policy) . '</p>';
			$policies[] = $this->settings_library->get('company', 'default');
		}
		?>
	</div>
	<div class="form-group mb-5">
		<?php
		$data = array(
			'name' => 'agree',
			'id' => 'agree',
			'value' => 1
		);
		if (set_value('agree') == 1) {
			$data['checked'] = TRUE;
		}
		?><div class="checkbox-single">
			<label class="checkbox">
				<?php echo form_checkbox($data); ?>
				I have read the <?php echo implode(" and ", $policies); ?> privacy policy and accept the terms of service
				<span></span>
			</label>
		</div>
	</div>
	<button id="kt_login_signin_submit" class="btn btn-primary font-weight-bold px-9 py-4 my-3 mx-4">Submit</button>
	<?php
echo form_close();
