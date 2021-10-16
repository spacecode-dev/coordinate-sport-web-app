<?php
display_messages();

if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $blockID,
		'tab' => $tab,
		'type' => $booking_info->type,
		'is_project' => $booking_info->project
	);
	$this->load->view('bookings/tabs.php', $data);
}

		echo form_open($submit_to);

			echo form_hidden(array('process' => 1));
			echo form_hidden(array('action' => 'staff'));
			echo form_hidden(array('from_date' => $from_date));
			echo form_hidden(array('to_date' => $to_date));

			$i = 1;
			foreach ($lessons as $lessonID => $lesson_info) {

				// store lesson
				echo form_hidden(array('lessons[]' => $lessonID));

				?><h2><?php
				echo ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')';
				if (!empty($lesson_info->activity)) {
					echo ' - ' . ucwords($lesson_info->activity);
				} else if (!empty($lesson_info->activity_other)) {
					echo ' - ' . ucwords($lesson_info->activity_other);
				}
				?></h2>
				<div class='lesson-staff bulk-staff' data-day='<?php echo $lesson_info->day; ?>' data-booking='<?php echo $lesson_info->bookingID; ?>' data-lesson='<?php echo $lessonID; ?>' data-activity='<?php echo $lesson_info->activityID; ?>'><?php
					echo form_fieldset('', ['class' => 'card card-custom']);
						?><div class='card-header'>
							<div class="card-title">
								<span class="card-icon"><i class='far fa-calendar-check text-contrast'></i></span>
								<h3 class="card-label">Date &amp; Time</h3>
							</div>
						</div>
						<div class="card-body">
							<div class='multi-columns'>
								<div class='form-group'><?php
									echo form_label('Date From <em>*</em>', 'from_' . $lessonID);
									$from = NULL;
									if (isset($from_date)) {
										$from = $from_date;
									}
									if (!empty($lesson_info->startDate) && !empty($from) && strtotime($lesson_info->startDate) > uk_to_mysql_date($from)) {
										$from = mysql_to_uk_date($lesson_info->startDate);
									}
									$data = array(
										'name' => 'from_' . $lessonID,
										'id' => 'from_' . $lessonID,
										'class' => 'form-control datepicker from',
										'value' => set_value('from_' . $lessonID, $this->crm_library->htmlspecialchars_decode($from)),
										'maxlength' => 10,
										'data-mindate' => $block_info->startDate,
										'data-maxdate' => $block_info->endDate
									);
									if (!empty($lesson_info->startDate)) {
										$data['data-mindate'] = $lesson_info->startDate;
									}
									if (!empty($lesson_info->endDate)) {
										$data['data-maxdate'] = $lesson_info->endDate;
									}
									echo form_input($data);
								?></div>
								<div class='form-group'><?php
									echo form_label('Time From <em>*</em>', 'toH_' . $lessonID);
									$fromH = NULL;
									if (isset($lesson_info->startTime)) {
										$fromH = substr($lesson_info->startTime, 0, 2);
									} else if ($recordID == NULL) {
										$fromH = '07';
									}
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('fromH_' . $lessonID, $options, set_value('fromH_' . $lessonID, $this->crm_library->htmlspecialchars_decode($fromH)), 'id="fromH_' . $lessonID . '" class="form-control select2 fromH"');
									$fromM = NULL;
									if (isset($lesson_info->startTime)) {
										$fromM = substr($lesson_info->startTime, 3, 2);
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
									echo form_dropdown('fromM_' . $lessonID, $options, set_value('fromM_' . $lessonID, $this->crm_library->htmlspecialchars_decode($fromM)), 'id="fromM_' . $lessonID . '" class="form-control select2 fromM"');
								?></div>
								<div class='form-group'><?php
									echo form_label('Date To <em>*</em>', 'to_' . $lessonID);
									$to = NULL;
									if (isset($to_date)) {
										$to = $to_date;
									}
									if (!empty($lesson_info->endDate) && !empty($to) && strtotime($lesson_info->endDate) > uk_to_mysql_date($to)) {
										$to = mysql_to_uk_date($lesson_info->endDate);
									}
									$data = array(
										'name' => 'to_' . $lessonID,
										'id' => 'to_' . $lessonID,
										'class' => 'form-control datepicker to',
										'value' => set_value('to_' . $lessonID, $this->crm_library->htmlspecialchars_decode($to)),
										'maxlength' => 10,
										'data-mindate' => $block_info->startDate,
										'data-maxdate' => $block_info->endDate
									);
									if (!empty($lesson_info->startDate)) {
										$data['data-mindate'] = $lesson_info->startDate;
									}
									if (!empty($lesson_info->endDate)) {
										$data['data-maxdate'] = $lesson_info->endDate;
									}
									echo form_input($data);
								?></div>
								<div class='form-group'><?php
									echo form_label('Time To <em>*</em>', 'toH_' . $lessonID);
									$toH = NULL;
									if (isset($lesson_info->endTime)) {
										$toH = substr($lesson_info->endTime, 0, 2);
									} else if ($recordID == NULL) {
										$toH = '22';
									}
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('toH_' . $lessonID, $options, set_value('toH_' . $lessonID, $this->crm_library->htmlspecialchars_decode($toH)), 'id="toH_' . $lessonID . '" class="form-control select2 toH"');
									$toM = NULL;
									if (isset($lesson_info->endTime)) {
										$toM = substr($lesson_info->endTime, 3, 2);
									}
									$options = array();
									$m = 0;
									while ($m <= 59) {
										$m = sprintf("%02d",$m);
										if ($m % 5 == 0) {
											$options[$m] = $m;
										}
										if ($m == 59) {
											$options[$m] = $m;
										}
										$m++;
									}
									echo form_dropdown('toM_' . $lessonID, $options, set_value('toM_' . $lessonID, $this->crm_library->htmlspecialchars_decode($toM)), 'id="toM_' . $lessonID . '" class="form-control select2 toM"');
								?></div>
							</div>
						</div><?php
					echo form_fieldset_close();
					echo form_fieldset('', ['class' => 'card card-custom']);
						?><div class='card-header'>
							<div class="card-title">
								<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
								<h3 class="card-label">Details</h3>
							</div>
						</div>
						<div class="card-body">
							<div class="multi-columns">
								<p><small class='text-muted'><?php
									$staff_lead = 0;
									if (isset($lesson_staff_by_type[$lessonID]['lead'])) {
										$staff_lead = count($lesson_staff_by_type[$lessonID]['lead']);
									}
									$staff_head = 0;
									if (isset($lesson_staff_by_type[$lessonID]['head'])) {
										$staff_head = count($lesson_staff_by_type[$lessonID]['head']);
									}
									$staff_assistant = 0;
									if (isset($lesson_staff_by_type[$lessonID]['assistant'])) {
										$staff_assistant = count($lesson_staff_by_type[$lessonID]['assistant']);
									}
									?><strong>Staff Requirements:</strong><br />
									<?php
									foreach ($required_staff_for_session as $type => $staff_required) {
										echo $this->settings_library->get_staffing_type_label($type); ?>
										<?php $number = (isset($lesson_staff_by_type[$lessonID][$type]) ? count($lesson_staff_by_type[$lessonID][$type]) : 0);?>
										(<span class="staff_<?php echo $type; ?>" data-existing="<?php echo $number; ?>"><?php echo $number; ?></span>/<?php echo $lesson_info->{'staff_required_' . $type}; ?>)<br />
										<?php
									}
									?>
								</small></p>
								<div class='form-group'><?php
									echo form_label('Type <em>*</em>', 'type_' . $lessonID);
									$options = array(
										'' => 'Select',
										'head' => $this->settings_library->get_staffing_type_label('head'),
										'lead' => $this->settings_library->get_staffing_type_label('lead'),
										'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
										'participant' => $this->settings_library->get_staffing_type_label('participant'),
										'observer' => $this->settings_library->get_staffing_type_label('observer')
									);
									echo form_dropdown('type_' . $lessonID, $options, set_value('type_' . $lessonID, $staff_type), 'id="type_' . $lessonID . '" class="form-control select2 staffType"');
								?></div>
								<div class='form-group'><?php
									echo form_label('Staff <em>*</em>', 'staffID_' . $lessonID);
									$options = array(
										'' => 'Select'
									);
									if ($staff->num_rows() > 0) {
										foreach ($staff->result() as $row) {
											$options[$row->staffID] = $row->first . ' ' . $row->surname;
										}
									}
									echo form_dropdown('staffID_' . $lessonID, $options, set_value('staffID_' . $lessonID, $staffID), 'id="staffID_' . $lessonID . '" class="form-control select2-disabled staffID" data-staff="' . set_value('staffID_' . $lessonID, $staffID) . '"');
								?></div>
								<div class="reason"></div>
								<div class='form-group'><?php
									echo form_label('Comment', 'comment_' . $lessonID);
									$data = array(
										'name' => 'comment_' . $lessonID,
										'id' => 'comment_' . $lessonID,
										'class' => 'form-control',
										'value' => set_value('comment_' . $lessonID),
										'maxlength' => 255
									);
									echo form_input($data);
								?></div>
								<?php
								if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll')) {
									?><div class='form-group'><?php
										$data = array(
											'name' => 'salaried_' . $lessonID,
											'id' => 'salaried_' . $lessonID,
											'value' => 1
										);
										if (set_value('salaried_' . $lessonID, $salaried) == 1) {
											$data['checked'] = TRUE;
										}
										?><div class="checkbox">
											<label>
												<?php echo form_checkbox($data); ?>
												Salaried Session
											</label>
										</div>
									</div><?php
								}
								?>
							</div>
						</div>
					<?php echo form_fieldset_close(); ?>
				</div><?php
			$i++;
		}

		if ($this->settings_library->get('send_staff_new_sessions') == 1) {
			echo form_fieldset('', ['class' => 'card card-custom']); ?>
				<div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-bell text-contrast'></i></span>
						<h3 class="card-label">Notifications</h3>
					</div>
				</div>
				<div class="card-body">
					<div class='multi-columns'>
						<div class='form-group'><?php
							$data = array(
								'name' => 'notify_staff',
								'id' => 'notify_staff',
								'value' => 1
							);
							if (set_value('notify_staff') == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Notify staff member(s) of these sessions
									<span></span>
								</label>
							</div>
						</div>
					</div>
				</div><?php
			echo form_fieldset_close();
		}
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
