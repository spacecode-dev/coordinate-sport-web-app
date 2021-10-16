<?php
display_messages();

if ($bookingID != NULL && $lessonID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $lesson_info->blockID,
		'lessonID' => $lessonID,
		'tab' => $tab
	);
	$this->load->view('sessions/tabs.php', $data);
}
?>
<div class='lesson-staff' data-day='<?php echo $lesson_info->day; ?>' data-booking='<?php echo $lesson_info->bookingID; ?>' data-lesson='<?php echo $lesson_info->lessonID; ?>' data-activity='<?php echo $lesson_info->activityID; ?>' data-fromH='<?php echo substr($lesson_info->startTime, 0, 2); ?>' data-fromM='<?php echo substr($lesson_info->startTime, 3, 2); ?>' data-toH='<?php echo substr($lesson_info->endTime, 0, 2); ?>' data-toM='<?php echo substr($lesson_info->endTime, 3, 2); ?>' data-type='exception'>
	<?php
	echo form_open_multipart($submit_to, 'class="exception"');
		 echo form_fieldset('', ['class' => 'card card-custom']);
			$reason_select = NULL;
			if (isset($exception_info->reason_select)) {
				$reason_select = $exception_info->reason_select;
			}
			echo form_hidden(array('hidden_reason_select' => set_value('reason_select', $this->crm_library->htmlspecialchars_decode($reason_select), FALSE)));
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-calendar-check text-contrast'></i></span>
					<h3 class="card-label">Details</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'><?php
						echo form_label('Date <em>*</em>', 'date');
						$date = NULL;
						if (isset($exception_info->date)) {
							$date = date("d/m/Y", strtotime($exception_info->date));
						}
						$data = array(
							'name' => 'date',
							'id' => 'date',
							'class' => 'form-control datepicker date',
							'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
							'maxlength' => 10,
							'data-mindate' => $lesson_info->startDate,
							'data-maxdate' => $lesson_info->endDate,
							'data-onlyday' => $lesson_info->day
						);
						if (empty($lesson_info->startDate)) {
							$data['data-mindate'] = $block_info->startDate;
						}
						if (empty($lesson_info->endDate)) {
							$data['data-maxdate'] = $block_info->endDate;
						}
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Type <em>*</em>', 'type');
						$type = NULL;
						if (isset($exception_info->type)) {
							$type = $exception_info->type;
						}
						$options = array(
							'' => 'Select',
							'cancellation' => 'Cancellation',
							'staffchange' => 'Staff Change'
						);
						echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
					?></div>
					<div class='form-group'><?php
						echo form_label('Staff <em>*</em>', 'fromID');
						$fromID = NULL;
						if (isset($exception_info->fromID)) {
							$fromID = $exception_info->fromID;
						}
						$options = array(
							'' => 'Select'
						);
						if ($staff->num_rows() > 0) {
							foreach ($staff->result() as $row) {
								$options[$row->staffID] = [
									'name' => $row->first . ' ' . $row->surname,
									'extras' => 'data-staffType="' . $row->staffType . '"'
								];
							}
						}
						echo form_dropdown_advanced('fromID', $options, set_value('fromID', $this->crm_library->htmlspecialchars_decode($fromID), FALSE), 'id="fromID" class="form-control select2 fromID"');
					?></div>
					<div class='form-group'><?php
						echo form_label('Replacement', 'staffID');
						$staffID = NULL;
						if (isset($exception_info->staffID)) {
							$staffID = $exception_info->staffID;
						}
						$options = array(
							'' => 'Select'
						);
						if ($replacement_staff->num_rows() > 0) {
							$options[0] = 'No Replacement Required';
							foreach ($replacement_staff->result() as $row) {
								$options[$row->staffID] = $row->first . ' ' . $row->surname;
							}
						}
						echo form_dropdown('staffID', $options,
							set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE),
							'id="staffID" class="form-control select2-disabled staffID" data-staff="' .
							set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE) . '"');
						?>
					</div>
					<div class="reason"></div>
					<div class='form-group'><?php
						echo form_label('Assign To <em>*</em>', 'assign_to');
						$assign_to = NULL;
						if (isset($exception_info->assign_to)) {
							$assign_to = $exception_info->assign_to;
						}
						$options = array(
							'' => 'Select',
							'staff' => 'Staff',
							'company' => 'Company',
							'customer' => 'Customer'
						);
						echo form_dropdown('assign_to', $options, set_value('assign_to', $this->crm_library->htmlspecialchars_decode($assign_to), FALSE), 'id="assign_to" class="form-control select2"');
					?></div>
					<div class='form-group'><?php
						echo form_label('Reason <em>*</em>', 'reason_select');
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
						echo form_dropdown_advanced('reason_select', $options, set_value('reason_select', $this->crm_library->htmlspecialchars_decode($reason_select), FALSE), 'id="reason_select" class="form-control select2"');
					?></div>
					<div class='form-group'><?php
						echo form_label('Reason - Other<em>*</em>', 'reason');
						$reason = NULL;
						if (isset($exception_info->reason)) {
							$reason = $exception_info->reason;
						}
						$data = array(
							'name' => 'reason',
							'id' => 'reason',
							'class' => 'form-control',
							'value' => set_value('reason', $this->crm_library->htmlspecialchars_decode($reason), FALSE),
							'maxlength' => 255
						);
						echo form_input($data);
					?></div><?php
					if ($this->settings_library->get('send_staff_cancelled_sessions') == 1) {
						?><div class='form-group'><?php
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
									Notify staff member(s) of this exception
									<span></span>
								</label>
							</div>
							<small class="text-muted form-text">Notifies new staff member only and all staff for cancellations</small>
						</div>
					</div><?php
				}
				?>
			</div>
		</div>
		<?php echo form_fieldset_close(); ?>
		<div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div>
	<?php echo form_close(); ?>
</div>
