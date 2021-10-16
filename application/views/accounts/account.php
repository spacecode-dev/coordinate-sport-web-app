<?php
display_messages();
echo form_open_multipart($submit_to, 'class="edit_account"');
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-server text-contrast'></i></span>
				<h3 class="card-label">Account Information</h3>
			</div>
		</div>
		<div class="card-body">
			<p><small><?php
				if (isset($account_info->demo_data_imported)) {
					if ($account_info->demo_data_imported != 1) {
						?><a href="<?php echo site_url('accounts/demodata/' . $accountID); ?>" class="btn btn-sm btn-primary confirm">Import Demo Data</a><?php
					} else {
						?>The button below will delete everything within the account except the first created user. Use with caution.<br /><br /><a href="<?php echo site_url('accounts/cleardata/' . $accountID); ?>" class="btn btn-sm btn-danger confirm">Clear Account Data</a><?php
					}
				}
				if (!is_null($accountID) &&  $this->auth->user->department == 'directors') {
					?><br><br><a href="<?php echo site_url('accounts/anonymise/' . $accountID); ?>" class="btn btn-light btn-sm">Anonymise Account</a><?php
				}
			?></small></p>
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Company <em>*</em>', 'company');
					$company = NULL;
					if (isset($account_info->company)) {
						$company = $account_info->company;
					}
					$data = array(
						'name' => 'company',
						'id' => 'company',
						'class' => 'form-control',
						'value' => set_value('company', $this->crm_library->htmlspecialchars_decode($company), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Plan <em>*</em>', 'planID');
					$planID = NULL;
					if (isset($account_info->planID)) {
						$planID = $account_info->planID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($plans->num_rows () > 0) {
						foreach ($plans->result() as $plan) {
							$options[$plan->planID] = $plan->name;
						}
					}
					echo form_dropdown('planID', $options, set_value('planID', $this->crm_library->htmlspecialchars_decode($planID), FALSE), 'id="planID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Organisation Size', 'organisation_size');
					$organisation_size = NULL;
					if (isset($account_info->organisation_size)) {
						$organisation_size = $account_info->organisation_size;
					}
					if ($organisation_size == 0) {
						$organisation_size = NULL;
					}
					$data = array(
						'name' => 'organisation_size',
						'id' => 'organisation_size',
						'class' => 'form-control',
						'value' => set_value('organisation_size', $this->crm_library->htmlspecialchars_decode($organisation_size), FALSE),
						'min' => 0,
						'step' => 1
					);
					echo form_number($data);
					?><small class="text-muted form-text">Leave blank for unlimited</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Application Custom Domain', 'crm_customdomain');
					$crm_customdomain = NULL;
					if (isset($account_info->crm_customdomain)) {
						$crm_customdomain = $account_info->crm_customdomain;
					}
					$data = array(
						'name' => 'crm_customdomain',
						'id' => 'crm_customdomain',
						'class' => 'form-control',
						'value' => set_value('crm_customdomain', $this->crm_library->htmlspecialchars_decode($crm_customdomain), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
					?><small class="text-muted form-text">Changes made to this field will take 24 hours to propagate</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Booking Site Domain Type', 'booking_site_domain_type');
					$booking_site_domain_type = NULL;
					if (!empty($this->input->post('booking_customdomain'))) {
						$booking_site_domain_type = 'customdomain';
					} else if (!empty($this->input->post('booking_subdomain'))) {
						$booking_site_domain_type = 'subdomain';
					} else if (isset($account_info->booking_customdomain) && !empty($account_info->booking_customdomain)) {
						$booking_site_domain_type = 'customdomain';
					} else if (isset($account_info->booking_subdomain) && !empty($account_info->booking_subdomain)) {
						$booking_site_domain_type = 'subdomain';
					}
					$options = array(
						'' => 'None',
						'subdomain' => 'Subdomain',
						'customdomain' => 'Custom Domain'
					);
					echo form_dropdown('booking_site_domain_type', $options, set_value('booking_site_domain_type', $this->crm_library->htmlspecialchars_decode($booking_site_domain_type), FALSE), 'id="booking_site_domain_type" class="form-control select2"');
					?>
				</div>
				<div class='form-group'><?php
					echo form_label('Booking Site Subdomain', 'booking_subdomain');
					$booking_subdomain = NULL;
					if (isset($account_info->booking_subdomain)) {
						$booking_subdomain = $account_info->booking_subdomain;
					}
					$data = array(
						'name' => 'booking_subdomain',
						'id' => 'booking_subdomain',
						'class' => 'form-control',
						'value' => set_value('booking_subdomain', $this->crm_library->htmlspecialchars_decode($booking_subdomain), FALSE),
						'maxlength' => 50
					);
					?><div class="input-group">
						<?php echo form_input($data); ?>
						<span class="input-group-append">
							<span class="input-group-text">.<?php echo ROOT_DOMAIN; ?></span>
						</span>
					</div>
					<small class="text-muted form-text">Leave blank for no online booking site</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Booking Site Custom Domain', 'booking_customdomain');
					$booking_customdomain = NULL;
					if (isset($account_info->booking_customdomain)) {
						$booking_customdomain = $account_info->booking_customdomain;
					}
					$data = array(
						'name' => 'booking_customdomain',
						'id' => 'booking_customdomain',
						'class' => 'form-control',
						'value' => set_value('booking_customdomain', $this->crm_library->htmlspecialchars_decode($booking_customdomain), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
					?><small class="text-muted form-text">Changes made to this field will take 24 hours to propagate</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Status <em>*</em>', 'account_status');
					$status = NULL;
					if (isset($account_info->status)) {
						$status = $account_info->status;
					}
					$options = array(
						'' => 'Select',
						'trial' => 'Trial',
						'paid' => 'Paid',
						'demo' => 'Demo',
						'support' => 'Support Team',
						'internal' => 'Internal',
						'admin' => 'Admin Only',
					);
					echo form_dropdown('status', $options, set_value('status', $this->crm_library->htmlspecialchars_decode($status), FALSE), 'id="account_status" class="form-control select2"');
				?></div>
				<div class='form-group paid_until'><?php
					echo form_label('Account Expiry Date', 'paid_until');
					$paid_until = NULL;
					if (isset($account_info->paid_until)) {
						$paid_until = mysql_to_uk_date($account_info->paid_until);
					}
					$data = array(
						'name' => 'paid_until',
						'id' => 'paid_until',
						'class' => 'form-control datepicker',
						'value' => set_value('paid_until', $this->crm_library->htmlspecialchars_decode($paid_until), FALSE),
						'max_length' => 10,
						'autocomplete' => 'off'
					);
					echo form_input($data);
					?><small class="text-muted form-text">Leave blank for no expiry - Customers will not be able access this account after the date entered.
							An email will be sent to support@coordinate.cloud 30 days and 15 days before the expiry date.</small>
				</div>
				<div class='form-group trial_until'><?php
					echo form_label('Trial Until', 'trial_until');
					$trial_until = NULL;
					if (isset($account_info->trial_until)) {
						$trial_until = mysql_to_uk_date($account_info->trial_until);
					}
					$data = array(
						'name' => 'trial_until',
						'id' => 'trial_until',
						'class' => 'form-control datepicker',
						'value' => set_value('trial_until', $this->crm_library->htmlspecialchars_decode($trial_until), FALSE),
						'max_length' => 10,
						'autocomplete' => 'off'
					);
					echo form_input($data);
					?><small class="text-muted form-text">Leave blank for no expiry</small>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Email Notifications</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class="col-12 col-md-6">
					<div class='form-group'><?php
						echo form_label('Send Emails From', 'email_from_override');
						$email_from_override = $this->settings_library->get('email_from_override', $accountID);
						$data = array(
							'name' => 'email_from_override',
							'id' => 'email_from_override',
							'class' => 'form-control',
							'value' => set_value('email_from_override', $this->crm_library->htmlspecialchars_decode($email_from_override), FALSE),
							'maxlength' => 200
						);
						echo form_email($data);
						?>
						<small class="text-muted form-text">If not set, emails will be sent from <?php echo $this->settings_library->get('email_from_default', 'default'); ?></small>
					</div>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Contact Information</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Contact <em>*</em>', 'contact');
					$contact = NULL;
					if (isset($account_info->contact)) {
						$contact = $account_info->contact;
					}
					$data = array(
						'name' => 'contact',
						'id' => 'contact',
						'class' => 'form-control',
						'value' => set_value('contact', $this->crm_library->htmlspecialchars_decode($contact), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Email <em>*</em>', 'email');
					$email = NULL;
					if (isset($account_info->email)) {
						$email = $account_info->email;
					}
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE),
						'maxlength' => 200
					);
					echo form_email($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Phone', 'phone');
					$phone = NULL;
					if (isset($account_info->phone)) {
						$phone = $account_info->phone;
					}
					$data = array(
						'name' => 'phone',
						'id' => 'phone',
						'class' => 'form-control',
						'value' => set_value('phone', $this->crm_library->htmlspecialchars_decode($phone), FALSE),
						'maxlength' => 20
					);
					echo form_telephone($data);
				?></div>
			</div>
		</div>
	<?php
	echo form_fieldset_close();
	if ($has_user !== TRUE) {
		echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
					<h3 class="card-label">Create First User</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'>
						<?php
						$label = 'Password (<a href="#" class="generatepassword">Generate?)</a>';
						echo form_label($label, 'account_password');
						$data = array(
							'name' => 'account_password',
							'id' => 'account_password',
							'class' => 'form-control pwstrength',
							'value' => set_value('account_password', NULL, FALSE),
							'autocomplete' => 'off'
						);
						echo form_password($data);
						?>
					</div>
					<div class='form-group'><?php
						echo form_label('Confirm Password', 'account_password_confirm');
						$data = array(
							'name' => 'account_password_confirm',
							'id' => 'account_password_confirm',
							'class' => 'form-control pwstrength',
							'value' => set_value('account_password_confirm', NULL, FALSE),
							'autocomplete' => 'off'
						);
						echo form_password($data);
					?></div>
					<div class='form-group'><?php
						$data = array(
							'name' => 'notify',
							'id' => 'notify',
							'value' => 1
						);
						if (set_value('notify', 1) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox">
							<label>
								<?php echo form_checkbox($data); ?>
								Send login details by email
							</label>
						</div>
					</div>
					<small class="text-muted form-text">A temporary password will be sent and on next login, the staff member will be asked to choose a new password</small>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">User Activity
					<small><a href='<?php echo site_url('user-activity'); ?>' target="_blank">Show Activity</a></small>
				</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<?php
				echo form_label('User Access', 'staff_activity');
				if (count($staff_listing) > 0) {
					$options = array();
					if ($this->input->post()) {
						$staff_listing_selected = $this->input->post('staff_listing');
					}
					if (!is_array($staff_listing_selected)) {
						$staff_listing_selected = array();
					}
					echo form_multiselect('staff_activity[]',
						$staff_listing,
						$staff_listing_selected,
						'id="staff_activity" class="form-control select2 col-sm-6"');
				} else {
					echo "<p>None</p>";
				}
				?>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Modules</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				foreach ($addons as $field => $label) {
					?><div class='form-group'><?php
						echo form_label($label, $field);
						$data = array(
							'name' => $field,
							'id' => $field,
							'value' => 1
						);
						$val = NULL;
						if (isset($account_info->$field)) {
							$val = $account_info->$field;
						}
						if (set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE) == 1) {
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
						<?php
						if (array_key_exists($field, $addons_descriptions)) {
							?><small class="text-muted form-text"><?php echo $addons_descriptions[$field] ?></small><?php
						}
						?>
					</div><?php
				}
				?>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class="whitelabel_fields">
		<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
					<h3 class="card-label">Customisation</h3>
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<div class='col-md-6 form-group'><?php
						$field_info = $this->settings_library->get_field_info('logo');
						echo form_label($field_info->title, 'logo');
						$image_data = @unserialize($this->settings_library->get('logo', $accountID));
						if ($image_data !== FALSE) {
							$args = array(
								'alt' => 'Image',
								'src' => 'attachment/setting/logo/' . $accountID,
								'class' => 'responsive-img'
							);
							echo '<p>' . img($args) . '</p>';
							echo form_label('Replace ' . $field_info->title, 'logo');
						}
						$data = array(
							'name' => 'logo',
							'id' => 'logo',
							'class' => 'custom-file-input'
						);

						?><div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="logo">Choose file</label>
						</div>
						<small class="text-muted form-text">Transparent background recommended</small>
					</div>
					<div class='col-md-6 form-group'><?php
						$field_info = $this->settings_library->get_field_info('favicon');
						echo form_label($field_info->title, 'favicon');
						$image_data = @unserialize($this->settings_library->get('favicon', $accountID));
						if ($image_data !== FALSE) {
							$args = array(
								'alt' => 'Image',
								'src' => 'attachment/setting/favicon/' . $accountID,
								'class' => 'responsive-img'
							);
							echo '<p>' . img($args) . '</p>';
							echo form_label('Replace ' . $field_info->title, 'favicon');
						}
						$data = array(
							'name' => 'favicon',
							'id' => 'favicon',
							'class' => 'custom-file-input'
						);

						?><div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="favicon">Choose file</label>
						</div>
						<small class="text-muted form-text">Optimum size of 16px x16px. This Favicon will apply to both the Bookings Site and Web App of this customer account</small>
					</div>
				</div>
			</div>
		<?php echo form_fieldset_close(); ?>
	</div>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
