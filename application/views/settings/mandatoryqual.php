<?php
display_messages();
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
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($qual_info->name)) {
						$name = $qual_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'>
					<?php
					$data = array(
						'name' => 'require_issue_expiry_date',
						'id' => 'field_require_issue_expiry_date',
						'value' => 1
					);
					$require_issue_expiry_date = NULL;
					if (isset($qual_info->require_issue_expiry_date)) {
						$require_issue_expiry_date = $qual_info->require_issue_expiry_date;
					}
					if($require_issue_expiry_date == 1){
						$data["checked"] = "checked";
					}
					?>
					<div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Require Issue & Expiry Date
							<span></span>
						</label>
					</div>
					<?php
					$data = array(
						'name' => 'require_reference',
						'id' => 'field_require_reference',
						'value' => 1
					);
					$require_reference = NULL;
					if (isset($qual_info->require_reference)) {
						$require_reference = $qual_info->require_reference;
					}
					if($require_reference == 1){
						$data["checked"] = "checked";
					}
					?>
					<div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Require Reference
							<span></span>
						</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Tag', 'field_tag');
					$tag = NULL;
					if (isset($qual_info->tag)) {
						$tag = $qual_info->tag;
					}
					$data = array(
						'name' => 'tag',
						'id' => 'field_tag',
						'class' => 'form-control',
						'value' => set_value('tag', $this->crm_library->htmlspecialchars_decode($tag), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					?></div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
				<h3 class="card-label">Pay Rates</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Standard Pay Rate', 'hourly_rate_label');
					$hourlyRate = 0;
					if (isset($qual_info->hourly_rate)) {
						$hourlyRate = $qual_info->hourly_rate;
					}
					$data = array(
						'name' => 'hourly_rate',
						'id' => 'hourly_rate',
						'class' => 'form-control',
						'value' => set_value('hourly_rate', $this->crm_library->htmlspecialchars_decode($hourlyRate), FALSE),
						'maxlength' => 10,
						'min' => 0,
						'step' => 0.01
					);
					?><div class="input-group">
						<?php echo form_number($data); ?>
						<div span class="input-group-append"><span class="input-group-text">Per hour</span></div>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Use Increased Rate After X Months Service', 'length_label');
					$length = 0;
					if (isset($qual_info->length_increment)) {
						$length = $qual_info->length_increment;
					}
					$data = array(
						'name' => 'length_increment',
						'id' => 'length_increment',
						'class' => 'form-control',
						'value' => set_value('length_increment', $this->crm_library->htmlspecialchars_decode($length), FALSE),
						'maxlength' => 3,
						'min' => 0,
						'step' => 1
					);
					?><div class="input-group">
						<?php echo form_number($data); ?>
						<div span class="input-group-append"><span class="input-group-text">Months</span></div>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Increased Pay Rate', 'incremental_rate_label');
					$incrementalRate = 0;
					if (isset($qual_info->incremental_rate)) {
						$incrementalRate = $qual_info->incremental_rate;
					}
					$data = array(
						'name' => 'incremental_rate',
						'id' => 'incremental_rate',
						'class' => 'form-control',
						'value' => set_value('incremental_rate', $this->crm_library->htmlspecialchars_decode($incrementalRate), FALSE),
						'maxlength' => 10,
						'min' => 0,
						'step' => 0.01
					);
					?>
					<div class="input-group">
						<?php echo form_number($data); ?>
						<div span class="input-group-append"><span class="input-group-text">Per hour</span></div>
					</div>
				</div>
			</div>
		<?php echo form_fieldset_close(); ?>
		<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Session Type Override Pay Rates</h3>
				</div>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
					<tr>
						<th>Session Type</th>
						<th colspan="3">Hourly Standard Pay Rate</th>
						<th colspan="3">Hourly Increased Pay Rate</th>
					</tr>
					</thead>
					<tbody>
					<tr>
						<td></td>
						<?php foreach ($staffing_types as $key => $type) {?>
							<td for="staff_standart_payrate"><?php echo $type ?></td>
						<?php }?>
						<?php foreach ($staffing_types as $key => $type) {?>
							<td for="staff_increased_payrate"><?php echo $type ?></td>
						<?php }?>
					</tr>
					<?php
					if (!is_array($session_types)) {
						$session_types = [];
					}

					foreach ($session_types as $type) {
						?><tr>
						<td><?php echo $type->name; ?></td>
						<?php if ($session_rates[$type->typeID]['session_rate_overwrite']) { ?>
							<td colspan="6" class="center">
								Session Type Hourly Rate Override in use.
							</td>
						<?php } else {
							foreach ($staffing_types as $key => $name) { ?>
								<td><?php
									$data = array(
										'name' => "session_override_rate[". $type->typeID . "][". $key . "]",
										'id' => 'session_override_rate_' . $type->typeID . '_' . $key,
										'class' => 'form-control',
										'value' => set_value('session_override_rate['. $type->typeID . '][\''. $key .'\']',
											isset($session_rates[$type->typeID]['pay_rate']) ? number_format((float)$this->crm_library->htmlspecialchars_decode(json_decode($session_rates[$type->typeID]['pay_rate'], true)[$key]), 2) : '0.00', FALSE),
										'maxlength' => 10,
										'min' => 0,
										'step' => 0.01
									); ?>
									<div class="input-group">
										<?php echo form_number($data); ?>
										<div class="input-group-append"><span class="input-group-text">Per hour</span></div>
									</div>
								</td>
							<?php }
							foreach ($staffing_types as $key => $name) { ?>
								<td><?php
									$data = array(
										'name' => "session_increased_override_rate[". $type->typeID . "][". $key . "]",
										'id' => 'session_increased_override_rate_' . $type->typeID . '_' . $key,
										'class' => 'form-control',
										'value' => set_value('session_increased_override_rate['. $type->typeID . '][\''. $key .'\']',
											isset($session_rates[$type->typeID]['increased_pay_rate']) ? number_format((float)$this->crm_library->htmlspecialchars_decode(json_decode($session_rates[$type->typeID]['increased_pay_rate'], true)[$key]), 2) : '0.00', FALSE),
										'maxlength' => 10,
										'min' => 0,
										'step' => 0.01
									); ?>
									<div class="input-group">
										<?php echo form_number($data); ?>
										<div class="input-group-append"><span class="input-group-text">Per hour</span></div>
									</div>
								</td>
							<?php } ?>
						<?php } ?>
						</tr><?php
					}
					?>
					</tbody>
				</table>
			</div>
		<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
