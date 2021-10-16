<h1 class="h4 with-line"><?php echo $title; ?></h1>
<?php
display_messages($fa_weight);
if (!empty($success)) {
	?><script>
		window.parent.lightbox_callback(<?php echo json_encode($child_info); ?>);
	</script><?php
} else {
	echo form_open_multipart("", 'id="part_Form"');
	?><fieldset>
	<div class="row p-5">
		<div class="col-xs-12">
			<?php if (show_field('first_name', $fields)) { ?>
			<div class='form-group'>
				<?php
				$first_name = NULL;
				if (isset($child_info->first_name)) {
					$first_name = $child_info->first_name;
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
				if (isset($child_info->last_name)) {
					$last_name = $child_info->last_name;
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
			<div class='form-group'>
				<?php if (show_field('gender', $fields)) { ?>
					<div class='gender form-group'><?php
						echo field_label('gender', $fields);
						$gender = NULL;
						$gender_specify = NULL;
						if (isset($child_info->gender)) {
							$gender = $child_info->gender;
						}
						if (isset($child_info->gender_specify)) {
							$gender_specify = $child_info->gender_specify;
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
				?>
			</div>
			<?php if (show_field('dob', $fields)) { ?>
			<div class='form-group'>
				<?php
				$dob = NULL;
				if (isset($child_info->dob) && !empty($child_info->dob)) {
					$dob = mysql_to_uk_date($child_info->dob);
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
			</div>
			<?php } ?>
			<?php if (show_field('orgID', $fields)) { ?>
			<div class='form-group'>
				<?php
				$orgID = NULL;
				if (isset($child_info->orgID)) {
					$orgID = $child_info->orgID;
				}
				echo field_label('orgID', $fields);
				$options = array(
					'' => 'Select'
				);
				if ($schools->num_rows() > 0) {
					foreach ($schools->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('orgID', $options, set_value('orgID', $this->crm_library->htmlspecialchars_decode($orgID), FALSE), 'id="orgID" class="form-control select2"');
				?>
				<p class="help-text text-right"><a href="#" class="new-school">School not listed?</a></p>
			</div>
			<?php
				$data = array(
					'add_school' => set_value('add_school')
				);
				echo form_hidden($data);
			?>
			<div class='form-group'>
				<?php
				echo field_label('orgID', $fields);
				$data = array(
					'name' => 'new_school',
					'id' => 'new_school',
					'class' => 'form-control',
					'value' => set_value('new_school', NULL, FALSE)
				);
				echo form_input($data);
				?>
				<p class="help-text text-right"><a href="#" class="new-school">Select from list?</a></p>
			</div>
			<?php } ?>
			<?php if (show_field('pin', $fields)) { ?>
			<div class='form-group'>
				<?php
				$pin = NULL;
				if (isset($child_info->pin)) {
					$pin = $child_info->pin;
				}
				echo field_label('pin', $fields);
				$data = array(
					'name' => 'pin',
					'id' => 'pin',
					'pattern' => '\d{4}',
					'maxlength' => "4",
					'class' => 'form-control',
					'value' => set_value('pin', ($pin != 0 ?($this->crm_library->htmlspecialchars_decode($pin)):''), FALSE)
				);
				echo form_input($data);

				?>
				<small class="text-muted">If entered, this four digit PIN will be asked to the person who picks up the participant at the end of their session. This is used to protect children or vunerable participants so that their coach/instructor knows who is authorised to pick them up</small>
			</div>
			<?php } ?>
			<?php if (show_field('ethic_origin', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('ethnic_origin', $fields);
				$ethnic_origin = NULL;
				if (isset($child_info->ethnic_origin)) {
					$ethnic_origin = $child_info->ethnic_origin;
				}
				$options = $this->settings_library->ethnic_origins;
				echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', $this->crm_library->htmlspecialchars_decode($ethnic_origin), FALSE), 'id="ethnic_origin" class="form-control select2"');
				?></div>
			<?php } ?>
			<?php if (show_field('photoConsent', $fields)) { ?>
			<div class="checkbox">
				<label>
					<?php
					$photoConsent = NULL;
					if (isset($child_info->photoConsent)) {
						$photoConsent = $child_info->photoConsent;
					}
					$data = array(
						'name' => 'photoConsent',
						'id' => 'photoConsent',
						'value' => 1
					);
					if (set_value('photoConsent', $this->crm_library->htmlspecialchars_decode($photoConsent), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					echo form_checkbox($data);
					?>Photo Consent<?php echo required_field("photoConsent", $fields) ? " <em>*</em>" : ""; ?>
				</label>
			</div>
			<?php } ?>
			<?php if (show_field('profile_pic', $fields)) { ?>
			<div class='form-group'>
				<?php
				echo field_label('profile_pic', $fields);
				if (isset($child_info->profile_pic)) {
					$field_info = $child_info->profile_pic;
					$image_data = @unserialize($child_info->profile_pic);
					if ($image_data !== FALSE) {
						$args = array(
							'alt' => 'Image',
							'src' => '//'.ROOT_DOMAIN.'/attachment/participant_child/profile_pic/thumb/' . $child_info->childID,
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
			<h3 class="h4 with-line">Emergency Contacts</h3>
			<h4 class="h5">Emergency Contact 1</h4>
			<?php if (show_field('emergency_contact_1_name', $fields)) { ?>
			<div class='form-group'><?php
				$label = 'Name';
				if ($emergency_contact_1_required === TRUE) {
					$label .= ' <em>*</em>';
				}
				echo field_label('emergency_contact_1_name', $fields);
				$emergency_contact_1_name = NULL;
				if (isset($child_info->emergency_contact_1_name)) {
					$emergency_contact_1_name = $child_info->emergency_contact_1_name;
				}else{
					$emergency_contact_1_name = $account_holder_info->first_name." ".$account_holder_info->last_name;

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
				$label = 'Contact Number';
				if ($emergency_contact_1_required === TRUE) {
					$label .= ' <em>*</em>';
				}
				echo field_label('emergency_contact_1_phone', $fields);
				$emergency_contact_1_phone = NULL;
				if (isset($child_info->emergency_contact_1_phone)) {
					$emergency_contact_1_phone = $child_info->emergency_contact_1_phone;
				}else{
					$emergency_contact_1_phone = $account_holder_info->mobile;
					if (!empty($account_holder_info->phone)) {
						$emergency_contact_1_phone .= (!empty($emergency_contact_1_phone) ? ", " : "").$account_holder_info->phone;
					}
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
			<h4 class="h5">Emergency Contact 2</h4>
			<?php if (show_field('emergency_contact_2_name', $fields)) { ?>
			<div class='form-group'><?php
				echo field_label('emergency_contact_2_name', $fields);
				$emergency_contact_2_name = NULL;
				if (isset($child_info->emergency_contact_2_name)) {
					$emergency_contact_2_name = $child_info->emergency_contact_2_name;
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
				echo field_label('emergency_contact_1_phone', $fields);
				$emergency_contact_2_phone = NULL;
				if (isset($child_info->emergency_contact_2_phone)) {
					$emergency_contact_2_phone = $child_info->emergency_contact_2_phone;
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
	</div>
	</fieldset>
	<fieldset class="optional_information">
		<div class="row">
			<div class="col-xs-12">
				<h3 class="h4 with-line">Optional Information</h3>
			</div>
			<div class="col-sm-6">
				<?php if (show_field('ethnic_origin', $fields)) { ?>
					<div class='form-group'><?php
						echo field_label('ethnic_origin', $fields);
						$ethnic_origin = NULL;
						if (isset($child_info->ethnic_origin)) {
							$ethnic_origin = $child_info->ethnic_origin;
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
						if (isset($child_info->religion)) {
							$religion = $child_info->religion;
						}
						if (isset($child_info->religion_specify)) {
							$religion_specify = $child_info->religion_specify;
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
				<?php if (show_field('disability', $fields)) { ?>
					<div class='form-group'><?php
						echo field_label('disability', $fields);
						foreach ($this->settings_library->disabilities as $p => $checkbox) {
							$data = array(
								'name' => 'disability['.$p.']',
								'id' => 'disability_'.$p,
								'value' => '1',
								'checked' => (bool)(set_value('disability['.$p.']', false) || (set_value('disability', false)==false && isset($child_info->disability->{$p}) && $child_info->disability->{$p}==1))
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
						if (isset($child_info->medical)) {
							$medical = $child_info->medical;
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
				<?php } ?>
				<?php if (show_field('disability_info', $fields)) { ?>
					<div class='form-group'>
						<?php
						$disability_info = NULL;
						if (isset($child_info->disability_info)) {
							$disability_info = $child_info->disability_info;
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
				<?php } ?>
				<?php if (show_field('behavioural_information', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('behavioural_information', $fields);
						$behavioural = NULL;
						if (isset($child_info->behavioural_info)) {
							$behavioural = $child_info->behavioural_info;
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
				} ?>
			</div>
		</div>
	</fieldset>

	<button class='btn mb-10 <?php if ($in_crm) { echo 'btn-primary'; } ?>'><?php
	if (empty($child_info)) {
		echo 'Add';
	} else {
		echo 'Update';
	}
	?></button><?php
	echo form_close();
}
