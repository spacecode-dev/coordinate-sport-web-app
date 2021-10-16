<?php
display_messages();
echo form_open_multipart($submit_to);
	$data = array(
		'deleted_items' => set_value('deleted_items'),
		'deleted_mileage' => set_value('deleted_mileage'),
		'deleted_expenses' => set_value('deleted_expenses')
	);
	echo form_hidden($data);
	?><div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-clock text-contrast'></i></span>
				<h3 class="card-label">Timesheet</h3>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="timesheet" data-home="<?php echo $timesheet_info->staff_postcode; ?>">
				<thead>
					<tr>
						<th>
							Day
						</th>
						<th>
							<?php echo $this->settings_library->get_label('customer'); ?>
						</th>
						<th>
							<?php echo $this->settings_library->get_label('brand'); ?>
						</th>
						<th>
							Activity
						</th>
						<th>
							Start Time
						</th>
						<th>
							End Time
						</th>
						<th>
							Extra Time
						</th>
						<?php
						if (!in_array($this->auth->user->department, array('fulltimecoach', 'coaching' , 'fulltimecoach'))) {
							?><th>
								Est. Travel Time
							</th><?php
						}
						?>
						<th>
							Status
						</th>
						<th>
							Approver
						</th>
						<th>
							Reason
						</th>
						<?php
						if ($mode == 'edit') {
							?><th></th><?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					if (count($timesheet_items) > 0) {
						foreach ($timesheet_items as $item_id => $item) {
							$data_attrs = array(
								'brand' => $item['brandID'],
								'id' => $item_id,
								'day' => $date_map[$item['date']],
								'extra_time' => time_to_seconds($item['extra_time'])/60,
								'status' => $item['status']
							);
							if (!empty($item['role'])) {
								$data_attrs['role'] = $item['role'];
							} else {
								$data_attrs['role'] = $item['reason'];
							}
							// postcode for travel time
							if (!in_array($this->auth->user->department, array('fulltimecoach', 'coaching' , 'fulltimecoach'))) {
								switch ($item['booking_type']) {
									case 'booking':
										$data_attrs['postcode'] = $item['lesson_postcode'];
										break;
									case 'event':
										$data_attrs['postcode'] = $item['booking_postcode'];
										break;
									default:
										$data_attrs['postcode'] = $item['main_postcode'];
										break;
								}
							}
							$data_attr_string = NULL;
							foreach ($data_attrs as $data_key => $data_value) {
								$data_attr_string .= ' data-' . $data_key . '="' . $data_value . '"';
							}
							?><tr class="existing"<?php echo $data_attr_string; ?>>
								<td class="name">
									<?php if (empty($item['lessonID'])) { ?><div class="readonly"><?php } ?>
										<span title="<?php echo mysql_to_uk_date($item['date']); ?>"><?php echo ucwords($date_map[$item['date']]); ?></span>
									<?php if (empty($item['lessonID'])) { ?></div><?php }
									if ($mode == 'edit' && empty($item['lessonID'])) {
										?><div class="edit" style="display:none;">
											<?php
											$options = $date_map;
											echo form_dropdown('edited_items[' . $item_id . '][date]', $options, set_value('edited_items[' . $item_id . '][date]', $item['date'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<?php if (empty($item['lessonID'])) { ?><div class="readonly"><?php } ?>
										<?php echo $item['venue']; ?>
									<?php if (empty($item['lessonID'])) { ?></div><?php }																		if ($mode == 'edit' && empty($item['lessonID'])) {
										?><div class="edit" style="display:none;">
											<?php
											$options = array();
											if ($venues->num_rows() > 0) {
												foreach ($venues->result() as $row) {
													$options[$row->orgID] = $row->name;
												}
											}
											echo form_dropdown('edited_items[' . $item_id . '][orgID]', $options, set_value('edited_items[' . $item_id . '][orgID]', $item['orgID'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<?php if (empty($item['lessonID'])) { ?><div class="readonly"><?php } ?>
										<span class="label label-inline" style="<?php echo label_style($item['brand_colour']); ?>"><?php echo $item['brand']; ?></span>
									<?php if (empty($item['lessonID'])) { ?></div><?php }
									if ($mode == 'edit' && empty($item['lessonID'])) {
										?><div class="edit" style="display:none;">
											<?php
											$options = array();
											if ($brands->num_rows() > 0) {
												foreach ($brands->result() as $row) {
													$options[$row->brandID] = $row->name;
												}
											}
											echo form_dropdown('edited_items[' . $item_id . '][brandID]', $options, set_value('edited_items[' . $item_id . '][brandID]', $item['brandID'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<?php if (empty($item['lessonID'])) {
										?><div class="readonly"><?php
									}
									if (!empty($item['activity'])) {
										echo $item['activity'];
									} else {
										echo 'Other/Unknown';
									}
									if (empty($item['lessonID'])) {
										?></div><?php
									}
									if ($mode == 'edit' && empty($item['lessonID'])) {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Other/Unknown'
											);
											if ($activities->num_rows() > 0) {
												foreach ($activities->result() as $row) {
													$options[$row->activityID] = $row->name;
												}
											}
											echo form_dropdown('edited_items[' . $item_id . '][activityID]', $options, set_value('edited_items[' . $item_id . '][activityID]', $item['activityID'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
										<?php
										if (!empty($item['original_start_time']) && $item['original_start_time'] != $item['start_time']) {
											echo '<span class="old_value">' . substr($item['original_start_time'], 0, 5) . '</span> ';
										}
										echo '<span class="start_time">' . substr($item['start_time'], 0, 5) . '<span>';
										?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$start_time_parts = explode(":", $item['start_time']);
											$options = array();
											$h = 6;
											while ($h <= 23) {
												$h = sprintf("%02d",$h);
												$options[$h] = $h;
												$h++;
											}
											echo form_dropdown('edited_items[' . $item_id . '][start_time][h]', $options, set_value('edited_items[' . $item_id . '][start_time][h]', $start_time_parts[0], FALSE), 'class="form-control select2 start_time_h"');
											echo '<br />';
											$options = array();
											$m = 0;
											while ($m <= 59) {
												$m = sprintf("%02d",$m);
												if ($m % 5 == 0) {
													$options[$m] = $m;
												}
												$m++;
											}
											echo form_dropdown('edited_items[' . $item_id . '][start_time][m]', $options, set_value('edited_items[' . $item_id . '][start_time][m]', $start_time_parts[1], FALSE), 'class="form-control select2 start_time_m"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
										<?php
										if (!empty($item['original_end_time']) && $item['original_end_time'] != $item['end_time']) {
											echo '<span class="old_value">' . substr($item['original_end_time'], 0, 5) . '</span> ';
										}
										echo '<span class="end_time">' . substr($item['end_time'], 0, 5) . '<span>';
										?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$end_time_parts = explode(":", $item['end_time']);
											$options = array();
											$h = 6;
											while ($h <= 23) {
												$h = sprintf("%02d",$h);
												$options[$h] = $h;
												$h++;
											}
											echo form_dropdown('edited_items[' . $item_id . '][end_time][h]', $options, set_value('edited_items[' . $item_id . '][end_time][h]', $end_time_parts[0], FALSE), 'class="form-control select2 end_time_h"');
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
											echo form_dropdown('edited_items[' . $item_id . '][end_time][m]', $options, set_value('edited_items[' . $item_id . '][end_time][m]', $end_time_parts[1], FALSE), 'class="form-control select2 end_time_m"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<?php echo substr($item['extra_time'], 0, 5); ?>
								</td>
								<?php
								if (!in_array($this->auth->user->department, array('fulltimecoach', 'coaching' , 'fulltimecoach'))) {
									?><td class="travel_time">Calculating...</td><?php
								}
								?>
								<td>
									<?php
									switch ($item['status']) {
										case 'unsubmitted':
										default:
											$label_colour = 'danger';
											break;
										case 'submitted':
											$label_colour = 'warning';
											break;
										case 'approved':
											$label_colour = 'success';
											break;
										case 'declined':
											$label_colour = 'danger';
											break;
									}
									?>
									<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($item['status']); ?></span>
								</td>
								<td>
									<div class="readonly">
										<?php echo $item['approver_first'] . ' ' . $item['approver_last']; ?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Select Approver'
											);
											if ($approvers->num_rows() > 0) {
												foreach ($approvers->result() as $row) {
													$options[$row->staffID] = $row->first . ' ' .$row->surname;
												}
											}
											echo form_dropdown('edited_items[' . $item_id . '][approverID]', $options, set_value('edited_items[' . $item_id . '][approverID]', $item['approverID'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
										<?php
										echo ucwords($item['reason']);
										if (!empty($item['reason_desc'])) {
											echo ': ' . $item['reason_desc'];
										}
										?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Select Reason',
												'travel' => 'Travel',
												'training' => 'Training',
												'marketing' => 'Marketing',
												'admin' => 'Admin',
												'other' => 'Other'
											);
											echo form_dropdown('edited_items[' . $item_id . '][reason]', $options, set_value('edited_items[' . $item_id . '][reason]', $item['reason'], FALSE), 'class="select2 form-control reason"');
											$data = array(
												'name' => 'edited_items[' . $item_id . '][reason_desc]',
												'class' => 'form-control',
												'value' => set_value('edited_items[' . $item_id . '][reason_desc]', $item['reason_desc']),
												'maxlength' => 200,
												'placeholder' => 'Reason Details',
											);
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<?php
								if ($mode == 'edit') {
									?><td>
										<?php
										$data = array(
											'edited_items[' . $item_id . '][edited]' => set_value('edited_items[' . $item_id . '][edited]')
										);
										echo form_hidden($data);
										?><a class='btn btn-warning btn-sm edit' href='#' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm cancel' href='#' title="Cancel" style="display:none;">
											<i class='far fa-ban'></i>
										</a>
										<?php
										if (empty($item['lessonID'])) {
											?><a class='btn btn-danger btn-sm remove' href='#' title="Remove">
												<i class='far fa-trash'></i>
											</a><?php
										}
										?>
									</td><?php
								}
								?>
							</tr><?php
						}
					}
					?>
					<?php
					if ($mode == 'edit' && count($new_items) > 0) {
						$i = 0;
						foreach ($new_items as $item) {
							?><tr class="new" data-brand="<?php echo set_value('new_items[' . $i . '][brandID]'); ?>" data-role="<?php echo set_value('new_items[' . $i . '][reason]'); ?>">
								<td class="name">
									<?php
									$options = array(
										'' => 'Select Day',
									);
									$options = array_merge($options, $date_map);
									echo form_dropdown('new_items[' . $i . '][date]', $options, set_value('new_items[' . $i . '][date]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select ' . $this->settings_library->get_label('customer')
									);
									if ($venues->num_rows() > 0) {
										foreach ($venues->result() as $row) {
											$options[$row->orgID] = $row->name;
										}
									}
									echo form_dropdown('new_items[' . $i . '][orgID]', $options, set_value('new_items[' . $i . '][orgID]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select ' . $this->settings_library->get_label('brand')
									);
									if ($brands->num_rows() > 0) {
										foreach ($brands->result() as $row) {
											$options[$row->brandID] = $row->name;
										}
									}
									echo form_dropdown('new_items[' . $i . '][brandID]', $options, set_value('new_items[' . $i . '][brandID]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Activity'
									);
									if ($activities->num_rows() > 0) {
										foreach ($activities->result() as $row) {
											$options[$row->activityID] = $row->name;
										}
									}
									echo form_dropdown('new_items[' . $i . '][activityID]', $options, set_value('new_items[' . $i . '][activityID]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('new_items[' . $i . '][start_time][h]', $options, set_value('new_items[' . $i . '][start_time][h]'), 'class="form-control select2 start_time_h"');
									echo '<br />';
									$options = array();
									$m = 0;
									while ($m <= 59) {
										$m = sprintf("%02d",$m);
										if ($m % 5 == 0) {
											$options[$m] = $m;
										}
										$m++;
									}
									echo form_dropdown('new_items[' . $i . '][start_time][m]', $options, set_value('new_items[' . $i . '][start_time][m]'), 'class="form-control select2 start_time_m"');
									?>
								</td>
								<td>
									<?php
									$options = array();
									$h = 6;
									while ($h <= 23) {
										$h = sprintf("%02d",$h);
										$options[$h] = $h;
										$h++;
									}
									echo form_dropdown('new_items[' . $i . '][end_time][h]', $options, set_value('new_items[' . $i . '][end_time][h]', 7, FALSE), 'class="form-control select2 end_time_h"');
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
									echo form_dropdown('new_items[' . $i . '][end_time][m]', $options, set_value('new_items[' . $i . '][end_time][m]'), 'class="form-control select2 end_time_m"');
									?>
								</td>
								<td></td>
								<?php
								if (!in_array($this->auth->user->department, array('fulltimecoach', 'coaching' , 'fulltimecoach'))) {
									// travel time
									?><td></td><?php
								}
								?>
								<td>
									<span class="label label-inline label-danger">Unsubmitted</span>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Approver'
									);
									if ($approvers->num_rows() > 0) {
										foreach ($approvers->result() as $row) {
											$options[$row->staffID] = $row->first . ' ' .$row->surname;
										}
									}
									echo form_dropdown('new_items[' . $i . '][approverID]', $options, set_value('new_items[' . $i . '][approverID]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Reason',
										'travel' => 'Travel',
										'training' => 'Training',
										'marketing' => 'Marketing',
										'admin' => 'Admin',
										'other' => 'Other'
									);
									echo form_dropdown('new_items[' . $i . '][reason]', $options, set_value('new_items[' . $i . '][reason]'), 'class="select2 form-control reason"');
									$data = array(
										'name' => 'new_items[' . $i . '][reason_desc]',
										'class' => 'form-control',
										'value' => set_value('new_items[' . $i . '][reason_desc]'),
										'maxlength' => 200,
										'placeholder' => 'Reason Details',
									);
									echo form_input($data);
									?>
								</td>
								<?php
								if ($mode == 'edit') {
									?><td>
										<a class='btn btn-danger btn-sm remove' href='#' title="Remove"<?php if ($i == 0) { echo ' style="display:none;"'; } ?>>
											<i class='far fa-trash'></i>
										</a>
										<a class='btn btn-success btn-sm add' href='#' title="Add Row" style="display:none;">
											<i class='far fa-plus'></i>
										</a>
									</td><?php
								}
								?>
							</tr><?php
							$i++;
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	
	<?php if($mileage_section == 1 && $timesheet_info->activate_mileage == 1){ 
	$dataArray = array();
	// Check no of day appear for fuel card
	$fuelCardDate = array();
	$carmileage = $carid = 0; 
	$totalmileage = $totalcost = 0.00;
	?>

	<div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-clock text-contrast'></i></span>
				<h3 class="card-label">Mileage</h3>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="mileage" data-home="<?php echo $timesheet_info->staff_postcode; ?>">
				<thead>
					<tr>
						<th>
							Date
						</th>
						<th>
							Start Location
						</th>
						<th>
							End Location
						</th>
						<th>
							Via Location
						</th>
						<th>
							Mode
						</th>
						<th>
							Distance
						</th>
						<th>
							Amount
						</th>
						<th>
							Status
						</th>
						<th>
							Approver
						</th>
						<th>
							Reason
						</th>
						<?php
						if ($mode == 'edit') {
							?><th></th><?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					$exclude_mileage = 0;
					if(array_key_exists("excluded_mileage", $mileage_setting) && $mileage_setting["excluded_mileage"] != "" && $mileage_activate_fuel_cards == 1){
						$exclude_mileage = $mileage_setting["excluded_mileage"];
					}else if(array_key_exists("excluded_mileage_without_fuel_card", $mileage_setting) && $mileage_setting["excluded_mileage_without_fuel_card"] != "" && $mileage_activate_fuel_cards != 1){
						$exclude_mileage = $mileage_setting["excluded_mileage_without_fuel_card"];
					}
					if (count($timesheet_mileage) > 0) {
						foreach ($timesheet_mileage as $item_id => $item) {
							$data_attrs = array(
								'id' => $item_id,
								'status' => $item['status'],
								'endcode' => $item["session_location"],
								'startcode' => $item["start_location"],
								'mode' => $item["mode"]
							);
							foreach($mode_price as $d => $p){
								$data_attrs["mode".$d] = $p;
								$data_attrs["mode-name".$d] = $default_mode[$d];
								if($default_mode[$d] == 'Car'){
									$data_attrs["carprice"] = $p;
									$carid = $d;
								}
							}
							$data_attrs["date"] = mysql_to_uk_date($item['date']);
							
							if(!in_array($data_attrs["date"], $fuelCardDate) && set_value('edited_items[' . $item_id . '][mode]', $item['mode'], FALSE) == $carid && $item['status'] != 'declined')
								$fuelCardDate[] = $data_attrs["date"];
							if (!empty($item['role'])) {
								
								$data_attrs['role'] = $item['role'];
							} else {
								$data_attrs['role'] = $item['reason'];
							}
							if(set_value('edited_items[' . $item_id . '][mode]', $item['mode'], FALSE) == $carid && $item['status'] != 'declined')
								$carmileage += set_value('edited_items[' . $item_id . '][total_mileage]', $item['total_mileage']);
							if($item['status'] != 'declined' && $item['total_mileage'] != 0){
								if(!in_array($data_attrs["date"], $dataArray)){
									$dataArray[] = $data_attrs["date"];
									$transport_mode = set_value('edited_items[' . $item_id . '][mode]', $item['mode'], FALSE);
									$totalmileage += set_value('edited_items[' . $item_id . '][total_mileage]', $item['total_mileage']) - $exclude_mileage;
									$totalcost += set_value('edited_items[' . $item_id . '][total_cost]', $item['total_cost']) - ($mode_price[$transport_mode] * $exclude_mileage / 100);
									
								}else{
									$totalmileage += set_value('edited_items[' . $item_id . '][total_mileage]', $item['total_mileage']);
									$totalcost += set_value('edited_items[' . $item_id . '][total_cost]', $item['total_cost']);
								}
							}
							$data_attr_string = NULL;
							foreach ($data_attrs as $data_key => $data_value) {
								$data_attr_string .= ' data-' . $data_key . '="' . $data_value . '"';
							}
							?><tr class="existing" <?php echo $data_attr_string; ?>>
								<td class="name">
									<div class="readonly">
										<span title="<?php echo mysql_to_uk_date($item['date']); ?>"><?php echo mysql_to_uk_date($item['date']); ?></span>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$data = array();
											$data["value"] = mysql_to_uk_date($item['date']);
											$data["class"] = "datepicker form-control";
											$data["name"] = 'edited_mileage[' . $item_id . '][date]';
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
									<?php
									$types = '';
									if($item["start_location"] == $postcode["work"] && $item['start_location'] != null){
										$types = '(Work)';
									}else if($item["start_location"] == $postcode["staff"] && $item['start_location'] != null){
										$types = '(Home)';
									}else if($item['start_location'] != null && !empty($item['lessonID'])){
										$org_address = $postcode["global"];
										if(in_array($item['start_location'], $org_address)){
											$types = '(Session)';
										}
									}
									?>
									<?php echo $item['start_location']." ".$types; ?>
									</div><?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$data = array();
											$data["value"] = $item['start_location'];
											$data["class"] = "form-control";
											$data["name"] = 'edited_mileage[' . $item_id . '][start_location]';
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
									<?php
									$types = '';
									if($item["session_location"] == $postcode["work"] && $item['session_location'] != null){
										$types = '(Work)';
									}else if($item["session_location"] == $postcode["staff"] && $item['session_location'] != null){
										$types = '(Home)';
									}else if($item['session_location'] != null && !empty($item['lessonID'])){
										$org_address = $postcode["global"];
										if(in_array($item['session_location'], $org_address)){
											$types = '(Session)';
										}
									}
									?>
									 <?php echo $item['session_location']." ".$types; ?>
									</div><?php 
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$data = array();
											$data["value"] = $item['session_location'];
											$data["class"] = "form-control";
											$data["name"] = 'edited_mileage[' . $item_id . '][session_location]';
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
									<?php echo $item['via_location']; ?>
									</div><?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$data = array();
											$data["value"] = $item['via_location'];
											$data["class"] = "form-control";
											$data["name"] = 'edited_mileage[' . $item_id . '][via_location]';
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<td>
									<div class="readonly">
									<?php echo isset($default_mode[$item["mode"]])?$default_mode[$item["mode"]]:''; ?>
									</div><?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Select Mode'
											);
											
											foreach ($default_mode as $d => $row) {
												$options[$d] = $row;
											}
											echo form_dropdown('edited_mileage[' . $item_id . '][mode]', $options, set_value('edited_items[' . $item_id . '][mode]', $item['mode'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
								</td>
								
								<td> <span class="travel_time"> <?php echo set_value('edited_items[' . $item_id . '][total_mileage]', number_format($item['total_mileage'],2))." mi" ?> </span> </td>
								
								<td> <span class="travel_cost"> <?php echo currency_symbol() .set_value('edited_items[' . $item_id . '][total_cost]', number_format($item['total_cost'],2)) ?> </span> </td>
								<td>
									<?php
									switch ($item['status']) {
										case 'unsubmitted':
										default:
											$label_colour = 'danger';
											break;
										case 'submitted':
											$label_colour = 'warning';
											break;
										case 'approved':
											$label_colour = 'success';
											break;
										case 'declined':
											$label_colour = 'danger';
											break;
									}
									?>
									<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($item['status']); ?></span>
								</td>
								<td>
									<div class="readonly">
										<?php echo $item['approver_first'] . ' ' . $item['approver_last']; ?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Select Approver'
											);
											if ($approvers->num_rows() > 0) {
												foreach ($approvers->result() as $row) {
													$options[$row->staffID] = $row->first . ' ' .$row->surname;
												}
											}
											echo form_dropdown('edited_mileage[' . $item_id . '][approverID]', $options, set_value('edited_mileage[' . $item_id . '][approverID]', $item['approverID'], FALSE), 'class="select2 form-control"');
											?>
										</div><?php
									}
									?>
									
								</td>
								<td>
									<div class="readonly">
										<?php
										echo ucwords($item['reason']);
										if (!empty($item['reason_desc'])) {
											echo ': ' . $item['reason_desc'];
										}
										?>
									</div>
									<?php
									if ($mode == 'edit') {
										?><div class="edit" style="display:none;">
											<?php
											$options = array(
												'' => 'Select Reason',
												'travel' => 'Travel',
												'training' => 'Training',
												'marketing' => 'Marketing',
												'admin' => 'Admin',
												'other' => 'Other'
											);
											echo form_dropdown('edited_mileage[' . $item_id . '][reason]', $options, set_value('edited_mileage[' . $item_id . '][reason]', $item['reason'], FALSE), 'class="select2 form-control reason"');
											$data = array(
												'name' => 'edited_mileage[' . $item_id . '][reason_desc]',
												'class' => 'form-control',
												'value' => set_value('edited_mileage[' . $item_id . '][reason_desc]', $item['reason_desc']),
												'maxlength' => 200,
												'placeholder' => 'Reason Details',
											);
											echo form_input($data);
											?>
										</div><?php
									}
									?>
								</td>
								<?php
								if ($mode == 'edit') {
									?><td>
										<?php
										$data = array(
											'edited_mileage[' . $item_id . '][edited]' => set_value('edited_mileage[' . $item_id . '][edited]')
										);
										echo form_hidden($data);
										?><a class='btn btn-warning btn-sm edit' href='#' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm cancel' href='#' title="Cancel" style="display:none;">
											<i class='far fa-ban'></i>
										</a>
										<?php
										if (empty($item['lessonID'])) {
											?><a class='btn btn-danger btn-sm remove' href='#' title="Remove">
												<i class='far fa-trash'></i>
											</a><?php
										}
										?>
									</td><?php
								}
								?>
							</tr><?php
						}
					$total_exclude = ($exclude_mileage * count($dataArray));
					
					?>
					<tr>
						<td> <strong>Total</strong> </td>
						<td> </td>
						<td> </td>
						<td> </td>
						<td> </td>
						<td class="total_travel"> <?php echo number_format($totalmileage,2) ?> mi <?php if($total_exclude != 0){echo "<Br />(Exc ".$total_exclude." mi)";} ?></td>
						<td class="total_cost"> <?php echo currency_symbol() .number_format($totalcost,2) ?> </td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php
					}
					if ($mode == 'edit' && count($new_mileage) > 0) {
						$i = 0;
						foreach ($new_mileage as $mileage) { ?>
							<tr class="new" data-role="<?php echo set_value('new_mileage[' . $i . '][reason]'); ?>">
								<td class="name">
									<?php
									$data = array();
									$data["name"] = 'new_mileage[' . $i . '][date]';
									$data["value"] =  set_value('new_mileage[' . $i . '][date]');
									$data["class"] = "datepicker form-control";
									echo form_input($data);
									?>
								</td>
								<td>
									<?php
									$data = array();
									$data["name"] = 'new_mileage[' . $i . '][start_location]';
									$data["value"] =  set_value('new_mileage[' . $i . '][start_location]');
									$data["class"] = "form-control";
									echo form_input($data);
									?>
								</td>
								<td>
									<?php
									$data = array();
									$data["name"] = 'new_mileage[' . $i . '][session_location]';
									$data["value"] =  set_value('new_mileage[' . $i . '][session_location]');
									$data["class"] = "form-control";
									echo form_input($data);
									?>
								</td>
								<td>
									<?php
									$data = array();
									$data["name"] = 'new_mileage[' . $i . '][via_location]';
									$data["value"] =  set_value('new_mileage[' . $i . '][via_location]');
									$data["class"] = "form-control";
									echo form_input($data);
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Mode'
									);
									
									foreach ($default_mode as $d => $row) {
										$options[$d] = $row;
									}
									echo form_dropdown('new_mileage[' . $i . '][mode]', $options, set_value('new_mileage[' . $i . '][mode]'), 'class="select2 form-control"');
									?>
								</td>
								<td></td>
								<td> </td>
								<td>
									<span class="label label-inline label-danger">Unsubmitted</span>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Approver'
									);
									if ($approvers->num_rows() > 0) {
										foreach ($approvers->result() as $row) {
											$options[$row->staffID] = $row->first . ' ' .$row->surname;
										}
									}
									echo form_dropdown('new_mileage[' . $i . '][approverID]', $options, set_value('new_mileage[' . $i . '][approverID]'), 'class="select2 form-control"');
									?>
								</td>
								<td>
									<?php
									$options = array(
										'' => 'Select Reason',
										'travel' => 'Travel',
										'training' => 'Training',
										'marketing' => 'Marketing',
										'admin' => 'Admin',
										'other' => 'Other'
									);
									echo form_dropdown('new_mileage[' . $i . '][reason]', $options, set_value('new_mileage[' . $i . '][reason]'), 'class="select2 form-control reason"');
									$data = array(
										'name' => 'new_mileage[' . $i . '][reason_desc]',
										'class' => 'form-control',
										'value' => set_value('new_mileage[' . $i . '][reason_desc]'),
										'maxlength' => 200,
										'placeholder' => 'Reason Details',
									);
									echo form_input($data);
									?>
								</td>
								<?php
								if ($mode == 'edit') {
									?><td>
										<a class='btn btn-danger btn-sm remove' href='#' title="Remove"<?php if ($i == 0) { echo ' style="display:none;"'; } ?>>
											<i class='far fa-trash'></i>
										</a>
										<a class='btn btn-success btn-sm add' href='#' title="Add Row" style="display:none;">
											<i class='far fa-plus'></i>
										</a>
									</td><?php
								}
								?>
							</tr>
							<?php
							$i++;
						}
					}
					?>
					
				</tbody>
			</table>
		</div>
	</div>

		<?php if($mileage_setting["mileage_activate_fuel_cards"] == 1 && $mileage_activate_fuel_cards == 1){ ?>
		<div class='card card-custom'>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-clock text-contrast'></i></span>
					<h3 class="card-label">Fuel Card Mileage</h3>
				</div>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered' id="fuel_card_mileage" data-home="<?php echo $timesheet_info->staff_postcode; ?>">
					<thead>
						<tr>
							<th>
								Start Mileage
							</th>
							<th>
								End Mileage
							</th>
							<th>
								Total Personal Mileage
							</th>
							<th>
								Total Business Mileage
							</th>
							<th>
								Total Deduction
							</th>
							<th>
								Attachment
							</th>
							<th>
								Status
							</th>
							<th>
								Approver
							</th>
							<th>
								Reason
							</th>
							<?php
							if ($mode == 'edit') {
								?><th></th><?php
							}
							?>
						</tr>
					</thead>
					<tbody>
						
						<?php 
						$i = 0;
						$start_mileage = 0.00;
						$end_mileage = 0.00;
						$status = "unsubmitted";
						$approver_first = $approver_last = $approverID = $reason_desc = $reason = $receipt_path = "";
						$item_id = 0;
						if(count($timesheets_fuel_card) > 0){
							foreach($timesheets_fuel_card as $item_id => $item){
								$start_mileage = $item["start_mileage"];
								$end_mileage = $item["end_mileage"];
								$status = $item["status"];
								$approver_first = $item["approver_first"];
								$approver_last = $item["approver_last"];
								$approverID = $item["approverID"];
								$reason = $item["reason"];
								$reason_desc = $item["reason_desc"];
								$item_ids = $item_id;
								$receipt_path = $item["receipt_path"];
							}
						}
						?>
						<tr class="existing">
							<td class="name ">
								<?php if($start_mileage == 0){ ?> <div class="readonly"> <?php } ?>
								<span class="start_mileage"><?php echo number_format($start_mileage,2) ?></span>
								<?php if($start_mileage == 0){ ?> </div> <?php } ?>
								<?php
								if ($mode == 'edit' && $start_mileage == 0) {
									?><div class="edit" style="display:none;">
										<?php
										$data = array();
										$data["value"] = "";
										$data["class"] = "form-control";
										$data["name"] = 'edited_fuel_card[' . $item_id . '][start_mileage]';
										echo form_input($data);
										?>
									</div><?php
								}
								?>
							</td>
							<td>
								<div class="readonly">	
								<span class="end_mileage"><?php echo number_format($end_mileage,2) ?></span>
								</div>
								<?php
								if ($mode == 'edit') {
									?><div class="edit" style="display:none;">
										<?php
										$data = array();
										$data["value"] =$end_mileage;
										$data["class"] = "form-control";
										$data["name"] = 'edited_fuel_card[' . $item_id . '][end_mileage]';
										echo form_input($data);
										?>
									</div><?php
								}
								?>
							</td>
							<td class="remain_mileage">
								<?php echo ($end_mileage != 0)?number_format(($end_mileage - $start_mileage - $carmileage + (count($fuelCardDate) * $exclude_mileage)),2):"0.00"; ?>
							</td>
							<td class="remain_mileage">
								<?php echo ($end_mileage != 0)?number_format(($end_mileage + $totalmileage - $start_mileage),2):"0.00"; ?>
							</td>
							<td class="remain_amount">
								<?php echo currency_symbol() .(($end_mileage != 0)?number_format(((($end_mileage - $start_mileage - $carmileage + (count($fuelCardDate) * $exclude_mileage)) * $mode_price[$carid])/100),2):"0.00"); ?>
							</td>
							<td>
								<div class="readonly">	
								<?php
								if (!empty($receipt_path)) {
									echo anchor('attachment/fuelcard/' . $receipt_path, 'View Attachment', 'target="_blank"');
								} else {
									echo 'No Attachment';
								}
								?>
								</div>
								<?php
								if ($mode == 'edit') {
								?>
								<div class="edit" style="display:none;"><?php
									$data = array(
										'name' => 'edited_fuel_card[' . $i . '][receipt]',
										'id' => 'edited_fuel_card_' . $i . '_receipt',
										'class' => 'custom-file-input'
									);
									?>
									<div class="custom-file">
										<?php echo form_upload($data); ?>
										<label class="custom-file-label" for="edited_fuel_card<?php echo $i; ?>_receipt">Choose file</label>
									</div>
								</div>
								<?php } ?>
							</td>
							<td>
								<?php
								switch ($status) {
									case 'unsubmitted':
									default:
										$label_colour = 'danger';
										break;
									case 'submitted':
										$label_colour = 'warning';
										break;
									case 'approved':
										$label_colour = 'success';
										break;
									case 'declined':
										$label_colour = 'danger';
										break;
								}
								?>
								<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($status); ?></span>
							</td>
							<td>
								<div class="readonly">
									<?php echo $approver_first . ' ' . $approver_last; ?>
								</div>
								<?php
								if ($mode == 'edit') {
									?><div class="edit" style="display:none;">
										<?php
										$options = array(
											'' => 'Select Approver'
										);
										if ($approvers->num_rows() > 0) {
											foreach ($approvers->result() as $row) {
												$options[$row->staffID] = $row->first . ' ' .$row->surname;
											}
										}
										echo form_dropdown('edited_fuel_card[' . $item_id . '][approverID]', $options, set_value('edited_mileage[' . $item_id . '][approverID]', $approverID, FALSE), 'class="select2 form-control"');
										?>
									</div><?php
								}
								?>
								
							</td>
							<td>
								<div class="readonly">
									<?php
									echo ucwords($reason);
									if (!empty($reason_desc)) {
										echo ': ' . $reason_desc;
									}
									?>
								</div>
								<?php
								if ($mode == 'edit') {
									?><div class="edit" style="display:none;">
										<?php
										if($reason == NULL)
											$reason = 'travel';
										$options = array(
											'' => 'Select Reason',
											'travel' => 'Travel',
											'training' => 'Training',
											'marketing' => 'Marketing',
											'admin' => 'Admin',
											'other' => 'Other'
										);
										echo form_dropdown('edited_fuel_card[' . $item_id . '][reason]', $options, set_value('edited_fuel_card[' . $item_id . '][reason]', $reason, FALSE), 'class="select2 form-control reason"');
										$data = array(
											'name' => 'edited_fuel_card[' . $item_id . '][reason_desc]',
											'class' => 'form-control',
											'value' => set_value('edited_fuel_card[' . $item_id . '][reason_desc]', $reason_desc),
											'maxlength' => 200,
											'placeholder' => 'Reason Details',
										);
										echo form_input($data);
										?>
									</div><?php
								}
								?>
							</td>
							<?php
							if ($mode == 'edit') {
								?><td>
									<?php
									$data = array(
										'edited_fuel_card[' . $item_id . '][edited]' => set_value('edited_fuel_card[' . $item_id . '][edited]')
									);
									echo form_hidden($data);
									?><a class='btn btn-warning btn-sm edit' href='#' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm cancel' href='#' title="Cancel" style="display:none;">
										<i class='far fa-ban'></i>
									</a>
								</td><?php
							}
							?>
						</tr>
							
					</tbody>
				</table>
			</div>
		</div>
		<?php } ?>
	
	<?php } ?>
	
	<?php
	if ($this->auth->has_features('expenses')) {
		?><div class='card card-custom'>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
					<h3 class="card-label">Out of Pocket Expenses</h3>
				</div>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered' id="expenses">
					<thead>
						<tr>
							<th>
								Day
							</th>
							<th>
								<?php echo $this->settings_library->get_label('customer'); ?>
							</th>
							<th>
								<?php echo $this->settings_library->get_label('brand'); ?>
							</th>
							<th>
								Item
							</th>
							<th>
								Amount
							</th>
							<th>
								Receipt
							</th>
							<th>
								Status
							</th>
							<th>
								Approver
							</th>
							<th>
								Reason
							</th>
							<?php
							if ($mode == 'edit') {
								?><th></th><?php
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						if (count($timesheet_expenses) > 0) {
							foreach ($timesheet_expenses as $item_id => $item) {
								?><tr class="existing" data-brand="<?php echo $item['brandID']; ?>" data-status="<?php echo $item['status']; ?>" data-id="<?php echo $item_id; ?>">
									<td class="name">
										<div class="readonly">
											<span title="<?php echo mysql_to_uk_date($item['date']); ?>"><?php echo ucwords($date_map[$item['date']]); ?></span>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$options = $date_map;
												echo form_dropdown('edited_expenses[' . $item_id . '][date]', $options, set_value('edited_expenses[' . $item_id . '][date]', $item['date'], FALSE), 'class="select2 form-control"');
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<div class="readonly">
											<?php echo $item['venue']; ?>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$options = array();
												if ($venues->num_rows() > 0) {
													foreach ($venues->result() as $row) {
														$options[$row->orgID] = $row->name;
													}
												}
												echo form_dropdown('edited_expenses[' . $item_id . '][orgID]', $options, set_value('edited_expenses[' . $item_id . '][orgID]', $item['orgID'], FALSE), 'class="select2 form-control"');
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<div class="readonly">
											<span class="label label-inline" style="<?php echo label_style($item['brand_colour']); ?>"><?php echo $item['brand']; ?></span>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$options = array();
												if ($brands->num_rows() > 0) {
													foreach ($brands->result() as $row) {
														$options[$row->brandID] = $row->name;
													}
												}
												echo form_dropdown('edited_expenses[' . $item_id . '][brandID]', $options, set_value('edited_expenses[' . $item_id . '][brandID]', $item['brandID'], FALSE), 'class="select2 form-control"');
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<div class="readonly">
											<?php echo $item['item']; ?>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$data = array(
													'name' => 'edited_expenses[' . $item_id . '][item]',
													'class' => 'form-control',
													'value' => set_value('edited_expenses[' . $item_id . '][item]', $item['item']),
													'maxlength' => 100,
													'placeholder' => 'Item',
												);
												echo form_input($data);
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<div class="readonly">
											<?php echo currency_symbol() . number_format($item['amount'], 2); ?>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$data = array(
													'name' => 'edited_expenses[' . $item_id . '][amount]',
													'class' => 'form-control',
													'value' => set_value('edited_expenses[' . $item_id . '][amount]', $item['amount']),
													'maxlength' => 10,
													'placeholder' => 'Amount',
												);
												echo form_input($data);
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<?php
										if (!empty($item['receipt_path'])) {
											echo anchor('attachment/expense/' . $item['receipt_path'], 'View Receipt', 'target="_blank"');
										} else {
											echo 'No receipt';
										}
										?>
									</td>
									<td>
										<?php
										switch ($item['status']) {
											case 'unsubmitted':
											default:
												$label_colour = 'danger';
												break;
											case 'submitted':
												$label_colour = 'warning';
												break;
											case 'approved':
												$label_colour = 'success';
												break;
											case 'declined':
												$label_colour = 'danger';
												break;
										}
										?>
										<span class="label label-inline label-<?php echo $label_colour; ?>"><?php echo ucwords($item['status']); ?></span>
									</td>
									<td>
										<div class="readonly">
											<?php echo $item['approver_first'] . ' ' . $item['approver_last']; ?>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$options = array(
													'' => 'Select Approver'
												);
												if ($approvers->num_rows() > 0) {
													foreach ($approvers->result() as $row) {
														$options[$row->staffID] = $row->first . ' ' .$row->surname;
													}
												}
												echo form_dropdown('edited_expenses[' . $item_id . '][approverID]', $options, set_value('edited_expenses[' . $item_id . '][approverID]', $item['approverID'], FALSE), 'class="select2 form-control"');
												?>
											</div><?php
										}
										?>
									</td>
									<td>
										<div class="readonly">
											<?php
											echo ucwords($item['reason']);
											if (!empty($item['reason_desc'])) {
												echo ': ' . $item['reason_desc'];
											}
											?>
										</div>
										<?php
										if ($mode == 'edit') {
											?><div class="edit" style="display:none;">
												<?php
												$options = array(
													'' => 'Select Reason',
													'travel' => 'Travel',
													'training' => 'Training',
													'marketing' => 'Marketing',
													'admin' => 'Admin',
													'other' => 'Other'
												);
												echo form_dropdown('edited_expenses[' . $item_id . '][reason]', $options, set_value('edited_expenses[' . $item_id . '][reason]', $item['reason'], FALSE), 'class="select2 form-control"');
												$data = array(
													'name' => 'edited_expenses[' . $item_id . '][reason_desc]',
													'class' => 'form-control',
													'value' => set_value('edited_expenses[' . $item_id . '][reason_desc]', $item['reason_desc']),
													'maxlength' => 200,
													'placeholder' => 'Reason Details',
												);
												echo form_input($data);
												?>
											</div><?php
										}
										?>
									</td>
									<?php
									if ($mode == 'edit') {
										?><td><?php
											$data = array(
												'edited_expenses[' . $item_id . '][edited]' => set_value('edited_expenses[' . $item_id . '][edited]')
											);
											echo form_hidden($data);
											?><a class='btn btn-warning btn-sm edit' href='#' title="Edit">
												<i class='far fa-pencil'></i>
											</a>
											<a class='btn btn-danger btn-sm cancel' href='#' title="Cancel" style="display:none;">
												<i class='far fa-ban'></i>
											</a>
											<?php
											if (empty($item['lessonID'])) {
												?><a class='btn btn-danger btn-sm remove' href='#' title="Remove">
													<i class='far fa-trash'></i>
												</a><?php
											}
											?>
										</td><?php
									}
									?>
								</tr><?php
							}
						}
						if ($mode == 'edit' && count($new_expenses) > 0) {
							$i = 0;
							foreach ($new_expenses as $item) {
								?><tr class="new">
									<td class="name">
										<?php
										$options = array(
											'' => 'Select Day',
										);
										$options = array_merge($options, $date_map);
										echo form_dropdown('new_expenses[' . $i . '][date]', $options, set_value('new_expenses[' . $i . '][date]'), 'class="select2 form-control"');
										?>
									</td>
									<td>
										<?php
										$options = array(
											'' => 'Select ' . $this->settings_library->get_label('customer')
										);
										if ($venues->num_rows() > 0) {
											foreach ($venues->result() as $row) {
												$options[$row->orgID] = $row->name;
											}
										}
										echo form_dropdown('new_expenses[' . $i . '][orgID]', $options, set_value('new_expenses[' . $i . '][orgID]'), 'class="select2 form-control"');
										?>
									</td>
									<td>
										<?php
										$options = array(
											'' => 'Select ' . $this->settings_library->get_label('brand')
										);
										if ($brands->num_rows() > 0) {
											foreach ($brands->result() as $row) {
												$options[$row->brandID] = $row->name;
											}
										}
										echo form_dropdown('new_expenses[' . $i . '][brandID]', $options, set_value('new_expenses[' . $i . '][brandID]'), 'class="select2 form-control"');
										?>
									</td>
									<td>
										<?php
										$data = array(
											'name' => 'new_expenses[' . $i . '][item]',
											'class' => 'form-control',
											'value' => set_value('new_expenses[' . $i . '][item]'),
											'maxlength' => 100,
											'placeholder' => 'Item',
										);
										echo form_input($data);
										?>
									</td>
									<td>
										<?php
										$data = array(
											'name' => 'new_expenses[' . $i . '][amount]',
											'class' => 'form-control',
											'value' => set_value('new_expenses[' . $i . '][amount]'),
											'maxlength' => 10,
											'placeholder' => 'Amount',
										);
										echo form_input($data);
										?>
									</td>
									<td>
										<?php
										$data = array(
											'name' => 'new_expenses[' . $i . '][receipt]',
											'id' => 'new_expenses_' . $i . '_receipt',
											'class' => 'custom-file-input'
										);
										?>
										<div class="custom-file">
											<?php echo form_upload($data); ?>
											<label class="custom-file-label" for="new_expenses_<?php echo $i; ?>_receipt">Choose file</label>
										</div>
									</td>
									<td>
										<span class="label label-inline label-danger">Unsubmitted</span>
									</td>
									<td>
										<?php
										$options = array(
											'' => 'Select Approver'
										);
										if ($approvers->num_rows() > 0) {
											foreach ($approvers->result() as $row) {
												$options[$row->staffID] = $row->first . ' ' .$row->surname;
											}
										}
										echo form_dropdown('new_expenses[' . $i . '][approverID]', $options, set_value('new_expenses[' . $i . '][approverID]'), 'class="select2 form-control"');
										?>
									</td>
									<td>
										<?php
										$options = array(
											'' => 'Select Reason',
											'travel' => 'Travel',
											'training' => 'Training',
											'marketing' => 'Marketing',
											'admin' => 'Admin',
											'other' => 'Other'
										);
										echo form_dropdown('new_expenses[' . $i . '][reason]', $options, set_value('new_expenses[' . $i . '][reason]'), 'class="select2 form-control"');
										$data = array(
											'name' => 'new_expenses[' . $i . '][reason_desc]',
											'class' => 'form-control',
											'value' => set_value('new_expenses[' . $i . '][reason_desc]'),
											'maxlength' => 200,
											'placeholder' => 'Reason Details',
										);
										echo form_input($data);
										?>
									</td>
									<?php
									if ($mode == 'edit') {
										?><td>
											<a class='btn btn-danger btn-sm remove' href='#' title="Remove"<?php if ($i == 0) { echo ' style="display:none;"'; } ?>>
												<i class='far fa-trash'></i>
											</a>
											<a class='btn btn-success btn-sm add' href='#' title="Add Row" style="display:none;">
												<i class='far fa-plus'></i>
											</a>
											<a class='btn btn-danger btn-sm clear' href='#' title="Clear"<?php if ($i > 0) { echo ' style="display:none;"'; } ?>>
												<i class='far fa-ban'></i>
											</a>
										</td><?php
									}
									?>
								</tr><?php
								$i++;
							}
						}
						?>
					</tbody>
				</table>
			</div>
		</div><?php
	}
	if($this->auth->user->department == 'directors'){
	?>
	<div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-clock text-contrast'></i></span>
				<h3 class="card-label">Totals</h3>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="totals">
				<thead>
					<th></th>
					<?php
					if ($brands->num_rows() > 0) {
						foreach ($brands->result() as $row) {
							echo '<th><span class="label label-inline"  style="' . label_style($row->colour) . '">' . $row->name . '</span></th>';
						}
					}
					?>
					<th>Total</th>
				</thead>
				<tbody>
					<?php
					if (count($main_roles) > 0) {
						foreach ($main_roles as $key => $label) {
							echo '<tr data-role="' . $key . '">';
								echo '<th>' . $label . '</th>';
								if ($brands->num_rows() > 0) {
									foreach ($brands->result() as $row) {
										echo '<td data-brand="' . $row->brandID . '">-</td>';
									}
								}
								echo '<td class="total">00:00</td>';
							echo '</tr>';
						}
					}
					if (count($additional_roles) > 0) {
						foreach ($additional_roles as $key => $label) {
							echo '<tr data-role="' . $key . '">';
							echo '<th>' . $label . '</th>';
							if ($brands->num_rows() > 0) {
								foreach ($brands->result() as $row) {
									echo '<td data-brand="' . $row->brandID . '">-</td>';
								}
							}
							echo '<td class="total">00:00</td>';
							echo '</tr>';
						}
					}
					?>
					<tr class="totals">
						<th>Total</th>
						<?php
						if ($brands->num_rows() > 0) {
							foreach ($brands->result() as $row) {
								echo '<td data-brand="' . $row->brandID . '">00:00</td>';
							}
						}
						?>
						<td class="total_time"><strong>00:00</strong></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	}
	if ($mode == 'edit') {
		?><div class='form-actions d-flex justify-content-between'>
			<div class="btn-group">
				<button class='btn btn-primary btn-submit' type="submit">
					Submit
				</button>
				<input class='btn btn-light' type="submit" name="save" value="Save Draft" />
			</div>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div><?php
	}
echo form_close();
