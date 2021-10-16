<?php
display_messages();

if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'class="family account-holder"');
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (show_field('title', $fields)) { ?>
					<div class='form-group'><?php
						echo field_label('title', $fields);
						$title = NULL;
						if (isset($contact_info->title)) {
							$title = $contact_info->title;
						}
						$options = array(
							'' => 'Select',
							'mr' => 'Mr',
							'mrs' => 'Mrs',
							'miss' => 'Miss',
							'ms' => 'Ms',
							'dr' => 'Dr'
						);
						echo form_dropdown('title', $options, set_value('title', $this->crm_library->htmlspecialchars_decode($title), FALSE), 'id="title" class="form-control select2"');
					?></div>
				<?php
				}
				if (show_field('first_name', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('first_name', $fields);
					$first_name = NULL;
					if (isset($contact_info->first_name)) {
						$first_name = $contact_info->first_name;
					}
					$data = array(
						'name' => 'first_name',
						'id' => 'first_name',
						'class' => 'form-control',
						'value' => set_value('first_name', $this->crm_library->htmlspecialchars_decode($first_name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<?php
				}
				if (show_field('last_name', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('last_name', $fields);
					$last_name = NULL;
					if (isset($contact_info->last_name)) {
						$last_name = $contact_info->last_name;
					}
					$data = array(
						'name' => 'last_name',
						'id' => 'last_name',
						'class' => 'form-control',
						'value' => set_value('last_name', $this->crm_library->htmlspecialchars_decode($last_name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<?php
				}
				if (show_field('gender', $fields)) {
				?>
				<div class='form-group'><?php
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
					<div class="d-flex">
					<?php
					echo form_dropdown('gender', $options, set_value('gender', $this->crm_library->htmlspecialchars_decode($gender), FALSE), 'id="gender" class="form-control select2"');
					$data = array(
						'name' => 'gender_specify',
						'id' => 'gender_specify',
						'placeholder' => 'Please specify',
						'class' => 'ml-2 form-control'.(set_value('gender', $this->crm_library->htmlspecialchars_decode($gender), FALSE)!="please_specify" ? " d-none" : ""),
						'value' => set_value('gender_specify', $gender_specify, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?>
					</div>
				</div>
				<?php
				}
				if (show_field('dob', $fields)) {
				?>
				<div class='form-group'><?php
					$label = 'Date of Birth';
					if ($this->settings_library->get('require_dob') == 1) {
						$label .= ' <em>*</em>';
					}
					echo field_label('dob', $fields);
					$dob = NULL;
					if (isset($contact_info->dob)) {
						$dob = date("d/m/Y", strtotime($contact_info->dob));
					}
					$data = array(
						'name' => 'dob',
						'id' => 'dob',
						'class' => 'form-control datepicker datepicker-dob',
						'value' => set_value('dob', $this->crm_library->htmlspecialchars_decode($dob), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<?php
				}
				if (show_field('medical', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('medical', $fields);
						$medical = NULL;
						if (isset($contact_info->medical)) {
							$medical = $contact_info->medical;
						}
						$data = array(
							'name' => 'medical',
							'id' => 'medical',
							'class' => 'form-control',
							'value' => set_value('medical', $this->crm_library->htmlspecialchars_decode($medical), FALSE)
						);
						echo form_textarea($data);
						?></div>
					<?php
				}
				if (show_field('disability', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('disability',$fields);
					foreach ($this->settings_library->disabilities as $p => $checkbox) {
						$data = array(
							'name' => 'disability['.$p.']',
							'id' => 'disability_'.$p,
							'value' => '1',
							'checked' => (bool)(set_value('disability['.$p.']', false) || (set_value('disability', false)==false && isset($contact_info->disability->{$p}) && $contact_info->disability->{$p}==1))
						);
						?>
						<div class="checkbox-single">
							<label class="checkbox">
								<?php
								echo form_checkbox($data);
								echo $checkbox;
								?>
								<span></span>
							</label>
						</div>
						<?php
					}
					?></div>
				<?php
				}
				if (show_field('disability_info', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('disability_info', $fields);
					$disability_info = NULL;
					if (isset($contact_info->disability_info)) {
						$disability_info = $contact_info->disability_info;
					}
					$data = array(
						'name' => 'disability_info',
						'id' => 'disability_info',
						'class' => 'form-control',
						'value' => set_value('disability_info', $this->crm_library->htmlspecialchars_decode($disability_info), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<?php
				}
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
				if (show_field('eRelationship', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('eRelationship', $fields);
					$relationship = NULL;
					if (isset($contact_info->relationship)) {
						$relationship = $contact_info->relationship;
					}
					$options = array(
						'' => 'Select',
						'individual' => 'Individual',
						'parent' => 'Parent',
						'grandparent' => 'Grandparent',
						'guardian' => 'Guardian',
						"parent's friend" => "Parent's Friend",
						'other' => 'Other'
					);
					echo form_dropdown('eRelationship', $options, set_value('eRelationship', $this->crm_library->htmlspecialchars_decode($relationship), FALSE), 'id="eRelationship" class="form-control select2"');
					?></div>
				<?php
				}
				if (show_field('profile_pic', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('profile_pic', $fields);
					if (isset($contact_info->profile_pic)) {
						$field_info = $contact_info->profile_pic;
						$image_data = @unserialize($contact_info->profile_pic);
						if ($image_data !== FALSE) {
							$args = array(
								'alt' => 'Image',
								'src' => 'attachment/participant/profile_pic/thumb/' . $contactID,
								'class' => 'responsive-img'
							);
							echo '<p>' . img($args) . '</p>';
						}
					}
					$data = array(
						'name' => 'profile_pic',
						'id' => 'profile_pic',
						'class' => 'custom-file-input'
					);
					?>
					<div class="custom-file">
						<?php echo form_upload($data); ?>
						<label class="custom-file-label" for="logo">Choose file</label>
					</div>
					<small class="text-muted">Recommended size is 200px by 200px. Thumbnails will be cropped into a square.</small>
				</div>
				<?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']); ?>
	<div class="card-header">
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Diversity</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class="multi-columns">
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
			<?php }
			if (show_field('religion', $fields)) {
			?>
			<div class='form-group'><?php
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
				<div class="d-flex"><?php
				echo form_dropdown('religion', $options, set_value('religion', $this->crm_library->htmlspecialchars_decode($religion), FALSE), 'class="form-control select2"');
				$data = array(
					'name' => 'religion_specify',
					'id' => 'religion_specify',
					'placeholder' => 'Please specify',
					'class' => 'ml-2 form-control'.(set_value('religion', $this->crm_library->htmlspecialchars_decode($religion), FALSE)!="please_specify" ? " d-none" : ""),
					'value' => set_value('religion_specify', $religion_specify, FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
				?>
				</div>
			</div>
			<?php
			}
			//Only show orientation fields if the contact is the main account holder.
			if (isset($contact_info->main) && $contact_info->main) {
				if (show_field('sexual_orientation', $fields)) {?>
			<div class='form-group'><?php
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
						'class' => 'ml-2 form-control'.(set_value('sexual_orientation', $this->crm_library->htmlspecialchars_decode($sexual_orientation), FALSE)!="please_specify" ? " d-none" : ""),
						'value' => set_value('sexual_orientation_specify', $sexual_orientation_specify, FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?>
				</div>
			</div>
					<?php }
				if (show_field('gender_since_birth', $fields)) {
				?>
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
			<?php }
			} ?>
		</div>
	</div>
	<?php echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
				<h3 class="card-label">Address</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (show_field('address1', $fields) || show_field('address2', $fields) || show_field('address3', $fields)) {
					if(show_field('address1', $fields)){
				?>
				<div class='form-group'><?php
					echo field_label('address1', $fields);
					$address1 = NULL;
					if (isset($contact_info->address1)) {
						$address1 = $contact_info->address1;
					}
					$data = array(
						'name' => 'address1',
						'id' => 'address1',
						'class' => 'form-control',
						'value' => set_value('address1', $this->crm_library->htmlspecialchars_decode($address1), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
					}
					if(show_field('address2', $fields)){
					?><br /><?php
					$address2 = NULL;
					if (isset($contact_info->address2)) {
						$address2 = $contact_info->address2;
					}
					$data = array(
						'name' => 'address2',
						'id' => 'address2',
						'class' => 'form-control',
						'value' => set_value('address2', $this->crm_library->htmlspecialchars_decode($address2), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
					}
					if(show_field('address3', $fields)){
					?><br /><?php
					$address3 = NULL;
					if (isset($contact_info->address3)) {
						$address3 = $contact_info->address3;
					}
					$data = array(
						'name' => 'address3',
						'id' => 'address3',
						'class' => 'form-control',
						'value' => set_value('address3', $this->crm_library->htmlspecialchars_decode($address3), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
					}
				?></div>
				<?php
				}
				if (show_field('town', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('town', $fields);
					$town = NULL;
					if (isset($contact_info->town)) {
						$town = $contact_info->town;
					}
					$data = array(
						'name' => 'town',
						'id' => 'town',
						'class' => 'form-control',
						'value' => set_value('town', $this->crm_library->htmlspecialchars_decode($town), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<?php
				}
				if (show_field('county', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('county', $fields);
					$county = NULL;
					if (isset($contact_info->county)) {
						$county = $contact_info->county;
					}
					$data = array(
						'name' => 'county',
						'id' => 'county',
						'class' => 'form-control',
						'value' => set_value('county', $this->crm_library->htmlspecialchars_decode($county), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<?php
				}
				if (show_field('postcode', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('postcode', $fields);
					$postcode = NULL;
					if (isset($contact_info->postcode)) {
						$postcode = $contact_info->postcode;
					}
					$data = array(
						'name' => 'postcode',
						'id' => 'postcode',
						'class' => 'form-control',
						'value' => set_value('postcode', $this->crm_library->htmlspecialchars_decode($postcode), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-phone text-contrast'></i></span>
				<h3 class="card-label">Contact</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (show_field('mobile', $fields)) {
				?>
				<div class='form-group'><?php
					$label = 'Mobile';
					if ($this->settings_library->get('require_mobile') == 1) {
						$label .= ' <em>*</em>';
					}
					echo field_label('mobile', $fields);
					$mobile = NULL;
					if (isset($contact_info->mobile)) {
						$mobile = $contact_info->mobile;
					}
					$data = array(
						'name' => 'mobile',
						'id' => 'mobile',
						'class' => 'form-control',
						'value' => set_value('mobile', $this->crm_library->htmlspecialchars_decode($mobile), FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<?php
				}
				if (show_field('phone', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('phone', $fields);
					$phone = NULL;
					if (isset($contact_info->phone)) {
						$phone = $contact_info->phone;
					}
					$data = array(
						'name' => 'phone',
						'id' => 'phone',
						'class' => 'form-control',
						'value' => set_value('phone', $this->crm_library->htmlspecialchars_decode($phone), FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<?php
				}
				if (show_field('workPhone', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('workPhone', $fields);
					$workPhone = NULL;
					if (isset($contact_info->workPhone)) {
						$workPhone = $contact_info->workPhone;
					}
					$data = array(
						'name' => 'workPhone',
						'id' => 'workPhone',
						'class' => 'form-control',
						'value' => set_value('workPhone', $this->crm_library->htmlspecialchars_decode($workPhone), FALSE),
						'maxlength' => 20
					);
					echo form_tel($data);
				?></div>
				<?php
				}
				if (show_field('email', $fields)) {
				?>
				<div class='form-group'><?php
					$label = 'Email';
					if ($this->settings_library->get('require_participant_email') == 1) {
						$label .= ' <em>*</em>';
					}
					echo field_label('email', $fields);
					$email = NULL;
					if (isset($contact_info->email)) {
						$email = $contact_info->email;
					}
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE),
						'maxlength' => 150,
						'autocomplete' => 'off'
					);
					echo form_email($data);
				?></div>
				<?php
				}
				?>
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
				<?php
				if (show_field('emergency_contact_1_name', $fields)) {
				?>
				<h4 class="lead small">Emergency Contact 1</h4>
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
				<?php
				}
				if (show_field('emergency_contact_1_phone', $fields)) {
				?>
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
				<?php
				}
				if (show_field('emergency_contact_2_name', $fields)) {
				?>
				<h4 class="lead small">Emergency Contact 2</h4>
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
				<?php
				}
				if (show_field('emergency_contact_2_phone', $fields)) {
				?>
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
				<?php
				}
				?>
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
				if ($this->settings_library->get('send_new_participant') == 1 && show_field("notify", $fields)) {
					?><div class='form-group'><?php
						$data = array(
							'name' => 'notify',
							'id' => 'notify',
							'value' => 1
						);
						if (set_value('notify') == 1) {
							$data['checked'] = TRUE;
						}
						?>
						<div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Send login details by email
								<span></span>
							</label>
						</div>
						<?php
						if ($contactID != NULL) {
							?><small class="text-muted form-text">Requires a new password entering as they are stored encrypted and can't be retrieved</small><?php
						}
						?>
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
			<?php
			if (show_field('tags', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('tags', $fields);
				$tags = array();
				if (isset($contact_info->tags) && is_array($contact_info->tags)) {
					$tags = $contact_info->tags;
				}
				$options = array();
				if (count($tag_list) > 0) {
					foreach ($tag_list as $tag) {
						$options[$tag] = $tag;
					}
				}
				echo form_dropdown('tags[]', $options, set_value('tags', $tags), 'id="tags" multiple="multiple" class="form-control select2-tags"');
				?>
				<p class="help-block">
					<small class="text-muted">Start typing to select a tag or create a new one.</small>
				</p>
			</div>
			<?php
			}
			if (show_field('blacklisted', $fields)) {
			?>
			<div class='form-group'><?php
				$blacklisted = NULL;
				if (isset($contact_info->blacklisted)) {
					$blacklisted = $contact_info->blacklisted;
				}
				$data = array(
					'name' => 'blacklisted',
					'id' => 'blacklisted',
					'value' => 1
				);
				if (set_value('blacklisted', $this->crm_library->htmlspecialchars_decode($blacklisted)) == 1) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Block contact from making bookings
						<span></span>
					</label>
				</div>
			</div>
			<?php
			}
			?>
		</div><?php
	echo form_fieldset_close();
	// gdpr consent field
	if ($contactID == NULL && time() >= strtotime('2018-05-25')) {
		$data = array(
			'contact_info' => $contact_info,
			'brands' => $brands
		);
		$this->load->view('participants/privacy-inlay.php', $data);
	}
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
