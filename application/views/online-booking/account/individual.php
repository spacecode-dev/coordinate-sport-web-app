<h1 class="h4 with-line"><?php echo $title; ?></h1>
<?php
display_messages($fa_weight);
if (!empty($success)) {
	?><script>
		window.parent.lightbox_callback(<?php echo json_encode($contact_info); ?>);
	</script><?php
} else {
	echo form_open_multipart();
	?><fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Personal Details</h3>
			<div class='form-group'>
				<?php
				$title = NULL;
				if (isset($contact_info->title)) {
					$title = $contact_info->title;
				}
				echo form_label('Title', 'title');
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
			<div class='form-group'>
				<?php
				$first_name = NULL;
				if (isset($contact_info->first_name)) {
					$first_name = $contact_info->first_name;
				}
				echo form_label('First Name <em>*</em>', 'first_name');
				$data = array(
					'name' => 'first_name',
					'id' => 'first_name',
					'class' => 'form-control',
					'value' => set_value('first_name', $this->crm_library->htmlspecialchars_decode($first_name), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$last_name = NULL;
				if (isset($contact_info->last_name)) {
					$last_name = $contact_info->last_name;
				}
				echo form_label('Last Name <em>*</em>', 'last_name');
				$data = array(
					'name' => 'last_name',
					'id' => 'last_name',
					'class' => 'form-control',
					'value' => set_value('last_name', $this->crm_library->htmlspecialchars_decode($last_name), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$dob = NULL;
				if (isset($contact_info->dob) && !empty($contact_info->dob)) {
					$dob = mysql_to_uk_date($contact_info->dob);
				}
				echo form_label('Date of Birth <em>*</em>', 'dob');
				$data = array(
					'name' => 'dob',
					'id' => 'dob',
					'class' => 'form-control datepicker datepicker-dob',
					'value' => set_value('dob', $this->crm_library->htmlspecialchars_decode($dob), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				echo form_label('Profile Picture', 'pic');
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
			<h3 class="h4 with-line">Contact Details</h3>
			<div class='form-group'>
				<?php
				$label = 'Email';
				if ($this->settings_library->get('require_participant_email', $this->cart_library->accountID) == 1) {
					$label .= ' <em>*</em>';
				}
				$email = NULL;
				if (isset($contact_info->email)) {
					$email = $contact_info->email;
				}
				echo form_label($label, 'email');
				$data = array(
					'name' => 'email',
					'id' => 'email',
					'class' => 'form-control',
					'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE)
				);
				echo form_email($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$label = 'Mobile';
				if ($this->settings_library->get('require_mobile', $this->cart_library->accountID) == 1) {
					$label .= ' <em>*</em>';
				}
				$mobile = NULL;
				if (isset($contact_info->mobile)) {
					$mobile = $contact_info->mobile;
				}
				echo form_label($label, 'mobile');
				$data = array(
					'name' => 'mobile',
					'id' => 'mobile',
					'class' => 'form-control',
					'value' => set_value('mobile', $this->crm_library->htmlspecialchars_decode($mobile), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$phone = NULL;
				if (isset($contact_info->phone)) {
					$phone = $contact_info->phone;
				}
				echo form_label('Other Phone', 'phone');
				$data = array(
					'name' => 'phone',
					'id' => 'phone',
					'class' => 'form-control',
					'value' => set_value('phone', $this->crm_library->htmlspecialchars_decode($phone), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$workPhone = NULL;
				if (isset($contact_info->workPhone)) {
					$workPhone = $contact_info->workPhone;
				}
				echo form_label('Work Phone', 'workPhone');
				$data = array(
					'name' => 'workPhone',
					'id' => 'workPhone',
					'class' => 'form-control',
					'value' => set_value('workPhone', $this->crm_library->htmlspecialchars_decode($workPhone), FALSE)
				);
				echo form_tel($data);
				?>
			</div>
			<h3 class="h4 with-line">Address</h3>
			<div class='form-group'>
				<?php
				$address1 = NULL;
				if (isset($contact_info->address1)) {
					$address1 = $contact_info->address1;
				}
				echo form_label('Address 1 <em>*</em>', 'address1');
				$data = array(
					'name' => 'address1',
					'id' => 'address1',
					'class' => 'form-control',
					'value' => set_value('address1', $this->crm_library->htmlspecialchars_decode($address1), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$address2 = NULL;
				if (isset($contact_info->address2)) {
					$address2 = $contact_info->address2;
				}
				echo form_label('Address 2', 'address2');
				$data = array(
					'name' => 'address2',
					'id' => 'address2',
					'class' => 'form-control',
					'value' => set_value('address2', $this->crm_library->htmlspecialchars_decode($address2), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$address3 = NULL;
				if (isset($contact_info->address3)) {
					$address3 = $contact_info->address3;
				}
				echo form_label('Address 3', 'address3');
				$data = array(
					'name' => 'address3',
					'id' => 'address3',
					'class' => 'form-control',
					'value' => set_value('address3', $this->crm_library->htmlspecialchars_decode($address3), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$town = NULL;
				if (isset($contact_info->town)) {
					$town = $contact_info->town;
				}
				echo form_label('Town <em>*</em>', 'town');
				$data = array(
					'name' => 'town',
					'id' => 'town',
					'class' => 'form-control',
					'value' => set_value('town', $this->crm_library->htmlspecialchars_decode($town), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$county = NULL;
				if (isset($contact_info->county)) {
					$county = $contact_info->county;
				}
				echo form_label(localise('county', $this->cart_library->accountID) . ' <em>*</em>', 'county');
				$data = array(
					'name' => 'county',
					'id' => 'county',
					'class' => 'form-control',
					'value' => set_value('county', $this->crm_library->htmlspecialchars_decode($county), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$postcode = NULL;
				if (isset($contact_info->postcode)) {
					$postcode = $contact_info->postcode;
				}
				echo form_label('Post Code <em>*</em>', 'postcode');
				$data = array(
					'name' => 'postcode',
					'id' => 'postcode',
					'class' => 'form-control',
					'value' => set_value('postcode', $this->crm_library->htmlspecialchars_decode($postcode), FALSE)
				);
				echo form_input($data);
				?>
			</div>
			<h3 class="h4 with-line">Optional Information</h3>
			<div class='form-group'>
				<?php
				$gender = NULL;
				if (isset($contact_info->gender)) {
					$gender = $contact_info->gender;
				}
				echo form_label('Gender', 'gender');
				$options = array(
					'' => 'Select',
					'male' => 'Male',
					'female' => 'Female',
					'other' => 'Other - Prefer not to say'
				);
				echo form_dropdown('gender', $options, set_value('gender', $this->crm_library->htmlspecialchars_decode($gender), FALSE), 'id="gender" class="form-control select2"');
				?>
			</div>
			<div class='form-group'>
				<?php
				$medical = NULL;
				if (isset($contact_info->medical)) {
					$medical = $contact_info->medical;
				}
				echo form_label('Any Medical Information', 'medical');
				$data = array(
					'name' => 'medical',
					'id' => 'medical',
					'class' => 'form-control',
					'value' => set_value('medical', $this->crm_library->htmlspecialchars_decode($medical), FALSE)
				);
				echo form_textarea($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$disability_info = NULL;
				if (isset($contact_info->disability_info)) {
					$disability_info = $contact_info->disability_info;
				}
				echo form_label('Any Disability Information', 'disability_info');
				$data = array(
					'name' => 'disability_info',
					'id' => 'disability_info',
					'class' => 'form-control',
					'value' => set_value('disability_info', $this->crm_library->htmlspecialchars_decode($disability_info), FALSE)
				);
				echo form_textarea($data);
				?>
			</div>
			<div class='form-group'>
				<?php
				$behavioural = NULL;
				if (isset($contact_info->behavioural_info)) {
					$behavioural = $contact_info->behavioural_info;
				}
				echo form_label('Behavioural Information', 'behavioural_information');
				$data = array(
					'name' => 'behavioural_information',
					'id' => 'behavioural_information',
					'class' => 'form-control',
					'value' => set_value('behavioural_information', $this->crm_library->htmlspecialchars_decode($behavioural), FALSE)
				);
				echo form_textarea($data);
				?>
			</div>
			<div class='form-group'><?php
				echo form_label('Ethnic Origin', 'ethnic_origin');
				$ethnic_origin = NULL;
				if (isset($contact_info->ethnic_origin)) {
					$ethnic_origin = $contact_info->ethnic_origin;
				}
				$options = $this->settings_library->ethnic_origins;
				echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', $this->crm_library->htmlspecialchars_decode($ethnic_origin), FALSE), 'id="ethnic_origin" class="form-control select2"');
				?></div>
			<h3 class="h4 with-line">Emergency Contacts</h3>
			<h4 class="h5">Emergency Contact 1</h4>
			<div class='form-group'><?php
				echo form_label('Name', 'emergency_contact_1_name');
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
			<div class='form-group'><?php
				echo form_label('Contact Number', 'emergency_contact_1_phone');
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
			<h4 class="h5">Emergency Contact 2</h4>
			<div class='form-group'><?php
				echo form_label('Name', 'emergency_contact_2_name');
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
			<div class='form-group'><?php
				echo form_label('Contact Number', 'emergency_contact_2_phone');
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
		</div>
	</div>
	</fieldset>
	<button class='btn <?php if ($in_crm) { echo 'btn-primary'; } ?>'><?php
	if (empty($contact_info)) {
		echo 'Add';
	} else {
		echo 'Update';
	}
	?></button><?php
	echo form_close();
}
