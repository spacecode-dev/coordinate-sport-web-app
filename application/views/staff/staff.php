<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Personal Information</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (show_field('title', $fields)) {
					?><div class='form-group'><?php
						echo field_label('title', $fields);
						$title = NULL;
						if (isset($staff_info->title)) {
							$title = $staff_info->title;
						}
						$options = array(
							'' => 'Select',
							'mr' => 'Mr',
							'mrs' => 'Mrs',
							'miss' => 'Miss',
							'ms' => 'Ms'
						);
						echo form_dropdown('title', $options, set_value('title', $this->crm_library->htmlspecialchars_decode($title), FALSE), 'id="title" class="form-control select2"');
					?></div><?php
				}
				if (show_field('first', $fields)) {
					?><div class='form-group'><?php
						echo field_label('first', $fields);
						$first = NULL;
						if (isset($staff_info->first)) {
							$first = $staff_info->first;
						}
						$data = array(
							'name' => 'first',
							'id' => 'first',
							'class' => 'form-control',
							'value' => set_value('first', $this->crm_library->htmlspecialchars_decode($first), FALSE),
							'maxlength' => 50
						);
						echo form_input($data);
					?></div><?php
				}
				if (show_field('middle', $fields)) {
					?><div class='form-group'><?php
						echo field_label('middle', $fields);
						$middle = NULL;
						if (isset($staff_info->middle)) {
							$middle = $staff_info->middle;
						}
						$data = array(
							'name' => 'middle',
							'id' => 'middle',
							'class' => 'form-control',
							'value' => set_value('middle', $this->crm_library->htmlspecialchars_decode($middle), FALSE),
							'maxlength' => 50
						);
						echo form_input($data);
					?></div><?php
				}
				if (show_field('surname', $fields)) {
					?><div class='form-group'><?php
						echo field_label('surname', $fields);
						$surname = NULL;
						if (isset($staff_info->surname)) {
							$surname = $staff_info->surname;
						}
						$data = array(
							'name' => 'surname',
							'id' => 'surname',
							'class' => 'form-control',
							'value' => set_value('surname', $this->crm_library->htmlspecialchars_decode($surname), FALSE),
							'maxlength' => 50
						);
						echo form_input($data);
					?></div><?php
				}
				if (show_field('jobTitle', $fields)) {
					?><div class='form-group'><?php
						echo field_label('jobTitle', $fields);
						$jobTitle = NULL;
						if (isset($staff_info->jobTitle)) {
							$jobTitle = $staff_info->jobTitle;
						}
						$data = array(
							'name' => 'jobTitle',
							'id' => 'jobTitle',
							'class' => 'form-control',
							'value' => set_value('jobTitle', $this->crm_library->htmlspecialchars_decode($jobTitle), FALSE),
							'maxlength' => 50
						);
						echo form_input($data);
					?></div><?php
				}
					?>
					<div class='form-group'><?php
						echo form_label('Profile Picture', 'pic');
						if (isset($staff_info->profile_pic)) {
							$field_info = $staff_info->profile_pic;
							$image_data = @unserialize($staff_info->profile_pic);
							if ($image_data !== FALSE) {
								$args = array(
									'alt' => 'Image',
									'src' => 'attachment/staff_profile_pic/profile_pic/thumb/' . $staffID,
									'class' => 'responsive-img'
								);
								echo '<p>' . img($args) . '</p>';
							}
						}
						elseif (!empty($staff_info->id_photo_path)) {
							$args = array(
								'alt' => 'Image',
								'src' => 'attachment/staff-id/' . $staff_info->id_photo_path,
								'class' => 'responsive-img'
							);
							echo '<p>' . img($args) . '</p>';
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
						<small class="text-muted">Minimum size of 200px x 200px. Thumbnails will be cropped into a square.</small>
					</div>
					<?php

				if ($edit_level_and_login === TRUE && show_field('department', $fields)) {
					?><div class='form-group'><?php
						echo field_label('department', $fields);
						$department = NULL;
						if (isset($staff_info->department)) {
							$department = $staff_info->department;
						}
						$options = array(
							'' => 'Select',
							'directors' => $this->settings_library->get_permission_level_label('directors'),
							'management' => $this->settings_library->get_permission_level_label('management'),
							'office' => $this->settings_library->get_permission_level_label('office'),
							'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
							'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
							'coaching' => $this->settings_library->get_permission_level_label('coaching')
						);
						// remove levels so can't create a user of higher level
						switch ($this->auth->user->department) {
							case 'office':
								unset($options['directors']);
								unset($options['management']);
								break;
							case 'management':
								unset($options['directors']);
								break;
						}
						echo form_dropdown('department', $options, set_value('department', $this->crm_library->htmlspecialchars_decode($department), FALSE), 'id="department" class="form-control select2"');
					?></div><?php
				}
				if (show_field('non_delivery', $fields)) {
					?><div class='form-group'><?php
						$data = array(
							'name' => 'non_delivery',
							'id' => 'non_delivery',
							'value' => 1
						);
						$non_delivery = NULL;
						if (isset($staff_info->non_delivery)) {
							$non_delivery = $staff_info->non_delivery;
						}
						if (set_value('non_delivery', $this->crm_library->htmlspecialchars_decode($non_delivery), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								<?php echo field_label('non_delivery', $fields); ?>
								<span></span>
							</label>
						</div>
						<span class="form-text text-muted">If selected, this user won't show when staffing, "Your Timetable" link will be removed and they won't have to confirm their timetable.</span>
					</div><?php
				}
				if (show_field('brandID', $fields)) {
					?><div class='form-group'><?php
						echo form_label('Primary ' . $this->settings_library->get_label('brand') . required_field('brandID', $fields, 'label'), 'brandID');
						$brandID = NULL;
						if (isset($staff_info->brandID)) {
							$brandID = $staff_info->brandID;
						}
						$options = array(
							'' => 'Select'
						);
						if ($brands->num_rows() > 0) {
							foreach ($brands->result() as $row) {
								$options[$row->brandID] = $row->name;
							}
						}
						echo form_dropdown('brandID', $options, set_value('brandID', $this->crm_library->htmlspecialchars_decode($brandID), FALSE), 'id="brandID" class="form-control select2"');
					?></div><?php
				}
				if (show_field('nationalInsurance', $fields)) {
					?><div class='form-group'><?php
						echo form_label('<abbr title="National Insurance">NI</abbr> Number' . required_field('nationalInsurance', $fields, 'label'), 'nationalInsurance');
						$nationalInsurance = NULL;
						if (isset($staff_info->nationalInsurance)) {
							$nationalInsurance = $staff_info->nationalInsurance;
						}
						$data = array(
							'name' => 'nationalInsurance',
							'id' => 'nationalInsurance',
							'class' => 'form-control',
							'value' => set_value('nationalInsurance', $this->crm_library->htmlspecialchars_decode($nationalInsurance), FALSE),
							'maxlength' => 20
						);
						echo form_input($data);
					?></div><?php
				}
				if (show_field('dob', $fields)) {
					?><div class='form-group'><?php
						echo field_label('dob', $fields);
						$dob = NULL;
						if (isset($staff_info->dob)) {
							$dob = mysql_to_uk_date($staff_info->dob);
						}
						$data = array(
							'name' => 'dob',
							'id' => 'dob',
							'class' => 'form-control datepicker datepicker-dob',
							'value' => set_value('dob', $this->crm_library->htmlspecialchars_decode($dob), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div><?php
				}
				?>
			</div>
		</div>
		<?php
	echo form_fieldset_close();
	if ($edit_level_and_login === TRUE) {
		echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
					<h3 class="card-label">Login Information</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (show_field('email', $fields)) {
						?><div class='form-group'><?php
							echo field_label('email', $fields);
							$email = NULL;
							if (isset($staff_info->email)) {
								$email = $staff_info->email;
							}
							$data = array(
								'name' => 'email',
								'id' => 'email',
								'class' => 'form-control',
								'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE),
								'maxlength' => 100,
								'autocomplete' => 'off'
							);
							echo form_email($data);
						?></div><?php
					}
					if ($this->auth->account_overridden === TRUE) {
						?><div class='form-group'>
							<?php
							$label = 'Password (<a href="#" class="generatepassword">Generate?)</a>';
							if ($staffID == NULL) {
								$label .= ' <em>*</em>';
							}
							echo form_label($label, 'password');
							$data = array(
								'name' => 'password',
								'id' => 'password',
								'class' => 'form-control pwstrength',
								'value' => set_value('password', NULL, FALSE),
								'autocomplete' => 'off'
							);
							echo form_password($data);
							?>
						</div>
						<div class='form-group'><?php
							$label = 'Password';
							if ($staffID == NULL) {
								$label .= ' <em>*</em>';
							}
							echo form_label('Confirm ' . $label, 'password_confirm');
							$data = array(
								'name' => 'password_confirm',
								'id' => 'password_confirm',
								'class' => 'form-control pwstrength',
								'value' => set_value('password_confirm', NULL, FALSE),
								'autocomplete' => 'off'
							);
							echo form_password($data);
						?></div><?php
					}
					if ($this->settings_library->get('send_new_staff') == 1 && show_field('notify', $fields)) {
						?><div class='form-group'><?php
							$data = array(
								'name' => 'notify',
								'id' => 'notify',
								'value' => 1
							);
							if (set_value('notify') == 1) {
								$data['checked'] = TRUE;
							}
							if (!$this->input->post() && $staffID === NULL) {
								$data['checked'] = TRUE;
							}
							?>
							<div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									<?php echo field_label('notify', $fields); ?>
									<span></span>
								</label>
							</div>
							<span class="form-text text-muted">A temporary password will be sent and on next login, the staff member will be asked to choose a new password</span>
						</div><?php
					}
					?>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	if (show_field('address1', $fields) || show_field('address2', $fields) || show_field('town', $fields) || show_field('county', $fields) || show_field('postcode', $fields) || show_field('from', $fields) || show_field('phone', $fields) || show_field('mobile', $fields) || show_field('mobile_work', $fields)) {
		?>
		<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class="card-header">
				<div class="card-title">
					<span class="card-icon"><i class="far fa-phone text-contrast"></i></span>
					<h3 class="card-label">Contact Information</h3>
					<small>To add additional addresses, please go to the <?php echo anchor('staff/addresses/'.$staffID, 'Addresses &amp; Contacts'); ?> tab.</small>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (show_field('address1', $fields)) {
						?><div class='form-group'><?php
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
								'maxlength' => 100
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('address2', $fields)) {
						?><div class='form-group'><?php
							echo field_label('address2', $fields);
							$address2 = NULL;
							if (isset($contact_info->address2)) {
								$address2 = $contact_info->address2;
							}
							$data = array(
								'name' => 'address2',
								'id' => 'address2',
								'class' => 'form-control',
								'value' => set_value('address2', $this->crm_library->htmlspecialchars_decode($address2), FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('town', $fields)) {
						?><div class='form-group'><?php
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
						?></div><?php
					}
					if (show_field('county', $fields)) {
						?><div class='form-group'><?php
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
						?></div><?php
					}
					if (show_field('postcode', $fields)) {
						?><div class='form-group'><?php
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
						?></div><?php
					}
					if (show_field('from', $fields)) {
						?><div class='form-group'><?php
							echo field_label('fromM', $fields);
							$fromM = NULL;
							if (isset($contact_info->from)) {
								$fromM = date("n", strtotime($contact_info->from));
							}
							$options = array(
								'' => 'Select',
								'1' => 'January',
								'2' => 'February',
								'3' => 'March',
								'4' => 'April',
								'5' => 'May',
								'6' => 'June',
								'7' => 'July',
								'8' => 'August',
								'9' => 'September',
								'10' => 'October',
								'11' => 'November',
								'12' => 'December'
							);
							echo form_dropdown('fromM', $options, set_value('fromM', $this->crm_library->htmlspecialchars_decode($fromM), FALSE), 'id="fromM" class="form-control select2"');
							$fromY = NULL;
							if (isset($contact_info->from)) {
								$fromY = date("Y", strtotime($contact_info->from));
							}
							$options = array(
								'' => 'Select',
							);
							$y = date("Y");
							while ($y >= date("Y")-100) {
								$options[$y] = $y;
								$y--;
							}
							echo form_dropdown('fromY', $options, set_value('fromY', $this->crm_library->htmlspecialchars_decode($fromY), FALSE), 'id="fromY" class="form-control select2"');
						?></div><?php
					}
					if (show_field('phone', $fields)) {
						?><div class='form-group'><?php
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
							echo form_input($data);
						?></div><?php
					}
					if (show_field('mobile', $fields)) {
						?><div class='form-group'><?php
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
							echo form_input($data);
						?></div><?php
					}
					if (show_field('mobile_work', $fields)) {
						?><div class='form-group'><?php
							echo field_label('mobile_work', $fields);
							$mobile_work = NULL;
							if (isset($contact_info->mobile_work)) {
								$mobile_work = $contact_info->mobile_work;
							}
							$data = array(
								'name' => 'mobile_work',
								'id' => 'mobile_work',
								'class' => 'form-control',
								'value' => set_value('mobile_work', $this->crm_library->htmlspecialchars_decode($mobile_work), FALSE),
								'maxlength' => 20
							);
							echo form_input($data);
						?></div><?php
					}
					?>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	if ($staffID == NULL && (show_field('eName', $fields) || show_field('eRelationship', $fields) || show_field('eAddress1', $fields) || show_field('eAddress2', $fields) || show_field('eTown', $fields) || show_field('eCounty', $fields) || show_field('ePostcode', $fields) || show_field('ePhone', $fields) || show_field('eMobile', $fields))) {
		echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class="card-header">
				<div class="card-title">
					<span class="card-icon"><i class="far fa-phone text-contrast"></i></span>
					<h3 class="card-label">Emergency Contact</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (show_field('eName', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eName', $fields);
							$data = array(
								'name' => 'eName',
								'id' => 'eName',
								'class' => 'form-control',
								'value' => set_value('eName', NULL, FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eRelationship', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eRelationship', $fields);
							$data = array(
								'name' => 'eRelationship',
								'id' => 'eRelationship',
								'class' => 'form-control',
								'value' => set_value('eRelationship', NULL, FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eAddress1', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eAddress1', $fields);
							$data = array(
								'name' => 'eAddress1',
								'id' => 'eAddress1',
								'class' => 'form-control',
								'value' => set_value('eAddress1', NULL, FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eAddress2', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eAddress2', $fields);
							$data = array(
								'name' => 'eAddress2',
								'id' => 'eAddress2',
								'class' => 'form-control',
								'value' => set_value('eAddress2', NULL, FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eTown', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eTown', $fields);
							$eTown = NULL;
							if (isset($contact_info->eTown)) {
								$eTown = $contact_info->eTown;
							}
							$data = array(
								'name' => 'eTown',
								'id' => 'eTown',
								'class' => 'form-control',
								'value' => set_value('eTown', NULL, FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eCounty', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eCounty', $fields);
							$data = array(
								'name' => 'eCounty',
								'id' => 'eCounty',
								'class' => 'form-control',
								'value' => set_value('eCounty', NULL, FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('ePostcode', $fields)) {
						?><div class='form-group'><?php
							echo field_label('ePostcode', $fields);
							$data = array(
								'name' => 'ePostcode',
								'id' => 'ePostcode',
								'class' => 'form-control',
								'value' => set_value('ePostcode', NULL, FALSE),
								'maxlength' => 10
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('ePhone', $fields)) {
						?><div class='form-group'><?php
							echo field_label('ePhone', $fields);
							$data = array(
								'name' => 'ePhone',
								'id' => 'ePhone',
								'class' => 'form-control',
								'value' => set_value('ePhone', NULL, FALSE),
								'maxlength' => 20
							);
							echo form_input($data);
						?></div><?php
					}
					if (show_field('eMobile', $fields)) {
						?><div class='form-group'><?php
							echo field_label('eMobile', $fields);
							$data = array(
								'name' => 'eMobile',
								'id' => 'eMobile',
								'class' => 'form-control',
								'value' => set_value('eMobile', NULL, FALSE),
								'maxlength' => 20
							);
							echo form_input($data);
						?></div><?php
					}
					?>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	if (show_field('equal_ethnic', $fields) || show_field('equal_disability', $fields) || show_field('equal_source', $fields)) {
		echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class="card-header">
				<div class="card-title">
					<span class="card-icon"><i class="far fa-user text-contrast"></i></span>
					<h3 class="card-label">Equal Opportunities</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (show_field('equal_ethnic', $fields)) {
						?><div class='form-group'><?php
							echo field_label('equal_ethnic', $fields);
							$equal_ethnic = NULL;
							if (isset($staff_info->equal_ethnic)) {
								$equal_ethnic = $staff_info->equal_ethnic;
							}
							$options = $this->settings_library->ethnic_origins;
							echo form_dropdown('equal_ethnic', $options, set_value('equal_ethnic', $this->crm_library->htmlspecialchars_decode($equal_ethnic), FALSE), 'id="equal_ethnic" class="form-control select2"');
						?></div><?php
					}
					if (show_field('equal_disability', $fields)) {
						?><div class='form-group'><?php
							echo field_label('equal_disability', $fields);
							$equal_disability = NULL;
							if (isset($staff_info->equal_disability)) {
								$equal_disability = $staff_info->equal_disability;
							}
							// convert pre-wysiwyg fields to html
							if ($equal_disability == strip_tags($equal_disability)) {
								$equal_disability = '<p>' . nl2br($equal_disability) . '</p>';
							}
							$data = array(
								'name' => 'equal_disability',
								'id' => 'equal_disability',
								'class' => 'form-control wysiwyg',
								'value' => set_value('equal_disability', $this->crm_library->htmlspecialchars_decode($equal_disability), FALSE),								);
							echo form_textarea($data);
						?></div><?php
					}
					if (show_field('equal_source', $fields)) {
						?><div class='form-group'><?php
							echo field_label('equal_source', $fields);
							$equal_source = NULL;
							if (isset($staff_info->equal_source)) {
								$equal_source = $staff_info->equal_source;
							}
							$data = array(
								'name' => 'equal_source',
								'id' => 'equal_source',
								'class' => 'form-control',
								'value' => set_value('equal_source', $this->crm_library->htmlspecialchars_decode($equal_source), FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div><?php
					}
					?>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	if (show_field('medical', $fields) || show_field('tshirtSize', $fields) || ($this->auth->has_features('online_booking') && show_field('onsite', $fields))) {
		 echo form_fieldset('', ['class' => 'card card-custom']);	?>
			 <div class="card-header">
				 <div class="card-title">
					 <span class="card-icon"><i class="far fa-book text-contrast"></i></span>
					 <h3 class="card-label">Miscellaneous Information</h3>
				 </div>
			 </div>
			 <div class="card-body">
				<div class='multi-columns'>
					<?php
					if (show_field('medical', $fields)) {
						?><div class='form-group'><?php
							echo field_label('medical', $fields);
							$medical = NULL;
							if (isset($staff_info->medical)) {
								$medical = $staff_info->medical;
							}
							// convert pre-wysiwyg fields to html
							if ($medical == strip_tags($medical)) {
								$medical = '<p>' . nl2br($medical) . '</p>';
							}
							$data = array(
								'name' => 'medical',
								'id' => 'medical',
								'class' => 'form-control wysiwyg',
								'value' => set_value('medical', $this->crm_library->htmlspecialchars_decode($medical), FALSE),								);
							echo form_textarea($data);
						?></div><?php
					}
					if (show_field('tshirtSize', $fields)) {
						?><div class='form-group'><?php
							echo field_label('tshirtSize', $fields);
							$tshirtSize = NULL;
							if (isset($staff_info->tshirtSize)) {
								$tshirtSize = $staff_info->tshirtSize;
							}
							$options = array(
								'' => 'Select',
								'xs' => 'XS',
								's' => 'S',
								'm' => 'M',
								'l' => 'L',
								'xl' => 'XL',
								'xxl' => 'XXL',
							);
							echo form_dropdown('tshirtSize', $options, set_value('tshirtSize', $this->crm_library->htmlspecialchars_decode($tshirtSize), FALSE), 'id="tshirtSize" class="form-control select2"');
						?></div><?php
					}
					if ($this->auth->has_features('online_booking') && show_field('onsite', $fields)) {
						?><div class='form-group'><?php
							echo field_label('onsite', $fields);
							$data = array(
								'name' => 'onsite',
								'id' => 'onsite',
								'value' => 1
							);
							$onsite = NULL;
							if (isset($staff_info->onsite)) {
								$onsite = $staff_info->onsite;
							}
							if (set_value('onsite', $this->crm_library->htmlspecialchars_decode($onsite), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div>
						</div><?php
					}
					?>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div><?php
echo form_close();
