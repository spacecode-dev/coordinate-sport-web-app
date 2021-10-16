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
	echo form_hidden(array('action' => $type));
	echo form_hidden(array('from' => $from));
	echo form_hidden(array('to' => $to));

	$i = 1;
	foreach ($lessons as $lessonID => $lesson_info) {

		// store lesson
		echo form_hidden(array('lessons[]' => $lessonID));

		foreach ($lesson_info->dates as $date) {

			?><h2><?php
			echo ucwords($lesson_info->day) . ' (' . substr($lesson_info->startTime, 0, 5) . '-' . substr($lesson_info->endTime, 0, 5) . ')' . ' - ' . mysql_to_uk_date($date);
			if (!empty($lesson_info->activity)) {
				echo ' - ' . ucwords($lesson_info->activity);
			} else if (!empty($lesson_info->activity_other)) {
				echo ' - ' . ucwords($lesson_info->activity_other);
			}
			?></h2>
			<div class='exception lesson-staff' data-day='<?php echo $lesson_info->day; ?>' data-booking='<?php echo $lesson_info->bookingID; ?>' data-lesson='<?php echo $lesson_info->lessonID; ?>' data-activity='<?php echo $lesson_info->activityID; ?>' data-fromH='<?php echo substr($lesson_info->startTime, 0, 2); ?>' data-fromM='<?php echo substr($lesson_info->startTime, 3, 2); ?>' data-toH='<?php echo substr($lesson_info->endTime, 0, 2); ?>' data-toM='<?php echo substr($lesson_info->endTime, 3, 2); ?>' data-date='<?php echo mysql_to_uk_date($date); ?>' data-type='bulk-exception'><?php
				echo form_fieldset('', ['class' => 'card card-custom']); ?>
					<div class='card-header'>
						<div class="card-title">
							<span class="card-icon"><i class='far fa-calendar-check text-contrast'></i></span>
							<h3 class="card-label">Details</h3>
						</div>
					</div>
					<div class="card-body">
						<div class="multi-columns">
							<?php
							$hidden_fields = array(
								'type_' . $lessonID .'_' . $date => $type,
								'hidden_reason_select_' . $lessonID .'_' . $date => set_value('reason_select_' . $lessonID .'_' . $date)
							);
							echo form_hidden($hidden_fields);
							?>
							<div class='form-group'><?php
								echo form_label('Staff <em>*</em>', 'fromID_' . $lessonID .'_' . $date);
								$options = array(
									'' => 'Select'
								);
								if ($staff->num_rows() > 0) {
									foreach ($staff->result() as $row) {
										if (array_key_exists($row->staffID, $lesson_info->staff)) {
											$options[$row->staffID] = [
												'name' => $row->first . ' ' . $row->surname,
												'extras' => 'data-staffType="' . $lesson_info->staff[$row->staffID] . '"'
											];
										}
									}
								}
								echo form_dropdown_advanced('fromID_' . $lessonID .'_' . $date, $options, set_value('fromID_' . $lessonID .'_' . $date, $this->crm_library->htmlspecialchars_decode($staffID)), 'id="fromID_' . $lessonID .'_' . $date . '" class="form-control select2 fromID"');
							?></div>
							<div class='form-group'><?php
								echo form_label('Replacement', 'staffID_' . $lessonID .'_' . $date);
								$options = array(
									'' => 'Select'
								);
								if ($staff->num_rows() > 0) {
									foreach ($staff->result() as $row) {
										$options[$row->staffID] = $row->first . ' ' . $row->surname;
									}
								}
								echo form_dropdown('staffID_' . $lessonID .'_' . $date, $options, set_value('staffID_' . $lessonID .'_' . $date, $this->crm_library->htmlspecialchars_decode($replacementID)), 'id="staffID_' . $lessonID .'_' . $date . '" class="form-control select2-disabled staffID" data-staff="' . set_value('staffID_' . $lessonID .'_' . $date, $this->crm_library->htmlspecialchars_decode($replacementID)) . '"');
							?></div>
							<div class="reason"></div>
							<div class='form-group'><?php
								echo form_label('Assign To <em>*</em>', 'assign_to_' . $lessonID .'_' . $date);
								$options = array(
									'' => 'Select',
									'staff' => 'Staff',
									'company' => 'Company',
									'customer' => 'Customer'
								);
								echo form_dropdown('assign_to_' . $lessonID .'_' . $date, $options, set_value('assign_to_' . $lessonID .'_' . $date), 'id="assign_to_' . $lessonID .'_' . $date . '" class="form-control select2"');
							?></div>
							<div class='form-group'><?php
								echo form_label('Reason <em>*</em>', 'reason_select_' . $lessonID .'_' . $date);
								$reason_select = NULL;
								if (isset($exception_info->reason_select)) {
									$reason_select = $exception_info->reason_select;
								}
								$options = array(
									'' => array(
										'name' => 'Select',
										'extras' => NULL
									),
									'authorised absence' => array(
										'name' => 'Authorised Absence',
										'extras' => 'data-assigned="staff"'
									),
									'unauthorised absence' => array(
										'name' => 'Unauthorised Absence',
										'extras' => 'data-assigned="staff"'
									),
									'sick' => array(
										'name' => 'Sick',
										'extras' => 'data-assigned="staff"'
									),
									'timetable conflict' => array(
										'name' => 'Timetable Conflict',
										'extras' => 'data-assigned="company"'
									),
									'other' => array(
										'name' => 'Other (Please specify)',
										'extras' => 'data-assigned="staff company customer"'
									)
								);
								echo form_dropdown_advanced('reason_select_' . $lessonID .'_' . $date, $options, set_value('reason_select_' . $lessonID .'_' . $date), 'id="reason_select_' . $lessonID .'_' . $date . '" class="form-control select2"');
							?></div>
							<div class='form-group'><?php
								echo form_label('Reason - Other<em>*</em>', 'reason_' . $lessonID .'_' . $date);
								$reason = NULL;
								if (isset($exception_info->reason)) {
									$reason = $exception_info->reason;
								}
								$data = array(
									'name' => 'reason_' . $lessonID .'_' . $date,
									'id' => 'reason_' . $lessonID .'_' . $date,
									'class' => 'form-control',
									'value' => set_value('reason_' . $lessonID .'_' . $date),
									'maxlength' => 255
								);
								echo form_input($data);
							?></div>
						</div>
					</div>
				<?php echo form_fieldset_close(); ?>
			</div><?php
		}
		$i++;
	}
	if ($this->settings_library->get('send_staff_cancelled_sessions') == 1) {
		echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-bell text-contrast'></i></span>
					<h3 class="card-label">Notifications</h3>
				</div>
			</div>
			<div class="card-body">
				<div class="multi-columns">
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
								Notify staff member(s) of these exceptions
								<span></span>
							</label>
						</div>
						<small class="text-muted form-text">Notifies new staff member(s) only and all staff for cancellations</small>
					</div>
				</div>
			</div><?php
		echo form_fieldset_close();
	}
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Send
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
