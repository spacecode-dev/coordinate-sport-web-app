<?php
display_messages();
echo form_open_multipart($submit_to, 'id="equipment_booking"');
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-folder-open text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Equipment <em>*</em>', 'equipmentID');
					$equipmentID = NULL;
					if (isset($booking_info->equipmentID)) {
						$equipmentID = $booking_info->equipmentID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($equipment->num_rows() > 0) {
						foreach ($equipment->result() as $row) {
							$options[$row->equipmentID] = $row->name;
						}
					}
					echo form_dropdown('equipmentID', $options, set_value('equipmentID', $this->crm_library->htmlspecialchars_decode($equipmentID), FALSE), 'id="equipmentID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Type <em>*</em>', 'type');
					$type = NULL;
					if (isset($booking_info->type)) {
						$type = $booking_info->type;
					}
					$options = array(
						'' => 'Select',
						'staff' => 'Staff',
						'org' => $this->settings_library->get_label('customer'),
						'contact' => 'Parent/Contact',
						'child' => 'Child'
					);
					echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Staff <em>*</em>', 'staffID');
					$staffID = NULL;
					if (isset($booking_info->staffID)) {
						$staffID = $booking_info->staffID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' . $row->surname;
						}
					}
					echo form_dropdown('staffID', $options, set_value('staffID', $this->crm_library->htmlspecialchars_decode($staffID), FALSE), 'id="staffID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('customer') . ' <em>*</em>', 'orgID');
					$orgID = NULL;
					if (isset($booking_info->orgID)) {
						$orgID = $booking_info->orgID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($orgs->num_rows() > 0) {
						foreach ($orgs->result() as $row) {
							$options[$row->orgID] = $row->name;
						}
					}
					echo form_dropdown('orgID', $options, set_value('orgID', $this->crm_library->htmlspecialchars_decode($orgID), FALSE), 'id="orgID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Parent/Contact <em>*</em>', 'contactID');
					$contactID = NULL;
					if (isset($booking_info->contactID)) {
						$contactID = $booking_info->contactID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($contacts->num_rows() > 0) {
						foreach ($contacts->result() as $row) {
							$options[$row->contactID] = $row->first_name . ' ' . $row->last_name;
						}
					}
					echo form_dropdown('contactID', $options, set_value('contactID', $this->crm_library->htmlspecialchars_decode($contactID), FALSE), 'id="contactID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Child <em>*</em>', 'childID');
					$childID = NULL;
					if (isset($booking_info->childID)) {
						$childID = $booking_info->childID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($children->num_rows() > 0) {
						foreach ($children->result() as $row) {
							$options[$row->childID] = $row->first_name . ' ' . $row->last_name;
						}
					}
					echo form_dropdown('childID', $options, set_value('childID', $this->crm_library->htmlspecialchars_decode($childID), FALSE), 'id="childID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Return Date <em>*</em>', 'dateIn');
					$dateIn = NULL;
					if (isset($booking_info->dateIn)) {
						$dateIn = mysql_to_uk_date($booking_info->dateIn);
					}
					$data = array(
						'name' => 'dateIn',
						'id' => 'dateIn',
						'class' => 'form-control datepicker datepicker-future',
						'value' => set_value('dateIn', $this->crm_library->htmlspecialchars_decode($dateIn), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Return Time <em>*</em>', 'toH');
					$timeH = NULL;
					if (isset($booking_info->dateIn)) {
						$from_parts = explode(' ', $booking_info->dateIn);
						if (isset($from_parts[1])) {
							$timeH = substr($from_parts[1], 0, 2);
						}
					} else if ($bookingID == NULL) {
						$timeH = '14';
					}
					$options = array();
					$h = 6;
					while ($h <= 23) {
						$h = sprintf("%02d",$h);
						$options[$h] = $h;
						$h++;
					}
					echo form_dropdown('timeH', $options, set_value('timeH', $this->crm_library->htmlspecialchars_decode($timeH), FALSE), 'id="timeH" class="form-control select2"');
					$timeM = NULL;
					if (isset($booking_info->dateIn)) {
						$from_parts = explode(' ', $booking_info->dateIn);
						if (isset($from_parts[1])) {
							$timeM = substr($from_parts[1], 3, 5);
							$timeM = substr($timeM, 0, 2); // trim microseconds off
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
					echo form_dropdown('timeM', $options, set_value('timeM', $this->crm_library->htmlspecialchars_decode($timeM), FALSE), 'id="timeM" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Quantity <em>*</em>', 'quantity');
					$quantity = NULL;
					if (isset($booking_info->quantity)) {
						$quantity = $booking_info->quantity;
					}
					$data = array(
						'name' => 'quantity',
						'id' => 'quantity',
						'class' => 'form-control',
						'value' => set_value('quantity', $this->crm_library->htmlspecialchars_decode($quantity), FALSE),
						'maxlength' => 10,
						'min' => 1
					);
					echo form_number($data);
				?></div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
