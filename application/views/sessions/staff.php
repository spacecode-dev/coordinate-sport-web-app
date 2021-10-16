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
<div class='lesson-staff' data-day='<?php echo $lesson_info->day; ?>' data-booking='<?php echo $lesson_info->bookingID; ?>' data-lesson='<?php if ($lessonID != NULL) { echo $lesson_info->lessonID; } ?>' data-activity='<?php echo $lesson_info->activityID; ?>'>
<?php
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-calendar-check text-contrast'></i></span>
				<h3 class="card-label">Date &amp; Time</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Date From <em>*</em>', 'from');
					$from = NULL;
					if (isset($staff_info->startDate)) {
						$from = date("d/m/Y", strtotime($staff_info->startDate));
					} else if (isset($block_info->startDate)) {
						$from = date("d/m/Y", strtotime($block_info->startDate));
					}
					$data = array(
						'name' => 'from',
						'id' => 'from',
						'class' => 'form-control datepicker from',
						'value' => set_value('from', $this->crm_library->htmlspecialchars_decode($from), FALSE),
						'maxlength' => 10,
						'data-mindate' => $lesson_info->startDate,
						'data-maxdate' => $lesson_info->endDate
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
					echo form_label('Time From <em>*</em>', 'toH');
					$fromH = NULL;
					if (isset($staff_info->startTime)) {
						$fromH = substr($staff_info->startTime, 0, 2);
					} else if (isset($lesson_info->startTime)) {
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
					echo form_dropdown('fromH', $options, set_value('fromH', $this->crm_library->htmlspecialchars_decode($fromH), FALSE), 'id="fromH" class="form-control select2 fromH"');
					$fromM = NULL;
					if (isset($staff_info->startTime)) {
						$fromM = substr($staff_info->startTime, 3, 2);
					} else if (isset($lesson_info->startTime)) {
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
					echo form_dropdown('fromM', $options, set_value('fromM', $this->crm_library->htmlspecialchars_decode($fromM), FALSE), 'id="fromM" class="form-control select2 fromM"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Date To <em>*</em>', 'to');
					$to = NULL;
					if (isset($staff_info->endDate)) {
						$to = date("d/m/Y", strtotime($staff_info->endDate));
					} else if (isset($block_info->endDate)) {
						$to = date("d/m/Y", strtotime($block_info->endDate));
					}
					$data = array(
						'name' => 'to',
						'id' => 'to',
						'class' => 'form-control datepicker to',
						'value' => set_value('to', $this->crm_library->htmlspecialchars_decode($to), FALSE),
						'maxlength' => 10,
						'data-mindate' => $lesson_info->startDate,
						'data-maxdate' => $lesson_info->endDate
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
					echo form_label('Time To <em>*</em>', 'toH');
					$toH = NULL;
					if (isset($staff_info->endTime)) {
						$toH = substr($staff_info->endTime, 0, 2);
					} else if (isset($lesson_info->endTime)) {
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
					echo form_dropdown('toH', $options, set_value('toH', $this->crm_library->htmlspecialchars_decode($toH), FALSE), 'id="toH" class="form-control select2 toH"');
					$toM = NULL;
					if (isset($staff_info->endTime)) {
						$toM = substr($staff_info->endTime, 3, 2);
					} else if (isset($lesson_info->endTime)) {
						$toM = substr($lesson_info->endTime, 3, 2);
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
					echo form_dropdown('toM', $options, set_value('toM', $this->crm_library->htmlspecialchars_decode($toM), FALSE), 'id="toM" class="form-control select2 toM"');
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
			<p><small class="text-muted form-text"><?php
			$staff_lead = 0;
			if (isset($lesson_staff['lead'])) {
				$staff_lead = count($lesson_staff['lead']);
			}
			$staff_head = 0;
			if (isset($lesson_staff['head'])) {
				$staff_head = count($lesson_staff['head']);
			}
			$staff_assistant = 0;
			if (isset($lesson_staff['assistant'])) {
				$staff_assistant = count($lesson_staff['assistant']);
			}
			?><strong>Staff Requirements:</strong><br />
            <?php
            foreach ($required_staff_for_session as $type => $staff_required) {
                echo $this->settings_library->get_staffing_type_label($type); ?>
                <?php $number = (isset($lesson_staff[$type]) ? count($lesson_staff[$type]) : 0);?>
                (<span class="staff_<?php echo $type; ?>" data-existing="<?php echo $number; ?>"><?php echo $number; ?></span>/<?php echo $lesson_info->{'staff_required_' . $type}; ?>)<br />
                <?php
            }
            ?></small></p>
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Type <em>*</em>', 'type');
					$type = NULL;
					if (isset($staff_info->type)) {
						$type = $staff_info->type;
					}
					$options = array(
						'' => 'Select',
						'head' => $this->settings_library->get_staffing_type_label('head'),
						'lead' => $this->settings_library->get_staffing_type_label('lead'),
						'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
						'participant' => $this->settings_library->get_staffing_type_label('participant'),
						'observer' => $this->settings_library->get_staffing_type_label('observer')
					);
					echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2 staffType"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Staff <em>*</em>', 'staffID');
					$staffID = NULL;
					if (isset($staff_info->staffID)) {
						$staffID = $staff_info->staffID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' . $row->surname;
						}
					}
					echo form_dropdown('staffID', $options, set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE), 'id="staffID" class="form-control select2-disabled staffID" data-staff="' . set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE) . '"');
				?></div>
				<div class="reason" id="reason-staff-list"></div>
				<div class='form-group'><?php
					echo form_label('Comment', 'comment');
					$comment = NULL;
					if (isset($staff_info->comment)) {
						$comment = $staff_info->comment;
					}
					$data = array(
						'name' => 'comment',
						'id' => 'comment',
						'class' => 'form-control',
						'value' => set_value('comment', $this->crm_library->htmlspecialchars_decode($comment), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div><?php
				if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll')) {
					?><div class='form-group'><?php
						$salaried = NULL;
						if (isset($staff_info->salaried)) {
							$salaried = $staff_info->salaried;
						}
                        $options = array(
                            '' => 'Select Pay Type',
                            '0' => 'Non-Salaried Session',
                            '1' => 'Salaried Session'
                        );
						echo form_dropdown('salaried', $options, $salaried, 'id="salaried" class="form-control select2" data-minimum-results-for-search="-1"');


						$data = array(
							'name' => 'salaried',
							'id' => 'salaried',
							'value' => 1
						);
						if (set_value('salaried', $salaried) == 1) {
							$data['checked'] = TRUE;
						}
						?>
					</div><?php
				}
				if ($this->settings_library->get('send_staff_new_sessions') == 1) {
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
								Notify staff member of this session
								<span></span>
							</label>
						</div>
					</div><?php
				}
			?></div>
		</div><?php
	echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close(); ?>
</div>
