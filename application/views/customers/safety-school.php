<?php
display_messages();

if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Address <em>*</em>', 'addressID');
					$addressID = NULL;
					if (isset($doc_info->addressID)) {
						$addressID = $doc_info->addressID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($addresses->num_rows() > 0) {
						foreach ($addresses->result() as $row) {
							$addresses = array();
							if (!empty($row->address1)) {
								$addresses[] = $row->address1;
							}
							if (!empty($row->address2)) {
								$addresses[] = $row->address2;
							}
							if (!empty($row->address3)) {
								$addresses[] = $row->address3;
							}
							if (!empty($row->town)) {
								$addresses[] = $row->town;
							}
							if (!empty($row->county)) {
								$addresses[] = $row->county;
							}
							if (!empty($row->postcode)) {
								$addresses[] = $row->postcode;
							}
							if (count($addresses) > 0) {
								$options[$row->addressID] = implode(", ", $addresses);
							}
						}
					}
					echo form_dropdown('addressID', $options, set_value('addressID', $this->crm_library->htmlspecialchars_decode($addressID), FALSE), 'id="addressID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Session Type', 'typeID');
					$typeID = NULL;
					if (isset($doc_info->typeID)) {
						$typeID = $doc_info->typeID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($lesson_types->num_rows() > 0) {
						foreach ($lesson_types->result() as $row) {
							$options[$row->typeID] = $row->name;
						}
					}
					echo form_dropdown('typeID', $options, set_value('typeID', $this->crm_library->htmlspecialchars_decode($typeID), FALSE), 'id="typeID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Location', 'field_location');
					$location = NULL;
					if (isset($doc_info->details['location'])) {
						$location = $doc_info->details['location'];
					}
					$data = array(
						'name' => 'location',
						'id' => 'field_location',
						'class' => 'form-control',
						'value' => set_value('location', $this->crm_library->htmlspecialchars_decode($location), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Assessor <em>*</em>', 'byID');
					$byID = NULL;
					if (isset($doc_info->byID)) {
						$byID = $doc_info->byID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' . $row->surname;
						}
					}
					echo form_dropdown('byID', $options, set_value('byID', $this->crm_library->htmlspecialchars_decode($byID), FALSE), 'id="byID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('brand') . ' <em>*</em>', 'brandID');
					$brandID = NULL;
					if (isset($doc_info->brandID)) {
						$brandID = $doc_info->brandID;
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
					?></div>
				<div class='form-group'><?php
					echo form_label('Date <em>*</em>', 'date');
					$date = NULL;
					if (isset($doc_info->date)) {
						$date = mysql_to_uk_date($doc_info->date);
					}
					$data = array(
						'name' => 'date',
						'id' => 'date',
						'class' => 'form-control datepicker',
						'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Expiry <em>*</em>', 'expiry');
					$expiry = NULL;
					if (isset($doc_info->expiry)) {
						$expiry = mysql_to_uk_date($doc_info->expiry);
					}
					$data = array(
						'name' => 'expiry',
						'id' => 'expiry',
						'class' => 'form-control datepicker',
						'value' => set_value('expiry', $this->crm_library->htmlspecialchars_decode($expiry), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
			</div>
		</div>
		<?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Fire Evacuation Procedure/Emergency Exits</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Alarm Tests (When)', 'field_fire_alarm_tests');
					$fire_alarm_tests = NULL;
					if (isset($doc_info->details['fire_alarm_tests'])) {
						$fire_alarm_tests = $doc_info->details['fire_alarm_tests'];
					}
					// convert pre-wysiwyg fields to html
					if ($fire_alarm_tests == strip_tags($fire_alarm_tests)) {
						$fire_alarm_tests = '<p>' . nl2br($fire_alarm_tests) . '</p>';
					}
					$data = array(
						'name' => 'fire_alarm_tests',
						'id' => 'field_fire_alarm_tests',
						'class' => 'form-control wysiwyg',
						'value' => set_value('fire_alarm_tests', $this->crm_library->htmlspecialchars_decode($fire_alarm_tests), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Assembly Points', 'field_fire_assembly_points');
					$fire_assembly_points = NULL;
					if (isset($doc_info->details['fire_assembly_points'])) {
						$fire_assembly_points = $doc_info->details['fire_assembly_points'];
					}
					// convert pre-wysiwyg fields to html
					if ($fire_assembly_points == strip_tags($fire_assembly_points)) {
						$fire_assembly_points = '<p>' . nl2br($fire_assembly_points) . '</p>';
					}
					// convert pre-wysiwyg fields to html
					if ($fire_assembly_points == strip_tags($fire_assembly_points)) {
						$fire_assembly_points = '<p>' . nl2br($fire_assembly_points) . '</p>';
					}
					$data = array(
						'name' => 'fire_assembly_points',
						'id' => 'field_fire_assembly_points',
						'class' => 'form-control wysiwyg',
						'value' => set_value('fire_assembly_points', $this->crm_library->htmlspecialchars_decode($fire_assembly_points), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Procedure', 'field_fire_procedure');
					$fire_procedure = NULL;
					if (isset($doc_info->details['fire_procedure'])) {
						$fire_procedure = $doc_info->details['fire_procedure'];
					}
					// convert pre-wysiwyg fields to html
					if ($fire_procedure == strip_tags($fire_procedure)) {
						$fire_procedure = '<p>' . nl2br($fire_procedure) . '</p>';
					}
					$data = array(
						'name' => 'fire_procedure',
						'id' => 'field_fire_procedure',
						'class' => 'form-control wysiwyg',
						'value' => set_value('fire_procedure', $this->crm_library->htmlspecialchars_decode($fire_procedure), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">School Accident Procedure</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Reporting Procedure', 'field_accident_reporting_procedure');
					$accident_reporting_procedure = NULL;
					if (isset($doc_info->details['accident_reporting_procedure'])) {
						$accident_reporting_procedure = $doc_info->details['accident_reporting_procedure'];
					}
					// convert pre-wysiwyg fields to html
					if ($accident_reporting_procedure == strip_tags($accident_reporting_procedure)) {
						$accident_reporting_procedure = '<p>' . nl2br($accident_reporting_procedure) . '</p>';
					}
					$data = array(
						'name' => 'accident_reporting_procedure',
						'id' => 'field_accident_reporting_procedure',
						'class' => 'form-control wysiwyg',
						'value' => set_value('accident_reporting_procedure', $this->crm_library->htmlspecialchars_decode($accident_reporting_procedure), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Specify location of accident reporting books', 'field_accident_book');
					$accident_book = NULL;
					if (isset($doc_info->details['accident_book'])) {
						$accident_book = $doc_info->details['accident_book'];
					}
					// convert pre-wysiwyg fields to html
					if ($accident_book == strip_tags($accident_book)) {
						$accident_book = '<p>' . nl2br($accident_book) . '</p>';
					}
					$data = array(
						'name' => 'accident_book',
						'id' => 'field_accident_book',
						'class' => 'form-control wysiwyg',
						'value' => set_value('accident_book', $this->crm_library->htmlspecialchars_decode($accident_book), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Specify the relevant school contact', 'field_accident_contact');
					$accident_contact = NULL;
					if (isset($doc_info->details['accident_contact'])) {
						$accident_contact = $doc_info->details['accident_contact'];
					}
					// convert pre-wysiwyg fields to html
					if ($accident_contact == strip_tags($accident_contact)) {
						$accident_contact = '<p>' . nl2br($accident_contact) . '</p>';
					}
					$data = array(
						'name' => 'accident_contact',
						'id' => 'field_accident_contact',
						'class' => 'form-control wysiwyg',
						'value' => set_value('accident_contact', $this->crm_library->htmlspecialchars_decode($accident_contact), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Behaviour Policy</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Rewards', 'field_behaviour_rewards');
					$behaviour_rewards = NULL;
					if (isset($doc_info->details['behaviour_rewards'])) {
						$behaviour_rewards = $doc_info->details['behaviour_rewards'];
					}
					// convert pre-wysiwyg fields to html
					if ($behaviour_rewards == strip_tags($behaviour_rewards)) {
						$behaviour_rewards = '<p>' . nl2br($behaviour_rewards) . '</p>';
					}
					$data = array(
						'name' => 'behaviour_rewards',
						'id' => 'field_behaviour_rewards',
						'class' => 'form-control wysiwyg',
						'value' => set_value('behaviour_rewards', $this->crm_library->htmlspecialchars_decode($behaviour_rewards), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Procedures for bad behaviour', 'field_behaviour_procedure');
					$behaviour_procedure = NULL;
					if (isset($doc_info->details['behaviour_procedure'])) {
						$behaviour_procedure = $doc_info->details['behaviour_procedure'];
					}
					// convert pre-wysiwyg fields to html
					if ($behaviour_procedure == strip_tags($behaviour_procedure)) {
						$behaviour_procedure = '<p>' . nl2br($behaviour_procedure) . '</p>';
					}
					$data = array(
						'name' => 'behaviour_procedure',
						'id' => 'field_behaviour_procedure',
						'class' => 'form-control wysiwyg',
						'value' => set_value('behaviour_procedure', $this->crm_library->htmlspecialchars_decode($behaviour_procedure), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('SEN &amp; Medical Information', 'field_behaviour_sen_medical');
					$behaviour_sen_medical = NULL;
					if (isset($doc_info->details['behaviour_sen_medical'])) {
						$behaviour_sen_medical = $doc_info->details['behaviour_sen_medical'];
					}
					// convert pre-wysiwyg fields to html
					if ($behaviour_sen_medical == strip_tags($behaviour_sen_medical)) {
						$behaviour_sen_medical = '<p>' . nl2br($behaviour_sen_medical) . '</p>';
					}
					$data = array(
						'name' => 'behaviour_sen_medical',
						'id' => 'field_behaviour_sen_medical',
						'class' => 'form-control wysiwyg',
						'value' => set_value('behaviour_sen_medical', $this->crm_library->htmlspecialchars_decode($behaviour_sen_medical), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Further School Info</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Schools Do\'s and Don\'ts', 'field_further_dos_donts');
					$further_dos_donts = NULL;
					if (isset($doc_info->details['further_dos_donts'])) {
						$further_dos_donts = $doc_info->details['further_dos_donts'];
					}
					// convert pre-wysiwyg fields to html
					if ($further_dos_donts == strip_tags($further_dos_donts)) {
						$further_dos_donts = '<p>' . nl2br($further_dos_donts) . '</p>';
					}
					$data = array(
						'name' => 'further_dos_donts',
						'id' => 'field_further_dos_donts',
						'class' => 'form-control wysiwyg',
						'value' => set_value('further_dos_donts', $this->crm_library->htmlspecialchars_decode($further_dos_donts), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Helpful delivery info', 'field_further_helpful_info');
					$further_helpful_info = NULL;
					if (isset($doc_info->details['further_helpful_info'])) {
						$further_helpful_info = $doc_info->details['further_helpful_info'];
					}
					// convert pre-wysiwyg fields to html
					if ($further_helpful_info == strip_tags($further_helpful_info)) {
						$further_helpful_info = '<p>' . nl2br($further_helpful_info) . '</p>';
					}
					$data = array(
						'name' => 'further_helpful_info',
						'id' => 'field_further_helpful_info',
						'class' => 'form-control wysiwyg',
						'value' => set_value('further_helpful_info', $this->crm_library->htmlspecialchars_decode($further_helpful_info), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Overview on the schools behaviour', 'field_further_behaviour');
					$further_behaviour = NULL;
					if (isset($doc_info->details['further_behaviour'])) {
						$further_behaviour = $doc_info->details['further_behaviour'];
					}
					// convert pre-wysiwyg fields to html
					if ($further_behaviour == strip_tags($further_behaviour)) {
						$further_behaviour = '<p>' . nl2br($further_behaviour) . '</p>';
					}
					$data = array(
						'name' => 'further_behaviour',
						'id' => 'field_further_behaviour',
						'class' => 'form-control wysiwyg',
						'value' => set_value('further_behaviour', $this->crm_library->htmlspecialchars_decode($further_behaviour), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Car Park Open and Close Times', 'field_further_carpark');
					$further_carpark = NULL;
					if (isset($doc_info->details['further_carpark'])) {
						$further_carpark = $doc_info->details['further_carpark'];
					}
					// convert pre-wysiwyg fields to html
					if ($further_carpark == strip_tags($further_carpark)) {
						$further_carpark = '<p>' . nl2br($further_carpark) . '</p>';
					}
					$data = array(
						'name' => 'further_carpark',
						'id' => 'field_further_carpark',
						'class' => 'form-control wysiwyg',
						'value' => set_value('further_carpark', $this->crm_library->htmlspecialchars_decode($further_carpark), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Venue Equipment for Use</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class="form-group safety-equipment">
					<?php
					$equipment_list = array(
						'Gymnastics',
						'Football',
						'Rugby',
						'Basketball',
						'Netball',
						'CD Player',
						'Tennis',
						'Rounders',
						'Cricket',
						'Softballs',
						'Cones',
						'Athletics',
						'Hoops',
						'Beanbags',
						'Quoits',
						'Tennis Balls',
						'Hockey'
					);
					if (count($equipment_list) > 0) {
						$equipment = array();
						if (is_array($this->input->post('equipment'))) {
							$equipment = $this->input->post('equipment');
						} else if (isset($doc_info->details['equipment']) && is_array($doc_info->details['equipment'])) {
							$equipment = $doc_info->details['equipment'];
						}
						$equipment_details = array();
						if (is_array($this->input->post('equipment_details'))) {
							$equipment_details = $this->input->post('equipment_details');
						} else if (isset($doc_info->details['equipment_details']) && is_array($doc_info->details['equipment_details'])) {
							$equipment_details = $doc_info->details['equipment_details'];
						}
						foreach ($equipment_list as $item) {
							$data = array(
								'name' => 'equipment[]',
								'value' => $item
							);
							if (in_array($item, $equipment)) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									<?php echo $item; ?>
									<span></span>
								</label>
							</div>
							<?php
							$item_key = preg_replace("/[^a-z0-9]/", '', strtolower($item));
							$value = NULL;
							if (array_key_exists($item_key, $equipment_details)) {
								$value = $equipment_details[$item_key];
							}
							$data = array(
								'name' => 'equipment_details[' . $item_key . ']',
								'class' => 'form-control',
								'value' => $value,
								'maxlength' => 100
							);
							echo form_input($data);
						}
					}
					?>
				</div>
				<div class='form-group'><?php
					echo form_label('Any Additional Equipment', 'field_equipment_additional');
					$equipment_additional = NULL;
					if (isset($doc_info->details['equipment_additional'])) {
						$equipment_additional = $doc_info->details['equipment_additional'];
					}
					// convert pre-wysiwyg fields to html
					if ($equipment_additional == strip_tags($equipment_additional)) {
						$equipment_additional = '<p>' . nl2br($equipment_additional) . '</p>';
					}
					$data = array(
						'name' => 'equipment_additional',
						'id' => 'field_equipment_additional',
						'class' => 'form-control wysiwyg',
						'value' => set_value('equipment_additional', $this->crm_library->htmlspecialchars_decode($equipment_additional), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Further Comments</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Comments', 'field_further_comments');
					$further_comments = NULL;
					if (isset($doc_info->details['further_comments'])) {
						$further_comments = $doc_info->details['further_comments'];
					}
					$data = array(
						'name' => 'further_comments',
						'id' => 'field_further_comments',
						'class' => 'form-control wysiwyg',
						'value' => set_value('further_comments', $this->crm_library->htmlspecialchars_decode($further_comments), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">School Images</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				for ($i = 0; $i < 5; $i++) {
					if (isset($doc_info->details['venue_images']) && is_array($doc_info->details['venue_images']) && array_key_exists($i, $doc_info->details['venue_images'])) {
						?><div class='form-group'><?php
							echo form_label('Current Image ' . ($i+1));
							if (AWS) {
								$src = $this->aws_library->s3_presigned_url('orgs/' . $org_id . '/safety/thumb.' . $doc_info->details['venue_images'][$i]);
								$src1 = $this->aws_library->s3_presigned_url('orgs/' . $org_id . '/safety/' . $doc_info->details['venue_images'][$i]);
							} else {
								$src = base_url('public/uploads/orgs/' . $org_id . '/safety/thumb.' . $doc_info->details['venue_images'][$i]);
								$src1 = base_url('public/uploads/orgs/' . $org_id . '/safety/' . $doc_info->details['venue_images'][$i]);
							}
							$data = array(
								'src' => $src
							);
							echo "<br /><a href='".$src1."' data-fancybox='gallery' class='profileimage'> " . img($data). "</a>";
						?></div><?php

						$data = array(
							'name' => 'delete_venue_images[' . $i . ']',
							'value' => $doc_info->details['venue_images'][$i]
						);
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Delete Image <?php echo ($i+1); ?>
								<span></span>
							</label>
						</div><?php
						$data = array(
							'venue_images[' . $i . ']' => $doc_info->details['venue_images'][$i]
						);
						echo form_hidden($data);
					}
					?><div class='form-group'><?php
						$label = NULL;
						if (isset($doc_info->details['venue_images']) && is_array($doc_info->details['venue_images']) && array_key_exists($i, $doc_info->details['venue_images'])) {
							$label = 'Replace ';
						}
						echo form_label($label . 'Image ' . ($i+1));
						$data = array(
							'name' => 'venue_images_' . $i,
							'id' => 'venue_images_' . $i,
							'class' => 'custom-file-input'
						);
						?>
						<div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="venue_images_<?php echo $i; ?>">Choose file</label>
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
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">School Site Map</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				for ($i = 0; $i < 1; $i++) {
					if (isset($doc_info->details['map_images']) && is_array($doc_info->details['map_images']) && array_key_exists($i, $doc_info->details['map_images'])) {
						?><div class='form-group'><?php
							echo form_label('Current Image ' . ($i+1));
							if (AWS) {
								$src = $this->aws_library->s3_presigned_url('orgs/' . $org_id . '/safety/thumb.' . $doc_info->details['map_images'][$i]);
							} else {
								$src = base_url('public/uploads/orgs/' . $org_id . '/safety/thumb.' . $doc_info->details['map_images'][$i]);
							}
							$data = array(
								'src' => $src
							);
							echo "<br />" . img($data);
						?></div><?php

						$data = array(
							'name' => 'delete_map_images[' . $i . ']',
							'value' => $doc_info->details['map_images'][$i]
						);
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Delete Image <?php echo ($i+1); ?>
								<span></span>
							</label>
						</div><?php
						$data = array(
							'map_images[' . $i . ']' => $doc_info->details['map_images'][$i]
						);
						echo form_hidden($data);
					}
					?><div class='form-group'><?php
						$label = NULL;
						if (isset($doc_info->details['map_images']) && is_array($doc_info->details['map_images']) && array_key_exists($i, $doc_info->details['map_images'])) {
							$label = 'Replace ';
						}
						echo form_label($label . 'Image ' . ($i+1));
						$data = array(
							'name' => 'map_images_' . $i,
							'id' => 'map_images_' . $i,
							'class' => 'custom-file-input'
						);
						?>
						<div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="map_images<?php echo $i; ?>">Choose file</label>
						</div>
					</div><?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
