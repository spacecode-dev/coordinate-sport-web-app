<?php
display_messages();

if ($bookingID != NULL) {
	if ($lessonID != NULL) {
		$data = array(
			'bookingID' => $bookingID,
			'blockID' => $lesson_info->blockID,
			'lessonID' => $lessonID,
			'tab' => 'details',
			'type' => $booking_type
		);
		$this->load->view('sessions/tabs.php', $data);
	} else {
		$data = array(
			'bookingID' => $bookingID,
			'tab' => $tab,
			'type' => $booking_type,
			'is_project' => $booking_info->project,
			'type' => $booking_info->type
		);
		$this->load->view('bookings/tabs.php', $data);
	}
}
echo form_open_multipart($submit_to, array('class' => 'edit_lesson'));
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-calendar-check text-contrast'></i></span>
				<h3 class="card-label">Day &amp; Time</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Day <em>*</em>', 'day');
					$day = NULL;
					if (isset($lesson_info->day)) {
						$day = $lesson_info->day;
					}
					$options = array(
						'' => 'Select',
						'monday' => 'Monday',
						'tuesday' => 'Tuesday',
						'wednesday' => 'Wednesday',
						'thursday' => 'Thursday',
						'friday' => 'Friday',
						'saturday' => 'Saturday',
						'sunday' => 'Sunday'
					);
					echo form_dropdown('day', $options, set_value('day', $this->crm_library->htmlspecialchars_decode($day), FALSE), 'id="day" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Start Time <em>*</em>', 'startTimeH');
					$startTimeH = 6;
					if (isset($lesson_info->startTime)) {
						$startTimeH = substr($lesson_info->startTime, 0, 2);
					}
					$options = array();
					$h = 6;
					while ($h <= 23) {
						$h = sprintf("%02d",$h);
						$options[$h] = $h;
						$h++;
					}
					echo form_dropdown('startTimeH', $options, set_value('startTimeH', $this->crm_library->htmlspecialchars_decode($startTimeH), FALSE), 'id="startTimeH" class="form-control select2"');
					$startTimeM = NULL;
					if (isset($lesson_info->startTime)) {
						$startTimeM = substr($lesson_info->startTime, 3, 5);
						$startTimeM = substr($startTimeM, 0, 2); // trim microseconds off
					}
					$options = array();
					$m = 0;
					while ($m <= 59) {
						$m = sprintf("%02d",$m);
						if ($m % 5 == 0) {
							$options[$m] = $m;
						}
						$m++;
					}
					echo form_dropdown('startTimeM', $options, set_value('startTimeM', $this->crm_library->htmlspecialchars_decode($startTimeM), FALSE), 'id="startTimeM" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('End Time <em>*</em>', 'endTimeH');
					$endTimeH = 7;
					if (isset($lesson_info->endTime)) {
						$endTimeH = substr($lesson_info->endTime, 0, 2);
					}
					$options = array();
					$h = 6;
					while ($h <= 23) {
						$h = sprintf("%02d",$h);
						$options[$h] = $h;
						if($h == 24){
							$options[$h] = "00";
						}
						$h++;
					}
					echo form_dropdown('endTimeH', $options, set_value('endTimeH', $this->crm_library->htmlspecialchars_decode($endTimeH), FALSE), 'id="endTimeH" class="form-control select2"');
					$endTimeM = NULL;
					if (isset($lesson_info->endTime)) {
						$endTimeM = substr($lesson_info->endTime, 3, 5);
						$endTimeM = substr($endTimeM, 0, 2); // trim microseconds off
					}
					$options = array();
					$m = 0;
					while ($m <= 59) {
						$m = sprintf("%02d",$m);
						if ($m % 5 == 0) {
							$options[$m] = $m;
						}
						if($m == 59){
							$options[$m] = $m;
						}
						$m++;
					}
					echo form_dropdown('endTimeM', $options, set_value('endTimeM', $this->crm_library->htmlspecialchars_decode($endTimeM), FALSE), 'id="endTimeM" class="form-control select2"');
				?></div>

				<?php
				if ($lessonID != NULL) {
					?><div class="alert alert-info times_changed" style="display:none">
						<?php
						$data = array(
							'name' => 'adjust_staff_times',
							'id' => 'adjust_staff_times',
							'value' => 1
						);
						$adjust_staff_times = NULL;
						if (set_value('adjust_staff_times', $this->crm_library->htmlspecialchars_decode($adjust_staff_times), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?>
						<label>
							<?php echo form_checkbox($data); ?>
							Adjust staff times to new times
						</label>
						<?php
						if ($this->settings_library->get('send_staff_changed_sessions') == 1) {
							echo ' and <label>';
							$data = array(
								'name' => 'notify_staff',
								'id' => 'notify_staff',
								'value' => 1
							);
							if (set_value('notify_staff') == 1) {
								$data['checked'] = TRUE;
							}
							echo form_checkbox($data);
							echo ' notify them</label>';
						}
						?><br>
						<em>Only effects staff where either the start or end staffing time matches the previous times</em>
					</div>
					<script>
						var orig_startH = '<?php echo substr($lesson_info->startTime, 0, 2); ?>';
						var orig_startM = '<?php echo substr($lesson_info->startTime, 3, 2); ?>';
						var orig_endH = '<?php echo substr($lesson_info->endTime, 0, 2); ?>';
						var orig_endM = '<?php echo substr($lesson_info->endTime, 3, 2); ?>';
					</script><?php
				}
				?>

				<p>If you want to limit this session to a specifc date or dates within the block, enter them below.</p>
				<div class='form-group'><?php
					echo form_label('Start Date', 'startDate');
					$startDate = NULL;
					if (isset($lesson_info->startDate)) {
						$startDate = mysql_to_uk_date($lesson_info->startDate);
					}
					$data = array(
						'name' => 'startDate',
						'id' => 'startDate',
						'class' => 'form-control datepicker',
						'value' => set_value('startDate', $this->crm_library->htmlspecialchars_decode($startDate), FALSE),
						'maxlength' => 10,
						'data-mindate' => $block_info->startDate,
						'data-maxdate' => $block_info->endDate
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('End Date', 'endDate');
					$endDate = NULL;
					if (isset($lesson_info->endDate)) {
						$endDate = mysql_to_uk_date($lesson_info->endDate);
					}
					$data = array(
						'name' => 'endDate',
						'id' => 'endDate',
						'class' => 'form-control datepicker',
						'value' => set_value('endDate', $this->crm_library->htmlspecialchars_decode($endDate), FALSE),
						'maxlength' => 10,
						'data-mindate' => $block_info->startDate,
						'data-maxdate' => $block_info->endDate
					);
					echo form_input($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
				<h3 class="card-label">Location</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if ($booking_type == 'booking') {
					if ($add_address != 1) {
						?><div class='form-group'><?php
							echo form_label('Session Delivery Address <em>*</em>', 'addressID');
							$addressID = NULL;
							if (isset($lesson_info->addressID)) {
								$addressID = $lesson_info->addressID;
							} else if (isset($block_info->addressID)) {
								// get default from block
								$addressID = $block_info->addressID;
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
							?><p class="help-block">
								<small class="text-muted form-text"><a href="#" class="add_address">Add Address</a></small>
							</p>
						</div><?php
					}
					echo form_hidden(array('add_address' => $add_address));
					?>
					<div class="add_address_fields"<?php if ($add_address != 1) { echo ' style="display:none;"'; } ?>>
						<div class='form-group'><?php
							echo form_label('Address 1 <em>*</em>', 'address_address1');
							$data = array(
								'name' => 'address_address1',
								'id' => 'address_address1',
								'class' => 'form-control',
								'value' => set_value('address_address1', NULL, FALSE),
								'maxlength' => 255
							);
							echo form_input($data);
						?></div>
						<div class='form-group'><?php
							echo form_label('Address 2', 'address_address2');
							$data = array(
								'name' => 'address_address2',
								'id' => 'address_address2',
								'class' => 'form-control',
								'value' => set_value('address_address2', NULL, FALSE),
								'maxlength' => 255
							);
							echo form_input($data);
						?></div>
						<div class='form-group'><?php
							echo form_label('Address 3', 'address_address3');
							$data = array(
								'name' => 'address_address3',
								'id' => 'address_address3',
								'class' => 'form-control',
								'value' => set_value('address_address3', NULL, FALSE),
								'maxlength' => 255
							);
							echo form_input($data);
						?></div>
						<div class='form-group'><?php
							echo form_label('Town <em>*</em>', 'address_town');
							$data = array(
								'name' => 'address_town',
								'id' => 'address_town',
								'class' => 'form-control',
								'value' => set_value('address_town', NULL, FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div>
						<div class='form-group'><?php
							echo form_label(localise('county') . ' <em>*</em>', 'address_address1');
							$data = array(
								'name' => 'address_county',
								'id' => 'address_county',
								'class' => 'form-control',
								'value' => set_value('address_county', NULL, FALSE),
								'maxlength' => 50
							);
							echo form_input($data);
						?></div>
						<div class='form-group'><?php
							echo form_label('Postcode <em>*</em>', 'address_postcode');
							$data = array(
								'name' => 'address_postcode',
								'id' => 'address_postcode',
								'class' => 'form-control',
								'value' => set_value('address_postcode', NULL, FALSE),
								'maxlength' => 10
							);
							echo form_input($data);
						?></div>
					</div><?php
				}
				?>
				<div class='form-group'><?php
					$label = 'Location at Delivery Address';
					echo form_label($label, 'field_location');
					$location = NULL;
					if (isset($lesson_info->location)) {
						$location = $lesson_info->location;
					}
					$data = array(
						'name' => 'location',
						'id' => 'field_location',
						'class' => 'form-control',
						'value' => set_value('location', $this->crm_library->htmlspecialchars_decode($location), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?>
				<small class="text-muted form-text">
				For example, Sports Hall.
				</small>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					$label="Session Type";
					if ($this->settings_library->get('require_session_type')) {
						$label .= ' <em>*</em>';
					}
					echo form_label($label, 'typeID');
					$typeID = NULL;
					if (isset($lesson_info->typeID)) {
						$typeID = $lesson_info->typeID;
					}
					if (!empty($lesson_info->type_other)) {
						$typeID = 'other';
					}
					$options = array(
						'' => 'Select'
					);
					if ($lesson_types->num_rows() > 0) {
						foreach ($lesson_types->result() as $row) {
							$options[$row->typeID] = $row->name;
						}
					}
					$options['other'] = 'Other (Please specify)';
					echo form_dropdown('typeID', $options, set_value('typeID', $this->crm_library->htmlspecialchars_decode($typeID), FALSE), 'id="typeID" class="form-control select2" data-toggleother="field_type_other"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Type - Other', 'field_type_other');
					$type_other = NULL;
					if (isset($lesson_info->type_other)) {
						$type_other = $lesson_info->type_other;
					}
					$data = array(
						'name' => 'type_other',
						'id' => 'field_type_other',
						'class' => 'form-control',
						'value' => set_value('type_other', $this->crm_library->htmlspecialchars_decode($type_other), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					$req = NULL;
					if ($booking_type == 'booking') {
						 $req = ' <em>*</em>';
					}
					echo form_label('Activity' . $req, 'activity');
					$activityID = NULL;
					if (isset($lesson_info->activityID)) {
						$activityID = $lesson_info->activityID;
					}
					if (!empty($lesson_info->activity_other)) {
						$activityID = 'other';
					}
					$options = array(
						'' => 'Select'
					);
					if ($activities->num_rows() > 0) {
						foreach ($activities->result() as $row) {
							$options[$row->activityID] = $row->name;
						}
					}
					$options['other'] = 'Other (Please specify)';
					echo form_dropdown('activityID', $options, set_value('activityID', $this->crm_library->htmlspecialchars_decode($activityID), FALSE), 'id="activityID" class="form-control select2" data-toggleother="field_activity_other"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Activity - Other <em>*</em>', 'field_activity_other');
					$activity_other = NULL;
					if (isset($lesson_info->activity_other)) {
						$activity_other = $lesson_info->activity_other;
					}
					$data = array(
						'name' => 'activity_other',
						'id' => 'field_activity_other',
						'class' => 'form-control',
						'value' => set_value('activity_other', $this->crm_library->htmlspecialchars_decode($activity_other), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Activity Description', 'activity_desc');
					$activity_desc = NULL;
					if (isset($lesson_info->activity_desc)) {
						$activity_desc = $lesson_info->activity_desc;
					}
					$data = array(
						'name' => 'activity_desc',
						'id' => 'activity_desc',
						'class' => 'form-control',
						'value' => set_value('activity_desc', $this->crm_library->htmlspecialchars_decode($activity_desc), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Group/Class', 'group');
					$group = NULL;
					if (isset($lesson_info->group)) {
						$group = $lesson_info->group;
					}
					$options = array(
						'' => 'Select'
					);

					// fetch session groups
					$options = array_merge($options, $this->crm_library->lesson_groups());
					$options['other'] = 'Other (please specify)';

					echo form_dropdown('group', $options, set_value('group', $this->crm_library->htmlspecialchars_decode($group), FALSE), 'id="group" class="form-control select2" data-toggleother="field_group_other"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Group/Class - Other', 'field_group_other');
					$group_other = NULL;
					if (isset($lesson_info->group_other)) {
						$group_other = $lesson_info->group_other;
					}
					$data = array(
						'name' => 'group_other',
						'id' => 'field_group_other',
						'class' => 'form-control',
						'value' => set_value('group_other', $this->crm_library->htmlspecialchars_decode($group_other), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Class Size', 'field_class_size');
					$class_size = NULL;
					if (isset($lesson_info->class_size)) {
						$class_size = $lesson_info->class_size;
					}
					$data = array(
						'name' => 'class_size',
						'id' => 'field_class_size',
						'class' => 'form-control',
						'value' => set_value('class_size', $this->crm_library->htmlspecialchars_decode($class_size), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<?php
				if ($this->auth->has_features('resources')) {
					?><div class="form-group">
						<?php
						echo form_label('Scheme of Work', 'resources_attachments');
						if (count($resources_attachments) > 0) {
							$options = array();
							if ($this->input->post()) {
								$resources_attachments_array = $this->input->post('resources_attachments');
							}
							if (!is_array($resources_attachments_array)) {
								$resources_attachments_array = array();
							}
							echo form_multiselect('resources_attachments[]', $resources_attachments, $resources_attachments_array, 'id="resources_attachments" class="form-control select2"');
						} else {
							echo "<p>None</p>";
						}
						?>
					</div><?php
				}
				if ($booking_type == 'booking' && $this->auth->user->department != 'headcoach') { ?>
					<div class='form-group'><?php
						$label = "Customer Charge";
						echo form_label($label.' <em>*</em>', 'charge');
						$charge = NULL;
						if (isset($lesson_info->charge)) {
							$charge = $lesson_info->charge;
						} else if ($lessonID == NULL) {
							$charge = 'default';
						}
						$options = array(
							'' => 'Select',
							'default' => 'Booking Default',
							'prepaid' => ($booking_info->project == 0)?'Already Invoiced':'Prepaid',
							'free' => 'Free',
							'other' => 'Other (please specify)'
						);
						echo form_dropdown('charge', $options, set_value('charge', $this->crm_library->htmlspecialchars_decode($charge), FALSE), 'id="charge" class="form-control select2" data-toggleother="field_charge_other"');
					?></div>
					<div class='form-group'><?php
						echo form_label('Charge - Other', 'field_charge_other');
						$charge_other = NULL;
						if (isset($lesson_info->charge_other)) {
							$charge_other = $lesson_info->charge_other;
						}
						$data = array(
							'name' => 'charge_other',
							'id' => 'field_charge_other',
							'class' => 'form-control',
							'value' => set_value('charge_other', $this->crm_library->htmlspecialchars_decode($charge_other), FALSE),
							'maxlength' => 100
						);
						echo form_input($data);
					?></div><?php
				}
				if ($booking_type == 'event' || $booking_info->project == 1)  {
					?><div class='form-group'><?php
						echo form_label('Price (' . currency_symbol() . ')', 'price');
						$price = NULL;
						if (isset($lesson_info->price) && $lesson_info->price > 0) {
							$price = $lesson_info->price;
						}
						$data = array(
							'name' => 'price',
							'id' => 'price',
							'class' => 'form-control',
							'value' => set_value('price', $this->crm_library->htmlspecialchars_decode($price), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
						?>
						<small class="text-muted form-text">This relates to the price of the lesson, if applicable.</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Target ' . $this->settings_library->get_label('participants'), 'target_participants');
						$target_participants = NULL;
						if (isset($lesson_info->target_participants) && $lesson_info->target_participants > 0) {
							$target_participants = $lesson_info->target_participants;
						}
						$data = array(
							'name' => 'target_participants',
							'id' => 'target_participants',
							'class' => 'form-control',
							'value' => set_value('target_participants', $this->crm_library->htmlspecialchars_decode($target_participants), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div><?php
				}
				foreach ($required_staff_for_session as $type => $staff) { ?>
					<div class="form-group">
					<?php
					echo form_label('Number of ' . Inflect::pluralize($this->settings_library->get_staffing_type_label($type)) .  ' Required', 'staff_required_' . $type);
					$staff_required = 0;
					if (isset($lesson_info->{'staff_required_' . $type}) && $lesson_info->{'staff_required_' . $type} >= 0) {
						$staff_required = $lesson_info->{'staff_required_' . $type};
					}
					$data = array(
						'name' => 'staff_required_' . $type,
						'id' => 'staff_required_' . $type,
						'class' => 'form-control',
						'value' => set_value('staff_required_' . $type, $this->crm_library->htmlspecialchars_decode($staff_required), FALSE),
						'maxlength' => 3,
						'min' => 0,
						'step' => 1
					);
					echo form_number($data);
					?></div><?php
				}
				if ($booking_type == 'event' || $booking_info->project == 1)  {
					?><div class='form-group'><?php
						echo form_label('Online Booking Cut Off', 'booking_cutoff');
						$booking_cutoff = NULL;
						if (isset($lesson_info->booking_cutoff)) {
							$booking_cutoff = $lesson_info->booking_cutoff;
						}
						$data = array(
							'name' => 'booking_cutoff',
							'id' => 'booking_cutoff',
							'class' => 'form-control',
							'value' => set_value('booking_cutoff', $this->crm_library->htmlspecialchars_decode($booking_cutoff), FALSE),
							'maxlength' => 3
						);
						?><div class="input-group"><?php
						echo form_input($data);
						?><div class="input-group-append"><span class="input-group-text">Hours</span></div></div>
						<small class="text-muted form-text">If not set, default of <?php echo $this->settings_library->get('booking_cutoff'); ?> hour(s) applies.</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Minimum Age', 'min_age');
						$min_age = NULL;
						if (isset($lesson_info->min_age)) {
							$min_age = $lesson_info->min_age;
						}
						$data = array(
							'name' => 'min_age',
							'id' => 'min_age',
							'class' => 'form-control',
							'value' => set_value('min_age', $this->crm_library->htmlspecialchars_decode($min_age), FALSE),
							'maxlength' => 3
						);
						?><div class="input-group"><?php
						echo form_input($data);
						?><div class="input-group-append"><span class="input-group-text">Years</span></div></div>
						<?php
						$default_min_age = $this->settings_library->get('min_age');
						if (!empty($booking_info->min_age)) {
							$default_min_age = $booking_info->min_age;
						}
						if (!empty($block_info->min_age)) {
							$default_min_age = $block_info->min_age;
						}
						?>
						<small class="text-muted form-text">If not set, <?php
							if (empty($default_min_age)) {
								echo 'no limits';
							} else {
								echo 'a default of ' . $default_min_age;
							}
							?> will apply.</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Maximum Age', 'max_age');
						$max_age = NULL;
						if (isset($lesson_info->max_age)) {
							$max_age = $lesson_info->max_age;
						}
						$data = array(
							'name' => 'max_age',
							'id' => 'max_age',
							'class' => 'form-control',
							'value' => set_value('max_age', $this->crm_library->htmlspecialchars_decode($max_age), FALSE),
							'maxlength' => 3
						);
						?><div class="input-group"><?php
						echo form_input($data);
						?><div class="input-group-append"><span class="input-group-text">Years</span></div></div>
						<?php
						$default_max_age = $this->settings_library->get('max_age');
						if (!empty($booking_info->max_age)) {
							$default_max_age = $booking_info->max_age;
						}
						if (!empty($block_info->max_age)) {
							$default_max_age = $block_info->max_age;
						}
						?>
						<small class="text-muted form-text">If not set, <?php
						if (empty($default_max_age)) {
							echo 'no limits';
						} else {
							echo 'a default of ' . $default_max_age;
						}
						?> will apply.</small>
					</div><?php
				}
				?>
			</div>
		</div>
	<?php echo form_fieldset_close();
	if ($booking_type == 'booking') {
		echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Requirements</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class="form-group">
						<?php
						echo form_label('PPA cover');
						$options = array(
							'req_ppa_playground' => 'Playground collection',
							'req_ppa_classroom' => 'Classroom collection',
							'req_ppa_meet' => 'Meet in Hall',
							'req_ppa_reg' => 'Registration',
							'req_ppa_changed_before' => 'Children changed before',
							'req_ppa_changed_after' => 'Children changed after',
							'req_ppa_dismissed' => 'Children dismissed by coach',
							'req_ppa_assist' => 'Coach assist with dismissal'
						);
						if (count($options) > 0) {
							foreach ($options as $key => $option) {
								$data = array(
									'name' => $key,
									'value' => 1
								);
								$value = NULL;
								if (isset($lesson_info->$key)) {
									$value = $lesson_info->$key;
								}
								if (set_value($key, $value, FALSE) == 1) {
									$data['checked'] = TRUE;
								}
								?>
								<div class="checkbox-single">
									<label class="checkbox">
										<?php echo form_checkbox($data); ?>
										<?php echo $option; ?>
										<span></span>
									</label>
								</div>
								<?php
							}
						}
						?>
					</div>
					<div class="form-group">
						<?php
						echo form_label('Extra-curricular clubs');
						$options = array(
							'req_extra_perf' => 'Performance',
							'req_extra_cert' => 'Certificates',
							'req_extra_reg' => 'Registration',
							'req_extra_money' => 'Money collections',
							'req_extra_children' => 'Children signed out'
						);
						if (count($options) > 0) {
							foreach ($options as $key => $option) {
								$data = array(
									'name' => $key,
									'value' => 1
								);
								$value = NULL;
								if (isset($lesson_info->$key)) {
									$value = $lesson_info->$key;
								}
								if (set_value($key, $value, FALSE) == 1) {
									$data['checked'] = TRUE;
								}
								?>
								<div class="checkbox-single">
									<label class="checkbox">
										<?php echo form_checkbox($data); ?>
										<?php echo $option; ?>
										<span></span>
									</label>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	if (count($org_attachments) > 0) {
		echo form_fieldset('', ['class' => 'card card-custom']); ?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-paperclip text-contrast'></i></span>
					<h3 class="card-label">Attachments</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
						<?php
						echo form_label('Coach access to customer attachments', 'org_attachments');
						foreach ($org_attachments as $attachmentID => $attachment) {
							$data = array(
								'name' => 'org_attachments[]',
								'value' => $attachmentID
							);

							if ($this->input->post()) {
								$org_attachments_array = $this->input->post('org_attachments');
							}
							if (!is_array($org_attachments_array)) {
								$org_attachments_array = array();
							}
							if (in_array($attachmentID, $org_attachments_array)) {
								$data['checked'] = TRUE;
							}
							?>
							<div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									<?php echo $attachment; ?>
									<span></span>
								</label>
							</div>
						<?php
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
	</div>
<?php echo form_close();
