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
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Venue</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Emergency Contact', 'field_venue_contact1');
					$venue_contact1 = NULL;
					if (isset($doc_info->details['venue_contact1'])) {
						$venue_contact1 = $doc_info->details['venue_contact1'];
					}
					// convert pre-wysiwyg fields to html
					if ($venue_contact1 == strip_tags($venue_contact1)) {
						$venue_contact1 = '<p>' . nl2br($venue_contact1) . '</p>';
					}
					$data = array(
						'name' => 'venue_contact1',
						'id' => 'field_venue_contact1',
						'class' => 'form-control wysiwyg',
						'value' => set_value('venue_contact1', $this->crm_library->htmlspecialchars_decode($venue_contact1), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Secondary Emergency Contact', 'field_venue_contact2');
					$venue_contact2 = NULL;
					if (isset($doc_info->details['venue_contact2'])) {
						$venue_contact2 = $doc_info->details['venue_contact2'];
					}
					// convert pre-wysiwyg fields to html
					if ($venue_contact2 == strip_tags($venue_contact2)) {
						$venue_contact2 = '<p>' . nl2br($venue_contact2) . '</p>';
					}
					$data = array(
						'name' => 'venue_contact2',
						'id' => 'field_venue_contact2',
						'class' => 'form-control wysiwyg',
						'value' => set_value('venue_contact2', $this->crm_library->htmlspecialchars_decode($venue_contact2), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Open and Lock up Procedure', 'field_open_lockup');
					$open_lockup = NULL;
					if (isset($doc_info->details['open_lockup'])) {
						$open_lockup = $doc_info->details['open_lockup'];
					}
					// convert pre-wysiwyg fields to html
					if ($open_lockup == strip_tags($open_lockup)) {
						$open_lockup = '<p>' . nl2br($open_lockup) . '</p>';
					}
					$data = array(
						'name' => 'open_lockup',
						'id' => 'field_open_lockup',
						'class' => 'form-control wysiwyg',
						'value' => set_value('open_lockup', $this->crm_library->htmlspecialchars_decode($open_lockup), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Parent Registration Area', 'field_registration_area');
					$registration_area = NULL;
					if (isset($doc_info->details['registration_area'])) {
						$registration_area = $doc_info->details['registration_area'];
					}
					// convert pre-wysiwyg fields to html
					if ($registration_area == strip_tags($registration_area)) {
						$registration_area = '<p>' . nl2br($registration_area) . '</p>';
					}
					$data = array(
						'name' => 'registration_area',
						'id' => 'field_registration_area',
						'class' => 'form-control wysiwyg',
						'value' => set_value('registration_area', $this->crm_library->htmlspecialchars_decode($registration_area), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Fire Evacuation Procedure/Emergency Exits', 'field_fire_procedure');
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
				<h3 class="card-label">Areas for Use</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Toilets (Indoor)', 'field_indoor_toilets');
					$indoor_toilets = NULL;
					if (isset($doc_info->details['indoor_toilets'])) {
						$indoor_toilets = $doc_info->details['indoor_toilets'];
					}
					// convert pre-wysiwyg fields to html
					if ($indoor_toilets == strip_tags($indoor_toilets)) {
						$indoor_toilets = '<p>' . nl2br($indoor_toilets) . '</p>';
					}
					$data = array(
						'name' => 'indoor_toilets',
						'id' => 'field_indoor_toilets',
						'class' => 'form-control wysiwyg',
						'value' => set_value('indoor_toilets', $this->crm_library->htmlspecialchars_decode($indoor_toilets), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Toilets (Outdoor)', 'field_outdoor_toilets');
					$outdoor_toilets = NULL;
					if (isset($doc_info->details['outdoor_toilets'])) {
						$outdoor_toilets = $doc_info->details['outdoor_toilets'];
					}
					// convert pre-wysiwyg fields to html
					if ($outdoor_toilets == strip_tags($outdoor_toilets)) {
						$outdoor_toilets = '<p>' . nl2br($outdoor_toilets) . '</p>';
					}
					$data = array(
						'name' => 'outdoor_toilets',
						'id' => 'field_outdoor_toilets',
						'class' => 'form-control wysiwyg',
						'value' => set_value('outdoor_toilets', $this->crm_library->htmlspecialchars_decode($outdoor_toilets), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Lunch (Indoor)', 'field_indoor_lunch');
					$indoor_lunch = NULL;
					if (isset($doc_info->details['indoor_lunch'])) {
						$indoor_lunch = $doc_info->details['indoor_lunch'];
					}
					// convert pre-wysiwyg fields to html
					if ($indoor_lunch == strip_tags($indoor_lunch)) {
						$indoor_lunch = '<p>' . nl2br($indoor_lunch) . '</p>';
					}
					$data = array(
						'name' => 'indoor_lunch',
						'id' => 'field_indoor_lunch',
						'class' => 'form-control wysiwyg',
						'value' => set_value('indoor_lunch', $this->crm_library->htmlspecialchars_decode($indoor_lunch), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Lunch (Outdoor)', 'field_outdoor_lunch');
					$outdoor_lunch = NULL;
					if (isset($doc_info->details['outdoor_lunch'])) {
						$outdoor_lunch = $doc_info->details['outdoor_lunch'];
					}
					// convert pre-wysiwyg fields to html
					if ($outdoor_lunch == strip_tags($outdoor_lunch)) {
						$outdoor_lunch = '<p>' . nl2br($outdoor_lunch) . '</p>';
					}
					$data = array(
						'name' => 'outdoor_lunch',
						'id' => 'field_outdoor_lunch',
						'class' => 'form-control wysiwyg',
						'value' => set_value('outdoor_lunch', $this->crm_library->htmlspecialchars_decode($outdoor_lunch), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Activity (Indoor)', 'field_indoor_activity');
					$indoor_activity = NULL;
					if (isset($doc_info->details['indoor_activity'])) {
						$indoor_activity = $doc_info->details['indoor_activity'];
					}
					// convert pre-wysiwyg fields to html
					if ($indoor_activity == strip_tags($indoor_activity)) {
						$indoor_activity = '<p>' . nl2br($indoor_activity) . '</p>';
					}
					$data = array(
						'name' => 'indoor_activity',
						'id' => 'field_indoor_activity',
						'class' => 'form-control wysiwyg',
						'value' => set_value('indoor_activity', $this->crm_library->htmlspecialchars_decode($indoor_activity), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Activity (Outdoor)', 'field_outdoor_activity');
					$outdoor_activity = NULL;
					if (isset($doc_info->details['outdoor_activity'])) {
						$outdoor_activity = $doc_info->details['outdoor_activity'];
					}
					// convert pre-wysiwyg fields to html
					if ($outdoor_activity == strip_tags($outdoor_activity)) {
						$outdoor_activity = '<p>' . nl2br($outdoor_activity) . '</p>';
					}
					$data = array(
						'name' => 'outdoor_activity',
						'id' => 'field_outdoor_activity',
						'class' => 'form-control wysiwyg',
						'value' => set_value('outdoor_activity', $this->crm_library->htmlspecialchars_decode($outdoor_activity), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Areas Not for Use (Indoor)', 'field_indoor_not');
					$indoor_not = NULL;
					if (isset($doc_info->details['indoor_not'])) {
						$indoor_not = $doc_info->details['indoor_not'];
					}
					// convert pre-wysiwyg fields to html
					if ($indoor_not == strip_tags($indoor_not)) {
						$indoor_not = '<p>' . nl2br($indoor_not) . '</p>';
					}
					$data = array(
						'name' => 'indoor_not',
						'id' => 'field_indoor_not',
						'class' => 'form-control wysiwyg',
						'value' => set_value('indoor_not', $this->crm_library->htmlspecialchars_decode($indoor_not), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Areas Not for Use (Outdoor)', 'field_outdoor_not');
					$outdoor_not = NULL;
					if (isset($doc_info->details['outdoor_not'])) {
						$outdoor_not = $doc_info->details['outdoor_not'];
					}
					// convert pre-wysiwyg fields to html
					if ($outdoor_not == strip_tags($outdoor_not)) {
						$outdoor_not = '<p>' . nl2br($outdoor_not) . '</p>';
					}
					$data = array(
						'name' => 'outdoor_not',
						'id' => 'field_outdoor_not',
						'class' => 'form-control wysiwyg',
						'value' => set_value('outdoor_not', $this->crm_library->htmlspecialchars_decode($outdoor_not), FALSE)
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
				<h3 class="card-label">Accident Procedure</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Procedure', 'field_accident_procedure');
					$accident_procedure = NULL;
					if (isset($doc_info->details['accident_procedure'])) {
						$accident_procedure = $doc_info->details['accident_procedure'];
					}
					// convert pre-wysiwyg fields to html
					if ($accident_procedure == strip_tags($accident_procedure)) {
						$accident_procedure = '<p>' . nl2br($accident_procedure) . '</p>';
					}
					$data = array(
						'name' => 'accident_procedure',
						'id' => 'field_accident_procedure',
						'class' => 'form-control wysiwyg',
						'value' => set_value('accident_procedure', $this->crm_library->htmlspecialchars_decode($accident_procedure), FALSE)
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
						?><div class="custom-file">
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
							<label class="custom-file-label" for="map_images_<?php echo $i; ?>">Choose file</label>
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
