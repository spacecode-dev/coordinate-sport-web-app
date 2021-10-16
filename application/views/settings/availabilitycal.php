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
			<div class='form-group'><?php
				echo form_label('Name <em>*</em>', 'field_name');
				$name = NULL;
				if (isset($cal_info->name)) {
					$name = $cal_info->name;
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
			<div class='form-group'><?php
				echo form_label($this->settings_library->get_label('brand') . ' <em>*</em>', 'brandID');
				$brandID = NULL;
				if (isset($cal_info->brandID)) {
					$brandID = $cal_info->brandID;
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
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-futbol text-contrast'></i></span>
				<h3 class="card-label">Activities</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class="form-group">
					<?php
					if (count($activities) == 0) {
						echo '<div class="alert alert-info">
							<p>No activities defined, ' . anchor('settings/activities/new', 'create one') . '</p>
						</div>';
					} else {
						foreach ($activities as $activityID => $details) {
							$data = array(
								'name' => 'activities[]',
								'value' => $activityID
							);

							if ($this->input->post()) {
								$activities_array = $this->input->post('activities');
							}
							if (!is_array($activities_array)) {
								$activities_array = array();
							}
							if (in_array($activityID, $activities_array)) {
								$data['checked'] = TRUE;
							} else if ($details['active'] != 1) {
								continue;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									<?php echo $details['name']; ?>
									<span></span>
								</label>
							</div><?php
						}
					}
					?>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<h2>Slots</h2>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered availabilitycal_slots'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Start Time
						</th>
						<th>
							End Time
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if (count($slots) > 0) {
						$i = 0;
						foreach ($slots as $slot) {
							?><tr data-id="<?php echo $i; ?>">
								<td class="name">
									<?php
									$data = array(
										'name' => 'slots[' . $i . '][name]',
										'class' => 'form-control',
										'value' => set_value('slots[' . $i . '][name]', $this->crm_library->htmlspecialchars_decode($slot['name']), FALSE),
										'placeholder' => 'Session Name',
										'maxlength' => 100
									);
									echo form_input($data);
									?>
								</td>
								<td>
									<?php
									$startTimeH = 6;
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('slots[' . $i . '][startTimeH]', $options, set_value('slots[' . $i . '][startTimeH]', $this->crm_library->htmlspecialchars_decode($slot['startTimeH']), FALSE), 'class="form-control select2 startTimeH"');
									?><br /><?php
									$options = array();
									$m = 0;
									while ($m <= 59) {
										$m = sprintf("%02d",$m);
										if ($m % 5 == 0) {
											$options[$m] = $m;
										}
										$m++;
									}
									echo form_dropdown('slots[' . $i . '][startTimeM]', $options, set_value('slots[' . $i . '][startTimeM]', $this->crm_library->htmlspecialchars_decode($slot['startTimeM']), FALSE), 'class="form-control select2 startTimeM"');
									?>
								</td>
								<td>
									<?php
									$endTimeH = 7;
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('slots[' . $i . '][endTimeH]', $options, set_value('slots[' . $i . '][endTimeH]', $this->crm_library->htmlspecialchars_decode($slot['endTimeH']), FALSE), 'class="form-control select2 endTimeH"');
									echo '<br />';
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
									echo form_dropdown('slots[' . $i . '][endTimeM]', $options, set_value('slots[' . $i . '][endTimeM]', $this->crm_library->htmlspecialchars_decode($slot['endTimeM']), FALSE), 'class="form-control select2 endTimeM"');
									?>
								</td>
								<td>
									<a class='btn btn-danger btn-sm remove' href='#' title="Remove">
										<i class='far fa-trash'></i>
									</a>
									<a class='btn btn-success btn-sm add' href='#' title="Add Row">
										<i class='far fa-plus'></i>
									</a>
								</td>
							</tr><?php
							$i++;
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
