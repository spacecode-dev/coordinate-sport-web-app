<?php
if ($mode == 'register') {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
	if ($this->online_booking->accountID == 64) {
		// ignite override
		?><p>If you already have an account, please <a href="<?php echo site_url('account/login') ?>reset">login</a> instead.</p><?php
	} else {
		?><p>If you have booked with us before, but not online, you can <a href="<?php echo site_url('account/reset') ?>">retrieve your password</a>. If you already have an account, please <a href="<?php echo site_url('account/login') ?>">login</a> instead.</p><?php
	}
}
echo form_open_multipart($submit_url, array("class" => "profile"));
display_messages('fas');
?>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Register As</h3>
		</div>
		<div class="col-xs-12">
			<div class='form-group'>
				<?php
				$booking_for = NULL;
				if (isset($contact_info->booking_for)) {
					$booking_for = $contact_info->booking_for;
				}
				$options = array(
					'child' => 'Are you making a booking for your child?',
					'contact' => 'Are you making a booking for yourself?',
					'child_and_contact' => 'Are you making a booking for your child and yourself?'
				);
				foreach ($options as $k => $label) {
					$data = array(
						'name' => 'booking_for',
						'id' => 'booking_for',
						'value' => $k,
						'required' => true,
						'type' => 'radio',
						'checked' => (bool)(set_value('booking_for', $booking_for)==$k)
					);
					?>
					<div class="radio-single">
						<label class="checkbox">
							<?php
							echo form_checkbox($data);
							echo $label;
							?>
						</label>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</fieldset>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Personal Details</h3>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('title', $fields)) { ?>
			<div class='form-group'>
				<?php
				$title = NULL;
				if (isset($contact_info->title)) {
					$title = $contact_info->title;
				}
				echo field_label('title', $fields);
				$options = array(
					'' => 'Select',
					'mr' => 'Mr',
					'mrs' => 'Mrs',
					'miss' => 'Miss',
					'ms' => 'Ms',
					'dr' => 'Dr'
				);
				echo form_dropdown('title', $options, set_value('title', $this->crm_library->htmlspecialchars_decode($title), FALSE), 'id="title" class="form-control select2"');
				?>
			</div>
			<?php } ?>
			<?php if (show_field('first_name', $fields)) { ?>
			<div class='form-group'>
				<?php
				$first_name = NULL;
				if (isset($contact_info->first_name)) {
					$first_name = $contact_info->first_name;
				}
				echo field_label('first_name', $fields);
				$data = array(
					'name' => 'first_name',
					'id' => 'first_name',
					'class' => 'form-control',
					'value' => set_value('first_name', $this->crm_library->htmlspecialchars_decode($first_name), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('last_name', $fields)) { ?>
			<div class='form-group'>
				<?php
				$last_name = NULL;
				if (isset($contact_info->last_name)) {
					$last_name = $contact_info->last_name;
				}
				echo field_label('last_name', $fields);
				$data = array(
					'name' => 'last_name',
					'id' => 'last_name',
					'class' => 'form-control',
					'value' => set_value('last_name', $this->crm_library->htmlspecialchars_decode($last_name), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('profile_pic', $fields)) { ?>
			<div class='form-group'>
				<?php
				echo field_label('profile_pic', $fields);
				if (isset($contact_info->profile_pic)) {
					$field_info = $contact_info->profile_pic;
					$image_data = @unserialize($contact_info->profile_pic);
					if ($image_data !== FALSE) {
						$args = array(
							'alt' => 'Image',
							'src' => '//'.ROOT_DOMAIN.'/attachment/participant/profile_pic/thumb/' . $contact_info->contactID,
							'class' => 'responsive-img'
						);
						echo '<p>' . img($args) . '</p>';
					}
				}
				$data = array(
					'name' => 'profile_pic',
					'class' => 'custom-file-input'
				);?>
				<div class="custom-file">
					<?php echo form_upload($data); ?>
				</div>
				<small class="text-muted">Recommended size is 200px by 200px. Thumbnails will be cropped into a square.</small>
			</div>
			<?php } ?>
			<?php
			if (show_field('dob', $fields) && $this->settings_library->get('require_dob', $this->online_booking->accountID) == 1) {
				?><div class='form-group'>
				<?php
				$dob = NULL;
				if (isset($contact_info->dob) && !empty($contact_info->dob)) {
					$dob = mysql_to_uk_date($contact_info->dob);
				}
				echo field_label('dob', $fields);
				$data = array(
					'name' => 'dob',
					'id' => 'dob',
					'class' => 'form-control datepicker datepicker-dob',
					'value' => set_value('dob', $this->crm_library->htmlspecialchars_decode($dob), FALSE)
				);
				echo form_input($data);
				?>
				</div><?php
			}
			?>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('mobile', $fields)) { ?>
			<div class='form-group'>
				<?php
				$mobile = NULL;
				if (isset($contact_info->mobile)) {
					$mobile = $contact_info->mobile;
				}
				echo field_label('mobile', $fields);
				$data = array(
					'name' => 'mobile',
					'id' => 'mobile',
					'class' => 'form-control',
					'value' => set_value('mobile', $this->crm_library->htmlspecialchars_decode($mobile), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('phone', $fields)) { ?>
			<div class='form-group'>
				<?php
				$phone = NULL;
				if (isset($contact_info->phone)) {
					$phone = $contact_info->phone;
				}
				echo field_label('phone', $fields);
				$data = array(
					'name' => 'phone',
					'id' => 'phone',
					'class' => 'form-control',
					'value' => set_value('phone', $this->crm_library->htmlspecialchars_decode($phone), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('workPhone', $fields)) { ?>
			<div class='form-group'>
				<?php
				$workPhone = NULL;
				if (isset($contact_info->workPhone)) {
					$workPhone = $contact_info->workPhone;
				}
				echo field_label('workPhone', $fields);
				$data = array(
					'name' => 'workPhone',
					'id' => 'workPhone',
					'class' => 'form-control',
					'value' => set_value('workPhone', $this->crm_library->htmlspecialchars_decode($workPhone), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<?php } ?>
		</div>
	</div>
</fieldset>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Address</h3>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('address1', $fields)) { ?>
			<div class='form-group'>
				<?php
				$address1 = NULL;
				if (isset($contact_info->address1)) {
					$address1 = $contact_info->address1;
				}
				echo field_label('address1', $fields);
				$data = array(
					'name' => 'address1',
					'id' => 'address1',
					'class' => 'form-control',
					'value' => set_value('address1', $this->crm_library->htmlspecialchars_decode($address1), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('address2', $fields)) { ?>
			<div class='form-group'>
				<?php
				$address2 = NULL;
				if (isset($contact_info->address2)) {
					$address2 = $contact_info->address2;
				}
				echo field_label('address2', $fields);
				$data = array(
					'name' => 'address2',
					'id' => 'address2',
					'class' => 'form-control',
					'value' => set_value('address2', $this->crm_library->htmlspecialchars_decode($address2), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('address3', $fields)) { ?>
			<div class='form-group'>
				<?php
				$address3 = NULL;
				if (isset($contact_info->address3)) {
					$address3 = $contact_info->address3;
				}
				echo field_label('address3', $fields);
				$data = array(
					'name' => 'address3',
					'id' => 'address3',
					'class' => 'form-control',
					'value' => set_value('address3', $this->crm_library->htmlspecialchars_decode($address3), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('town', $fields)) { ?>
			<div class='form-group'>
				<?php
				$town = NULL;
				if (isset($contact_info->town)) {
					$town = $contact_info->town;
				}
				echo field_label('town', $fields);
				$data = array(
					'name' => 'town',
					'id' => 'town',
					'class' => 'form-control',
					'value' => set_value('town', $this->crm_library->htmlspecialchars_decode($town), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('county', $fields)) { ?>
			<div class='form-group'>
				<?php
				$county = NULL;
				if (isset($contact_info->county)) {
					$county = $contact_info->county;
				}
				echo localise('county', $this->cart_library->accountID);
				$data = array(
					'name' => 'county',
					'id' => 'county',
					'class' => 'form-control',
					'value' => set_value('county', $this->crm_library->htmlspecialchars_decode($county), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
			<?php if (show_field('postcode', $fields)) { ?>
			<div class='form-group'>
				<?php
				$postcode = NULL;
				if (isset($contact_info->postcode)) {
					$postcode = $contact_info->postcode;
				}
				echo field_label('postcode', $fields);
				$data = array(
					'name' => 'postcode',
					'id' => 'postcode',
					'class' => 'form-control',
					'value' => set_value('postcode', $this->crm_library->htmlspecialchars_decode($postcode), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<?php } ?>
		</div>
	</div>
</fieldset>
<fieldset class="optional_information">
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Optional Information</h3>
			<p>We require the following information only if you are booking yourself onto one of our events.</p>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('gender', $fields)) { ?>
			<div class='gender form-group'><?php
				echo field_label('gender', $fields);
				$gender = NULL;
				$gender_specify = NULL;
				if (isset($contact_info->gender)) {
					$gender = $contact_info->gender;
				}
				if (isset($contact_info->gender_specify)) {
					$gender_specify = $contact_info->gender_specify;
				}
				$options = array(
					'' => 'Select',
					'male' => 'Male',
					'female' => 'Female',
					'please_specify' => 'Other (please specify)',
					'other' => 'Prefer not to say'
				);
				?>
				<div>
					<?php
					echo form_dropdown('gender', $options, set_value('gender', $this->crm_library->htmlspecialchars_decode($gender), FALSE), 'id="gender" class="form-control select2"');
					$data = array(
						'name' => 'gender_specify',
						'id' => 'gender_specify',
						'placeholder' => 'Please specify',
						'class' => 'form-control'.(set_value('gender', $this->crm_library->htmlspecialchars_decode($gender), FALSE)!="please_specify" ? " hidden" : ""),
						'value' => set_value('gender_specify', $gender_specify, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?>
				</div>
			</div>
			<?php
			}
			if (show_field('dob', $fields) && $this->settings_library->get('require_dob', $this->online_booking->accountID) != 1) {
				?><div class='form-group'>
				<?php
				$dob = NULL;
				if (isset($contact_info->dob) && !empty($contact_info->dob)) {
					$dob = mysql_to_uk_date($contact_info->dob);
				}
				echo field_label('dob', $fields);
				$data = array(
					'name' => 'dob',
					'id' => 'dob',
					'class' => 'form-control datepicker datepicker-dob',
					'value' => set_value('dob', $this->crm_library->htmlspecialchars_decode($dob), FALSE)
				);
				echo form_input($data);
				?>
				</div><?php
			}
			?>
			<?php if (show_field('ethnic_origin', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('ethnic_origin', $fields);
				$ethnic_origin = NULL;
				if (isset($contact_info->ethnic_origin)) {
					$ethnic_origin = $contact_info->ethnic_origin;
				}
				$options = $this->settings_library->ethnic_origins;
				echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', $this->crm_library->htmlspecialchars_decode($ethnic_origin), FALSE), 'id="ethnic_origin" class="form-control select2"');
				?>
			</div>
			<?php } ?>
			<?php if (show_field('religion', $fields)) { ?>
			<div class='religion form-group'><?php
				echo field_label('religion', $fields);
				$religion = NULL;
				$religion_specify = NULL;
				if (isset($contact_info->religion)) {
					$religion = $contact_info->religion;
				}
				if (isset($contact_info->religion_specify)) {
					$religion_specify = $contact_info->religion_specify;
				}
				$options = array_merge(array(NULL => "Select"),$this->settings_library->religions);
				?>
				<div><?php
					echo form_dropdown('religion', $options, set_value('religion', $this->crm_library->htmlspecialchars_decode($religion), FALSE), 'class="form-control select2"');
					$data = array(
						'name' => 'religion_specify',
						'id' => 'religion_specify',
						'placeholder' => 'Please specify',
						'class' => 'form-control'.(set_value('religion', $this->crm_library->htmlspecialchars_decode($religion), FALSE)!="please_specify" ? " hidden" : ""),
						'value' => set_value('religion_specify', $religion_specify, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?>
				</div>
			</div>
			<?php } ?>
			<?php
			//Only show orientation fields if the contact is the main account holder.
			if (!isset($contact_info->main) || $contact_info->main) { ?>
			<?php if (show_field('sexual_orientation', $fields)) { ?>
				<div class='sexual_orientation form-group'><?php
					echo field_label('sexual_orientation', $fields);
					$sexual_orientation = NULL;
					$sexual_orientation_specify = NULL;
					if (isset($contact_info->sexual_orientation)) {
						$sexual_orientation = $contact_info->sexual_orientation;
					}
					if (isset($contact_info->sexual_orientation_specify)) {
						$sexual_orientation_specify = $contact_info->sexual_orientation_specify;
					}
					$options = array_merge(array(NULL => "Select"),$this->settings_library->sexual_orientations);
					?>
					<div class="d-flex"><?php
						echo form_dropdown('sexual_orientation', $options, set_value('sexual_orientation', $this->crm_library->htmlspecialchars_decode($sexual_orientation), FALSE), 'class="form-control select2"');
						$data = array(
							'name' => 'sexual_orientation_specify',
							'id' => 'sexual_orientation_specify',
							'placeholder' => 'Please specify',
							'class' => 'form-control'.(set_value('sexual_orientation', $this->crm_library->htmlspecialchars_decode($sexual_orientation), FALSE)!="please_specify" ? " hidden" : ""),
							'value' => set_value('sexual_orientation_specify', $sexual_orientation_specify, FALSE),
							'maxlength' => 100
						);
						echo form_input($data);
						?>
					</div>
				</div>
				<?php } ?>
				<?php if (show_field('gender_since_birth', $fields)) { ?>
				<div class='form-group'><?php
					echo field_label('gender_since_birth', $fields);
					$gender_since_birth = NULL;
					if (isset($contact_info->gender_since_birth)) {
						$gender_since_birth = $contact_info->gender_since_birth;
					}
					$options = array(NULL => "Select", "yes" => "Yes", "no" => "No", "prefer_not_to_say" => "Prefer not to say");
					echo form_dropdown('gender_since_birth', $options, set_value('gender_since_birth', $this->crm_library->htmlspecialchars_decode($gender_since_birth), FALSE), 'class="form-control select2"');
					?>
				</div>
			<?php } } ?>
			<?php if (show_field('disability', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('disability', $fields);
				foreach ($this->settings_library->disabilities as $p => $checkbox) {
					$data = array(
						'name' => 'disability['.$p.']',
						'id' => 'disability_'.$p,
						'value' => '1',
						'checked' => (bool)(set_value('disability['.$p.']', false) || (set_value('disability', false)==false && isset($contact_info->disability->{$p}) && $contact_info->disability->{$p}==1))
					);
					?>
					<div class="checkbox-single">
						<?php
						echo form_checkbox($data);
						echo $checkbox;
						?>
					</div>
					<?php
				}
				?></div>
			<?php } ?>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('medical', $fields)) { ?>
			<div class='form-group'>
				<?php
				$medical = NULL;
				if (isset($contact_info->medical)) {
					$medical = $contact_info->medical;
				}
				echo field_label('medical', $fields);
				$data = array(
					'name' => 'medical',
					'id' => 'medical',
					'class' => 'form-control',
					'value' => set_value('medical', $this->crm_library->htmlspecialchars_decode($medical), FALSE)
				);
				echo form_textarea($data);
				?>
			</div>
			<?php }
			if (show_field('disability_info', $fields)) { ?>
			<div class='form-group'>
				<?php
				$disability_info = NULL;
				if (isset($contact_info->disability_info)) {
					$disability_info = $contact_info->disability_info;
				}
				echo field_label('disability_info', $fields);
				$data = array(
					'name' => 'disability_info',
					'id' => 'disability_info',
					'class' => 'form-control',
					'value' => set_value('disability_info', $this->crm_library->htmlspecialchars_decode($disability_info), FALSE)
				);
				echo form_textarea($data);
				?>
			</div>
			<?php }
			if (show_field('behavioural_information', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('behavioural_information', $fields);
					$behavioural = NULL;
					if (isset($contact_info->behavioural_info)) {
						$behavioural = $contact_info->behavioural_info;
					}
					$data = array(
						'name' => 'behavioural_information',
						'id' => 'behavioural_information',
						'class' => 'form-control',
						'value' => set_value('behavioural_information', $this->crm_library->htmlspecialchars_decode($behavioural), FALSE)
					);
					echo form_textarea($data);
					?></div>
				<?php
			}
			?>
		</div>
	</div>
</fieldset>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Emergency Contacts</h3>
			<p>Optional</p>
		</div>
		<?php if (show_field('emergency_contact_1_name', $fields) || show_field('emergency_contact_1_phone', $fields) ) { ?>
		<div class="col-sm-6">
			<h4 class="h5">Emergency Contact 1</h4>
			<?php if (show_field('emergency_contact_1_name', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('emergency_contact_1_name', $fields);
				$emergency_contact_1_name = NULL;
				if (isset($contact_info->emergency_contact_1_name)) {
					$emergency_contact_1_name = $contact_info->emergency_contact_1_name;
				}
				$data = array(
					'name' => 'emergency_contact_1_name',
					'id' => 'emergency_contact_1_name',
					'class' => 'form-control',
					'value' => set_value('emergency_contact_1_name', $this->crm_library->htmlspecialchars_decode($emergency_contact_1_name), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
				?></div>
			<?php } ?>
			<?php if (show_field('emergency_contact_1_phone', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('emergency_contact_1_phone', $fields);
				$emergency_contact_1_phone = NULL;
				if (isset($contact_info->emergency_contact_1_phone)) {
					$emergency_contact_1_phone = $contact_info->emergency_contact_1_phone;
				}
				$data = array(
					'name' => 'emergency_contact_1_phone',
					'id' => 'emergency_contact_1_phone',
					'class' => 'form-control',
					'value' => set_value('emergency_contact_1_phone', $this->crm_library->htmlspecialchars_decode($emergency_contact_1_phone), FALSE),
					'maxlength' => 20
				);
				echo form_tel($data);
				?></div>
			<?php } ?>
		</div>
		<?php } ?>
		<?php if (show_field('emergency_contact_2_name', $fields) || show_field('emergency_contact_2_phone', $fields) ) { ?>
		<div class="col-sm-6">
			<h4 class="h5">Emergency Contact 2</h4>
			<?php if (show_field('emergency_contact_2_name', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('emergency_contact_2_name', $fields);
				$emergency_contact_2_name = NULL;
				if (isset($contact_info->emergency_contact_2_name)) {
					$emergency_contact_2_name = $contact_info->emergency_contact_2_name;
				}
				$data = array(
					'name' => 'emergency_contact_2_name',
					'id' => 'emergency_contact_2_name',
					'class' => 'form-control',
					'value' => set_value('emergency_contact_2_name', $this->crm_library->htmlspecialchars_decode($emergency_contact_2_name), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
				?></div>
			<?php } ?>
			<?php if (show_field('emergency_contact_2_phone', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('emergency_contact_2_phone', $fields);
				$emergency_contact_2_phone = NULL;
				if (isset($contact_info->emergency_contact_2_phone)) {
					$emergency_contact_2_phone = $contact_info->emergency_contact_2_phone;
				}
				$data = array(
					'name' => 'emergency_contact_2_phone',
					'id' => 'emergency_contact_2_phone',
					'class' => 'form-control',
					'value' => set_value('emergency_contact_2_phone', $this->crm_library->htmlspecialchars_decode($emergency_contact_2_phone), FALSE),
					'maxlength' => 20
				);
				echo form_tel($data);
				?></div>
			<?php } ?>
		</div>
		<?php } ?>
	</div>
</fieldset>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Login Information</h3>
			<?php
			if ($mode == 'profile') {
				?><p>Leave password fields blank if you don't want to change your password.</p><?php
			}
			?>
		</div>
		<div class="col-sm-6">
			<?php if (show_field('email', $fields)) { ?>
			<div class='form-group'>
				<?php
				$email = NULL;
				if (isset($contact_info->email)) {
					$email = $contact_info->email;
				}
				echo field_label('email', $fields);
				$data = array(
					'name' => 'email',
					'id' => 'email',
					'class' => 'form-control',
					'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE)
				);
				echo form_email($data);
				?>
			</div>
			<?php } ?>
			<?php
			if ($mode != 'profile') {
				?><div class='form-group'>
				<?php
				echo form_label('Confirm Email Address <em>*</em>', 'email_confirm');
				$data = array(
					'name' => 'email_confirm',
					'id' => 'email_confirm',
					'class' => 'form-control',
					'value' => set_value('email_confirm')
				);
				echo form_email($data);
				?>
				</div><?php
			}
			?>
		</div>
		<div class="col-sm-6">
			<div class='form-group'>
				<?php
				$label = 'Password';
				if ($mode == 'register') {
					$label .= ' <em>*</em>';
				}
				echo form_label($label, 'password');
				$data = array(
					'name' => 'password',
					'id' => 'password',
					'class' => 'form-control',
					'value' => set_value('password', NULL, FALSE)
				);
				echo form_password($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$label = 'Confirm Password';
				if ($mode == 'register') {
					$label .= ' <em>*</em>';
				}
				echo form_label($label, 'password_confirm');
				$data = array(
					'name' => 'password_confirm',
					'id' => 'password_confirm',
					'class' => 'form-control',
					'value' => set_value('password_confirm', NULL, FALSE)
				);
				echo form_password($data);
				?>
			</div>
		</div>
	</div>
</fieldset>
<?php
if ($mode == 'register') {
	$this->load->view('online-booking/account/partials/consent');
} else {
	?><fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Confirm Changes</h3>
			<p>Enter your current password to confirm any changed you have made.</p>
		</div>
		<div class="col-sm-6">
			<div class='form-group'>
				<?php
				echo form_label('Current Password <em>*</em>', 'password_current');
				$data = array(
					'name' => 'password_current',
					'id' => 'password_current',
					'class' => 'form-control',
					'value' => set_value('password_current', NULL, FALSE)
				);
				echo form_password($data);
				?>
			</div>
		</div>
	</div>
	</fieldset><?php
}
?>
<button class='btn'><?php
	if ($mode == 'register') {
		echo 'Register';
	} else {
		echo 'Update';
	}
	?></button>
<?php echo form_close(); ?>
