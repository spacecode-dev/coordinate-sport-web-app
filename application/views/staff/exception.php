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
?>
<div class='card-header'>
	<div class="card-title">
		<span class="card-icon"><i class='far fa-calendar-alt text-contrast'></i></span>
		<h3 class="card-label">Date &amp; Time</h3>
	</div>
</div>
<div class="card-body">
	<div class='multi-columns'>
		<div class='form-group'><?php
			echo form_label('Date From <em>*</em>', 'from');
			$from = NULL;
			if (isset($exception_info->from)) {
				$from = date("d/m/Y", strtotime($exception_info->from));
			}
			$data = array(
				'name' => 'from',
				'id' => 'from',
				'class' => 'form-control datepicker',
				'value' => set_value('from', $this->crm_library->htmlspecialchars_decode($from), FALSE),
				'maxlength' => 10
			);
			echo form_input($data);
		?></div>
		<div class='form-group'><?php
			echo form_label('Time From <em>*</em>', 'toH');
			$fromH = NULL;
			if (isset($exception_info->from)) {
				$from_parts = explode(' ', $exception_info->from);
				if (isset($from_parts[1])) {
					$fromH = substr($from_parts[1], 0, 2);
				}
			} else if ($exceptionID == NULL) {
				$fromH = '07';
			}
			$options = array();
			$h = 6;
			while ($h <= 23) {
				$h = sprintf("%02d",$h);
				$options[$h] = $h;
				$h++;
			}
			echo form_dropdown('fromH', $options, set_value('fromH', $this->crm_library->htmlspecialchars_decode($fromH), FALSE), 'id="fromH" class="form-control select2"');
			$fromM = NULL;
			if (isset($exception_info->from)) {
				$from_parts = explode(' ', $exception_info->from);
				if (isset($from_parts[1])) {
					$fromM = substr($from_parts[1], 3, 5);
					$fromM = substr($fromM, 0, 2); // trim microseconds off
				}
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
			echo form_dropdown('fromM', $options, set_value('fromM', $this->crm_library->htmlspecialchars_decode($fromM), FALSE), 'id="fromM" class="form-control select2"');
		?></div>
		<div class='form-group'><?php
			echo form_label('Date To <em>*</em>', 'to');
			$to = NULL;
			if (isset($exception_info->to)) {
				$to = date("d/m/Y", strtotime($exception_info->to));
			}
			$data = array(
				'name' => 'to',
				'id' => 'to',
				'class' => 'form-control datepicker',
				'value' => set_value('to', $this->crm_library->htmlspecialchars_decode($to), FALSE),
				'maxlength' => 10
			);
			echo form_input($data);
		?></div>
		<div class='form-group'><?php
			echo form_label('Time To <em>*</em>', 'toH');
			$toH = NULL;
			if (isset($exception_info->to)) {
				$to_parts = explode(' ', $exception_info->to);
				if (isset($to_parts[1])) {
					$toH = substr($to_parts[1], 0, 2);
				}
			} else if ($exceptionID == NULL) {
				$toH = '22';
			}
			$options = array();
			$h = 6;
			while ($h <= 23) {
				$h = sprintf("%02d",$h);
				$options[$h] = $h;
				$h++;
			}
			echo form_dropdown('toH', $options, set_value('toH', $this->crm_library->htmlspecialchars_decode($toH), FALSE), 'id="toH" class="form-control select2"');
			$toM = NULL;
			if (isset($exception_info->to)) {
				$to_parts = explode(' ', $exception_info->to);
				if (isset($to_parts[1])) {
					$toM = substr($to_parts[1], 3, 5);
					$toM = substr($toM, 0, 2); // trim microseconds off
				}
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
			echo form_dropdown('toM', $options, set_value('toM', $this->crm_library->htmlspecialchars_decode($toM), FALSE), 'id="toM" class="form-control select2"');
		?></div>
	</div><?php
echo form_fieldset_close();
echo form_fieldset('', ['class' => 'card card-custom']);
?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Details</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<div class='form-group'><?php
				echo form_label('Type <em>*</em>', 'type');
				$type = NULL;
				if (isset($exception_info->type)) {
					$type = $exception_info->type;
				}
				$options = array(
					'' => 'Select',
					'authorised' => 'Authorised Absence',
					'unauthorised' => 'Unauthorised Absence',
					'other' => 'Other',
				);
				echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
			?></div>
			<div class='form-group'><?php
				echo form_label('Reason <em>*</em>', 'reason');
				$reason = NULL;
				if (isset($exception_info->reason)) {
					$reason = $exception_info->reason;
				}
				$options = array(
					'' => 'Select',
					'holiday' => 'Holiday',
					'appointment' => 'Appointment',
					'sick leave' => 'Sick Leave',
					'special leave' => 'Special Leave',
					'unavailable' => 'Unavailable',
					'other' => 'Other',
				);
				echo form_dropdown('reason', $options, set_value('reason', $this->crm_library->htmlspecialchars_decode($reason), FALSE), 'id="reason" class="form-control select2"');
				?></div>
			<div class='form-group'><?php
				echo form_label('Upload Attachment', 'file');
				$data = array(
					'name' => 'file',
					'id' => 'file',
					'class' => 'custom-file-input'
				);
				?><div class="custom-file">
					<?php echo form_upload($data); ?>
					<label class="custom-file-label" for="file">Choose file</label>
				</div>
				<?php if (isset($exception_info->attachment_name) && $exception_info->attachment_name) { ?>
					<div class="attachments">
						<ul>
							<li>
								<a href="/attachment/staff_availability_exception/<?php echo $exception_info->path ?>" target="_blank"><?php echo $exception_info->attachment_name ?></a>
							</li>
						</ul>
					</div>
				<?php } ?>
			</div>

			<div class='form-group'><?php
				echo form_label('Notes', 'note');
				$note = NULL;
				if (isset($exception_info->note)) {
					$note = $exception_info->note;
				}
				$data = array(
					'name' => 'note',
					'id' => 'note',
					'class' => 'form-control',
					'value' => set_value('note', $this->crm_library->htmlspecialchars_decode($note), FALSE),
				);
				echo form_textarea($data);
			?></div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div><?php
echo form_close();
