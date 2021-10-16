<?php
display_messages();
echo form_open_multipart($submit_to, 'class="new_family family"');
	echo form_fieldset('', ['class' => 'card card-custom']); ?>
       <div class='card-header'>
           	<div class="card-title">
           		<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
           		<h3 class="card-label">Individual</h3>
           	</div>
       </div>
       <div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Title', 'title');
					$options = array(
						'' => 'Select',
						'mr' => 'Mr',
						'mrs' => 'Mrs',
						'miss' => 'Miss',
						'ms' => 'Ms',
						'dr' => 'Dr'
					);
					echo form_dropdown('title', $options, set_value('title', NULL, FALSE), 'id="title" class="form-control select2"');
				?></div>
				<div id="contactcheck_result" style="display:none;">
					<div class="alert alert-danger">
						<h4><i class="far fa-exclamation-circle"></i> Error</h4>
						<div class="result"></div>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('First Name <em>*</em>', 'town');
					$data = array(
						'name' => 'first_name',
						'id' => 'first_name',
						'class' => 'form-control',
						'value' => set_value('first_name', NULL, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Last Name <em>*</em>', 'town');
					$data = array(
						'name' => 'last_name',
						'id' => 'last_name',
						'class' => 'form-control',
						'value' => set_value('last_name', NULL, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Gender <em>*</em>', 'gender');
					$options = array(
						'' => 'Select',
						'male' => 'Male',
						'female' => 'Female',
						'other' => 'Other - Prefer not to say'
					);
					echo form_dropdown('gender', $options, set_value('gender', NULL, FALSE), 'id="gender" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Date of Birth <em>*</em>', 'dob');
					$data = array(
						'name' => 'dob',
						'id' => 'dob',
						'class' => 'form-control datepicker datepicker-dob',
						'value' => set_value('dob', NULL, FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Medical Notes', 'medical');
					$data = array(
						'name' => 'medical',
						'id' => 'medical',
						'class' => 'form-control',
						'value' => set_value('medical', NULL, FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Disability Information', 'disability_info');
					$data = array(
						'name' => 'disability_info',
						'id' => 'disability_info',
						'class' => 'form-control',
						'value' => set_value('disability_info', NULL, FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Ethnic Origin', 'ethnic_origin');
					$options = $this->settings_library->ethnic_origins;
					echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', NULL, FALSE), 'id="ethnic_origin" class="form-control select2"');
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
				<h3 class="card-label">Address</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Address', 'address1');
					$data = array(
						'name' => 'address1',
						'id' => 'address1',
						'class' => 'form-control',
						'value' => set_value('address1', NULL, FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?><br /><?php
					$data = array(
						'name' => 'address2',
						'id' => 'address2',
						'class' => 'form-control',
						'value' => set_value('address2', NULL, FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?><br /><?php
					$data = array(
						'name' => 'address3',
						'id' => 'address3',
						'class' => 'form-control',
						'value' => set_value('address3', NULL, FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Town', 'town');
					$data = array(
						'name' => 'town',
						'id' => 'town',
						'class' => 'form-control',
						'value' => set_value('town', NULL, FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label(localise('county'), 'county');
					$data = array(
						'name' => 'county',
						'id' => 'county',
						'class' => 'form-control',
						'value' => set_value('county', NULL, FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Post Code <em>*</em>', 'postcode');
					$data = array(
						'name' => 'postcode',
						'id' => 'postcode',
						'class' => 'form-control',
						'value' => set_value('postcode', NULL, FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-phone text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					$label = 'Mobile';
					if ($this->settings_library->get('require_mobile') == 1) {
						$label .= ' <em>*</em>';
					}
					echo form_label($label, 'mobile');
					$data = array(
						'name' => 'mobile',
						'id' => 'mobile',
						'class' => 'form-control',
						'value' => set_value('mobile', NULL, FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Other Phone', 'phone');
					$data = array(
						'name' => 'phone',
						'id' => 'phone',
						'class' => 'form-control',
						'value' => set_value('phone', NULL, FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Work Phone', 'workPhone');
					$data = array(
						'name' => 'workPhone',
						'id' => 'workPhone',
						'class' => 'form-control',
						'value' => set_value('workPhone', NULL, FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<div class='form-group'><?php
					$label = 'Email';
					if ($this->settings_library->get('require_participant_email') == 1) {
						$label .= ' <em>*</em>';
					}
					echo form_label($label, 'email');
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', NULL, FALSE),
						'maxlength' => 150,
						'autocomplete' => 'off'
					);
					echo form_email($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user-friends text-contrast'></i></span>
				<h3 class="card-label">Emergency Contacts</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<h4 class="lead small">Emergency Contact 1</h4>
				<div class='form-group'><?php
					echo form_label('Name', 'emergency_contact_1_name');
					$data = array(
						'name' => 'emergency_contact_1_name',
						'id' => 'emergency_contact_1_name',
						'class' => 'form-control',
						'value' => set_value('emergency_contact_1_name', NULL, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Contact Number', 'emergency_contact_1_phone');
					$data = array(
						'name' => 'emergency_contact_1_phone',
						'id' => 'emergency_contact_1_phone',
						'class' => 'form-control',
						'value' => set_value('emergency_contact_1_phone', NULL, FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<h4 class="lead small">Emergency Contact 2</h4>
				<div class='form-group'><?php
					echo form_label('Name', 'emergency_contact_2_name');
					$data = array(
						'name' => 'emergency_contact_2_name',
						'id' => 'emergency_contact_2_name',
						'class' => 'form-control',
						'value' => set_value('emergency_contact_2_name', NULL, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Contact Number', 'emergency_contact_2_phone');
					$data = array(
						'name' => 'emergency_contact_2_phone',
						'id' => 'emergency_contact_2_phone',
						'class' => 'form-control',
						'value' => set_value('emergency_contact_2_phone', NULL, FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
				<h3 class="card-label">Web Site Login</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Password (<a href="#" class="generatepassword">Generate?)</a>', 'password');
					$password = NULL;
					$data = array(
						'name' => 'password',
						'id' => 'password',
						'class' => 'form-control pwstrength',
						'value' => set_value('password', $this->crm_library->htmlspecialchars_decode($password), FALSE),
						'maxlength' => 100,
						'autocomplete' => 'off'
					);
					echo form_password($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Confirm Password', 'password_confirm');
					$password_confirm = NULL;
					$data = array(
						'name' => 'password_confirm',
						'id' => 'password_confirm',
						'class' => 'form-control pwstrength',
						'value' => set_value('password_confirm', $this->crm_library->htmlspecialchars_decode($password_confirm), FALSE),
						'maxlength' => 100,
						'autocomplete' => 'off'
					);
					echo form_password($data);
				?></div><?php
				if ($this->settings_library->get('send_new_participant') == 1) {
					?><div class='form-group'><?php
						$data = array(
							'name' => 'notify',
							'id' => 'notify',
							'value' => 1
						);
						if (set_value('notify') == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Send login details by email
								<span></span>
							</label>
						</div>
					</div><?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Advanced</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Tags', 'tags');
					$options = array();
					if (count($tag_list) > 0) {
						foreach ($tag_list as $tag) {
							$options[$tag] = $tag;
						}
					}
					echo form_dropdown('tags[]', $options, set_value('tags'), 'id="tags" multiple="multiple" class="form-control select2-tags"');
					?>
					<small class="text-muted form-text">Start typing to select a tag or create a new one.</small>
				</div>
			</div>
		</div><?php
	echo form_fieldset_close();
	if (time() >= strtotime('2018-05-25')) {
		$data = array(
			'brands' => $brands
		);
		$this->load->view('participants/privacy-inlay.php', $data);
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
