<?php
display_messages();
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	if($ajaxFlag == NULL)
		$this->load->view('bookings/tabs.php', $data);
}$form_classes = 'card card-custom card-search';
if($ajaxFlag == NULL){
	if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
	echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
		<input type="hidden" id="modalflag" value="<?php echo $modalflag ?>" />
		<div class="card-header">
			<div class="card-title">
				<h3 class="card-label">Search</h3>
			</div>
			<div class="card-toolbar">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_day">Day</label></strong>
					</p>
					<?php
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
					echo form_dropdown('search_day', $options, $search_fields['day'], 'id="field_day" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_activity_id">Activity</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($activities->num_rows() > 0) {
						foreach ($activities->result() as $row) {
							$options[$row->activityID] = $row->name;
						}
					}
					$options['other'] = 'Other';
					echo form_dropdown('search_activity_id', $options, $search_fields['activity_id'], 'id="field_activity_id" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_group">Group/Class</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);

					// fetch session groups
					$options = array_merge($options, $this->crm_library->lesson_groups());

					echo form_dropdown('search_group', $options, $search_fields['group'], 'id="field_group" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_type_id">Session Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
					);
					if ($lesson_types->num_rows() > 0) {
						foreach ($lesson_types->result() as $row) {
							$options[$row->typeID] = $row->name;
						}
					}
					$options['other'] = 'Other';
					echo form_dropdown('search_type_id', $options, $search_fields['type_id'], 'id="field_type_id" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_staff_id">Staff</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($staff_list->num_rows() > 0) {
						foreach ($staff_list->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' .$row->surname;
						}
					}
					echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
					?>
				</div>
			</div>
		</div>
		<div class='card-footer'>
			<div class="d-flex justify-content-between">
				<button class='btn btn-primary btn-submit' type="submit">
					<i class='far fa-search'></i> Search
				</button>
				<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
					Cancel
				</a>
			</div>
		</div>
		<?php echo form_hidden('search', 'true'); ?>
	<?php echo form_close(); ?>
	<div id="results"></div>
	<?php
	if (!empty($block_info->staffing_notes) || !empty($org_info->staffing_notes)) {
		?><div class="alert alert-info">
			<p><?php
				echo nl2br(trim($org_info->staffing_notes . "\n" . $block_info->staffing_notes));
			?></p>
		</div><?php
	}
	?>
	<div class="card card-custom card-search select-block">
		<div class="card-header">
			<div class="card-title">
				<h5 class="card-label">Select Blocks</h5>
			</div>
		</div>
		<div class="card-body">
			<a href='<?php echo site_url('bookings/blocks/' . $bookingID . '/new'); ?>' style="float:right; margin-top: 5px; color:#80808F; font-weight: 500;" class="nav-link">
				Add Block
			</a>
			<ul class="nav nav-tabs nav-bold nav-tabs-line navbuttons fixed-1" style="width:87%">
				<?php
				if ($blocks->num_rows() > 0) {
					$past_blocks = array();
					foreach ($blocks->result() as $block) {
						// if block in past, show later
						if (strtotime($block->endDate) < strtotime(date('Y-m-d'))) {
							$past_blocks[] = $block;
							continue;
						}
						$block_dates = mysql_to_uk_date($block->startDate);
						if (!empty($block->endDate) && strtotime($block->endDate) > strtotime($block->startDate)) {
							$block_dates .= '-' . mysql_to_uk_date($block->endDate);
						}
						?><li title="<?php echo $block_dates; ?>" class="nav-item">
							<a href='<?php echo site_url('bookings/sessions/' . $bookingID . '/' . $block->blockID); ?>' class="nav-link<?php if ($blockID == $block->blockID) { echo " active"; } ?>">
								<?php
								echo $block->name;
								if (!empty($block->org_name)) {
									echo ' (' . $block->org_name . ')';
								}
								?>
							</a>
						</li><?php
					}
					// show past blocks
					if (count($past_blocks) > 0) {
						foreach ($past_blocks as $block) {
							$block_dates = mysql_to_uk_date($block->startDate);
							if (!empty($block->endDate) && strtotime($block->endDate) > strtotime($block->startDate)) {
								$block_dates .= '-' . mysql_to_uk_date($block->endDate);
							}
							?><li title="<?php echo $block_dates; ?>" class="nav-item">
								<a href='<?php echo site_url('bookings/sessions/' . $bookingID . '/' . $block->blockID); ?>' class="nav-link<?php if ($blockID == $block->blockID) { echo " active"; } ?>">
									Past: <?php
									echo $block->name;
									if (!empty($block->org_name)) {
										echo ' (' . $block->org_name . ')';
									}
									?>
								</a>
							</li><?php
						}
					}
				}
				?>

			</ul>
		</div>
	</div>
<?php
echo $this->pagination_library->display($page_base);
}
$tooltips = array();
if($ajaxFlag == NULL){
	echo form_open('sessions/bulk/' . $blockID, 'id="lessons"');

	// set hidden fields for new session form
	$hidden_fields = array(
		'addressID' => $block_info->addressID,
		'charge' => 'default',
		'bookingID' => $bookingID,
		'blockID' => $blockID
	);
	echo form_hidden($hidden_fields);
}
	if($ajaxFlag == NULL){
		echo "<div id='ajaxData'>";
	}
	$label_text = '<label><em> * </em></label>';
	?>

	<div class='card card-custom card-search'>
		<div class="card-header">
			<div class="card-title">
				<h5 class="card-label">Session List</h5>
			</div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes' id="sessiontable">
				<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
						<th>
							Day <?php echo $label_text ?>
						</th>
						<th>
							Start Time <?php echo $label_text ?>
						</th>
						<th>
							End Time <?php echo $label_text ?>
						</th>
						<th>
							Group/Class
						</th>
						<th>
							Class Size
						</th>
						<th>
							Location at Delivery Address
						</th>
						<th>
							Activity <?php echo $label_text ?>
						</th>
						<th>
							Session Type
						</th>
						<?php if ($type == 'event' || $booking_info->project == 1) {
							?><th>
								<?php echo $this->settings_library->get_label('participants'); ?>
							</th><?php
						} ?>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ($lessons->num_rows() > 0) {

						foreach ($lessons->result() as $row) {
							$offer_accept_status = $row->offer_accept_status;
							switch ($offer_accept_status) {
								case 'offering':
									$offer_accept_status = 'offered';
									break;
								case 'exhausted':
									$offer_accept_status = 'declined';
							}
							// build tooltip
							$tooltip = array();
							$tooltip[] = '<strong>Day:</strong> ' . ucwords($row->day);
							$tooltip[] = '<strong>Time:</strong> ' . substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0 ,5);
							$dates = '<strong>Date(s):</strong> ';
							if (!empty($row->startDate)) {
								$dates .=  mysql_to_uk_date($row->startDate);
								if (!empty($row->endDate) && strtotime($row->endDate) > strtotime($row->startDate)) {
									$dates .= '-' . mysql_to_uk_date($row->endDate);
								}
							} else {
								$dates .= mysql_to_uk_date($block_info->startDate);
								if (!empty($block_info->endDate) && strtotime($block_info->endDate) > strtotime($block_info->startDate)) {
									$dates .= '-' . mysql_to_uk_date($block_info->endDate);
								}
							}
							$tooltip[] = $dates;
							$address_bits = array();
							if (!empty($row->address1)) {
								$address_bits[] = $row->address1;
							}
							if (!empty($row->address2)) {
								$address_bits[] = $row->address2;
							}
							if (!empty($row->address3)) {
								$address_bits[] = $row->address3;
							}
							if (!empty($row->town)) {
								$address_bits[] = $row->town;
							}
							if (!empty($row->county)) {
								$address_bits[] = $row->county;
							}
							if (!empty($row->postcode)) {
								$address_bits[] = $row->postcode;
							}
							if (count($address_bits) > 0) {
								$tooltip[] = '<strong>Address:</strong> ' . implode(", ", $address_bits);
							}
							if (!empty($row->location)) {
								$tooltip[] = '<strong>Location:</strong> ' . $row->location;
							}
							if (!empty($row->type)) {
								$tooltip[] = '<strong>Type:</strong> ' . $row->type;
							} else if (!empty($row->type_other)) {
								$tooltip[] = '<strong>Type:</strong> ' . $row->type_other;
							}
							if (!empty($row->activity)) {
								$tooltip[] = '<strong>Activity:</strong> ' . $row->activity;
							} else if (!empty($row->activity_other)) {
								$tooltip[] = '<strong>Activity:</strong> ' . $row->activity_other;
							}
							if (!empty($row->actvitiy_desc)) {
								$tooltip[] = '<strong>Activity Description:</strong> ' . $row->actvitiy_desc;
							}
							if (!empty($row->group) && $row->group != 'other') {
								$tooltip[] = '<strong>Group:</strong> ' . $this->crm_library->format_lesson_group($row->group);
							} else if (!empty($row->group_other)) {
								$tooltip[] = '<strong>Group:</strong> ' . $row->group_other;
							}
							if (!empty($row->class_size)) {
								$tooltip[] = '<strong>Class Size:</strong> ' . $row->class_size;
							}
							if ($type == 'booking' && $this->auth->user->department != 'headcoach') {
								if (!empty($row->charge)) {
									if ($row->charge == 'other') {
										if (is_numeric($row->charge_other)) {
											$row->charge = currency_symbol() . number_format($row->charge_other, 2);
										} else {
											$row->charge = $row->charge_other;
										}
									} else {
										switch ($row->charge) {
											case 'default':
												$row->charge = 'Booking Default';
												break;
											default:
												$row->charge = ucwords($row->charge);
												break;
										}
									}
									$tooltip[] = '<strong>Charge:</strong> ' . $row->charge;
								}
							}
							if ($type == 'event' || $booking_info->project == 1)  {
								if (!empty($row->price)) {
									$tooltip[] = '<strong>Price:</strong> ' . currency_symbol() . number_format($row->price, 2);
								}
								$booking_cutoff = $this->settings_library->get('booking_cutoff');
								if ($row->booking_cutoff !== NULL) {
									$booking_cutoff = $row->booking_cutoff;
								}
								$pl = NULL;
								if (!in_array($booking_cutoff, array(1, -1))) {
									$pl = 's';
								}
								$tooltip[] = '<strong>Online Booking Cutoff:</strong> ' . $booking_cutoff . ' hour' . $pl;
								$min_age = 0;
								if (!empty($this->settings_library->get('min_age'))) {
									$min_age = $this->settings_library->get('min_age');
								}
								if (!empty($booking_info->min_age)) {
									$min_age = $booking_info->min_age;
								}
								if (!empty($block_info->min_age)) {
									$min_age = $block_info->min_age;
								}
								if (!empty($row->min_age)) {
									$min_age = $row->min_age;
								}
								$pl = NULL;
								if (!in_array($min_age, array(1, -1))) {
									$pl = 's';
								}
								if (empty($min_age)) {
									$tooltip[] = '<strong>Minimum Age:</strong> None';
								} else {
									$tooltip[] = '<strong>Minimum Age:</strong> ' . $min_age . ' year' . $pl;
								}
								$max_age = 0;
								if (!empty($this->settings_library->get('max_age'))) {
									$max_age = $this->settings_library->get('max_age');
								}
								if (!empty($booking_info->max_age)) {
									$max_age = $booking_info->max_age;
								}
								if (!empty($block_info->max_age)) {
									$max_age = $block_info->max_age;
								}
								if (!empty($row->max_age)) {
									$max_age = $row->max_age;
								}
								$pl = NULL;
								if (!in_array($max_age, array(1, -1))) {
									$pl = 's';
								}
								if (empty($max_age)) {
									$tooltip[] = '<strong>Maximum Age:</strong> None';
								} else {
									$tooltip[] = '<strong>Maximum Age:</strong> ' . $max_age . ' year' . $pl;
								}
							}

							// Staff Type
							$staff_type_array = array('head' => 'headcoaches',
							'lead' => 'leadcoaches',
							'assistant' => 'assistantcoaches',
							'participant' => 'participants',
							'observer' => 'observers');
							foreach($staff_type_array as $staff_key => $staff_value){
								if (isset($lesson_staff_by_type_name[$row->lessonID][$staff_value]) && count($lesson_staff_by_type_name[$row->lessonID][$staff_value])) {
									$label = $this->settings_library->get_staffing_type_label($staff_key);
									if (count($lesson_staff_by_type_name[$row->lessonID][$staff_value]) != 1) {
										$label = Inflect::pluralize($label);
									}
									$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson_staff_by_type_name[$row->lessonID][$staff_value]);
								}
							}
							$requirements = array();
							foreach ($lesson_requirements as $key => $value) {
								if (isset($row->$key) && $row->$key == 1) {
									$requirements[] = $value;
								}
							}
							if (count($requirements) > 0) {
								$tooltip[] = '<strong>Session Requirements:</strong> ' . implode(", ", $requirements);
							}
							$requirements_met = TRUE;
							$staff_req_desc = 'None';
							$staff_req = [];
							foreach ($required_staff_for_session as $key => $label) {
								$staff_count = 0;
								$field = 'staff_required_' . $key;
								if (isset($lesson_staff_by_type[$row->lessonID][$key])) {
									$staff_count = count($lesson_staff_by_type[$row->lessonID][$key]);
								}
								if ($row->$field > 0 && $staff_count < $row->$field) {
									$requirements_met = FALSE;
								}
								if ($row->$field > 0) {
									$staff_req[] = $this->settings_library->get_staffing_type_label($key) . ' (' . $staff_count . '/' . $row->$field . ')';
								}
							}
							if (count($staff_req) > 0) {
								$staff_req_desc = implode(", ", $staff_req);
							}
							// check for at least one staff member if no staff req
							if (!isset($lesson_staff_by_type[$row->lessonID]) || array_sum(array_map('intval', $lesson_staff_by_type[$row->lessonID])) == 0) {
								$requirements_met = FALSE;
							}

							$tooltip[] = '<strong>Staff Requirements:</strong> ' . $staff_req_desc;
							if ($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) {
								$offer_accept = sprintf('<strong>Offer & Accept %s:</strong> %s',
									!empty($row->offer_type) ? ($row->offer_type != 'auto' ? '(Manual)' : '(' . ucwords($row->offer_type) . ')') : '',
									ucwords($offer_accept_status)) ;
								if (!empty($row->offer_accept_reason)) {
									$offer_accept .= ' (' . $row->offer_accept_reason . ')';
								}
								$tooltip[] = $offer_accept;
							}
							$tooltips[] = '<div class="tooltip-' . $row->lessonID . '">' . implode('<br />', $tooltip) . '</div>';
							?>
							<tr class="<?php if (in_array($row->lessonID, $invalid_lessons)) { echo 'invalid'; } if ($requirements_met !== TRUE) { echo ' missing_staff'; } ?>">
								<?php
								$date_info = "";
								$startdate = $enddate = "";
								if (!empty($row->startDate)) {
									$date_info = mysql_to_uk_date($row->startDate);
									$startdate = $row->startDate;
									if (!empty($row->endDate) && strtotime($row->endDate) > strtotime($row->startDate)) {
										$date_info.= '-' . mysql_to_uk_date($row->endDate);
										$enddate = $row->endDate;
									}
								}
								?>
								<td class="center">
									<input name="lessons[]" data-start-date="<?php echo $startdate?>" data-end-date="<?php echo $enddate?>" value="<?php echo $row->lessonID; ?>"<?php if (array_key_exists('lessons', $bulk_data) && array_key_exists($row->lessonID, $bulk_data['lessons'])) { echo " checked=\"checked\""; } ;?> type="checkbox" />
								</td>
								<td class="name" data-tooltip="tooltip-<?php echo $row->lessonID; ?>">
									<?php
									echo anchor('bookings/sessions/edit/' . $row->lessonID, ucwords($row->day));
									if(!empty($date_info)){
										echo '<br />('.$date_info.')';
									}
									?>
								</td>
								<td>
									<?php echo substr($row->startTime, 0, 5); ?>
								</td>
								<td>
									<?php echo substr($row->endTime, 0, 5); ?>
								</td>
								<td>
									<?php
									if ($row->group == "other") {
										echo $row->group_other;
									} else {
										echo $this->crm_library->format_lesson_group($row->group);
									}
									?>
								</td>
								<td>
									<?php echo $row->class_size; ?>
								</td>
								<td>
									<?php echo $row->location; ?>
								</td>
								<td>
									<?php
									if (!empty($row->activity_desc)) {
										echo '<span title="' . $row->activity_desc . '">';
									}
									if (!empty($row->activity)) {
										echo $row->activity;
									} else if (!empty($row->activity_other)) {
										echo $row->activity_other;
									}
									if (!empty($row->activity_desc)) {
										echo '</span>';
									}
									?>
								</td>
								<td>
									<?php
									if (!empty($row->type)) {
										echo $row->type;
									} else if (!empty($row->type_other)) {
										echo $row->type_other;
									}
									?>
								</td>
								<?php if ($type == 'event' || $booking_info->project == 1) {
									?><td class="center">
										<?php
										$row->participants = 0;

										switch ($booking_info->register_type) {
											case 'numbers':
												$row->participants = $row->participants_numbers;
												break;
											case 'names':
											case 'bikeability':
												$row->participants = $row->participants_names;
												break;
											case 'children':
											case 'children_bikeability':
											case 'children_shapeup':
												$row->participants = 0;
												if (isset($participants[$row->lessonID]['children'])) {
													$row->participants = $participants[$row->lessonID]['children'];
												}
												break;
											case 'individuals':
											case 'individuals_bikeability':
											case 'individuals_shapeup':
											$row->participants = 0;
												if (isset($participants[$row->lessonID]['individuals'])) {
													$row->participants = $participants[$row->lessonID]['individuals'];
												}
												break;
											case 'adults_children':
												$row->participants = 0;
												if (isset($participants[$row->lessonID]['individuals'])) {
													$row->participants += $participants[$row->lessonID]['individuals'];
												}
												if (isset($participants[$row->lessonID]['children'])) {
													$row->participants += $participants[$row->lessonID]['children'];
												}
												break;
										}
										echo anchor('bookings/participants/' . $row->blockID . '/' . $row->lessonID, intval($row->participants) . '/' . intval($row->target_participants));
										?>
									</td><?php
								} ?>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('bookings/sessions/edit/' . $row->lessonID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/sessions/remove/' . $row->lessonID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a>
									</div>
								</td>
							</tr>
							<?php
						}
					}
					?>
					<tr>
						<td></td>
						<td>
							<?php
							$options = array(
								'' => 'Day',
								'monday' => 'Monday',
								'tuesday' => 'Tuesday',
								'wednesday' => 'Wednesday',
								'thursday' => 'Thursday',
								'friday' => 'Friday',
								'saturday' => 'Saturday',
								'sunday' => 'Sunday'
							);
							echo form_dropdown('day', $options, set_value('day', NULL, FALSE), 'id="day" class="form-control select2"');
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
							echo form_dropdown('startTimeH', $options, set_value('startTimeH', $this->crm_library->htmlspecialchars_decode($startTimeH), FALSE), 'id="startTimeH" class="form-control select2"');
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
							echo form_dropdown('startTimeM', $options, set_value('startTimeM', NULL, FALSE), 'id="startTimeM" class="form-control select2"');
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
							echo form_dropdown('endTimeH', $options, set_value('endTimeH', $this->crm_library->htmlspecialchars_decode($endTimeH), FALSE), 'id="endTimeH" class="form-control select2"');
							echo '<br />';
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
							echo form_dropdown('endTimeM', $options, set_value('endTimeM', NULL, FALSE), 'id="endTimeM" class="form-control select2"');
							?>
						</td>
						<td>
							<?php
							$options = array(
								'' => 'Group/Class'
							);

							// fetch session groups
							$options = array_merge($options, $this->crm_library->lesson_groups());
							$options['other'] = 'Other (please specify)';

							echo form_dropdown('group', $options, set_value('group', NULL, FALSE), 'id="group" class="form-control select2" data-toggleother="field_group_other"');
							echo '<br />';
							$data = array(
								'name' => 'group_other',
								'id' => 'field_group_other',
								'class' => 'form-control',
								'value' => set_value('group_other', NULL, FALSE),
								'style' => 'display:none;',
								'maxlength' => 100
							);
							echo form_input($data);
							?>
						</td>
						<td>
							<?php
							$data = array(
								'name' => 'class_size',
								'id' => 'field_class_size',
								'class' => 'form-control',
								'value' => set_value('class_size', NULL, FALSE),
								'placeholder' => 'Class Size',
								'maxlength' => 100
							);
							echo form_input($data);
							?>
						</td>
						<td>
							<?php
							$data = array(
								'name' => 'location',
								'id' => 'field_location',
								'class' => 'form-control',
								'value' => set_value('location', NULL, FALSE),
								'placeholder' => 'Location',
								'maxlength' => 100
							);
							echo form_input($data);
							?>
						</td>
						<td>
							<?php
							$options = array(
								'' => 'Activity'
							);
							if ($activities->num_rows() > 0) {
								foreach ($activities->result() as $row) {
									$options[$row->activityID] = $row->name;
								}
							}
							$options['other'] = 'Other (Please specify)';

							echo form_dropdown('activityID', $options, set_value('activityID', NULL, FALSE), 'id="activityID" class="form-control select2" data-toggleother="field_activity_other"');
							echo '<br />';
							$data = array(
								'name' => 'activity_other',
								'id' => 'field_activity_other',
								'class' => 'form-control',
								'value' => set_value('activity_other', NULL, FALSE),
								'style' => 'display:none;',
								'maxlength' => 100
							);
							echo form_input($data);
							?>
						</td>
						<td>
							<?php
							$options = array(
								'' => 'Type'
							);
							if ($lesson_types->num_rows() > 0) {
								foreach ($lesson_types->result() as $row) {
									$options[$row->typeID] = $row->name;
								}
							}
							$options['other'] = 'Other (Please specify)';

							echo form_dropdown('typeID', $options, set_value('typeID', NULL, FALSE), 'id="type" class="form-control select2" data-toggleother="field_type_other"');
							echo '<br />';
							$data = array(
								'name' => 'type_other',
								'id' => 'field_type_other',
								'class' => 'form-control',
								'value' => set_value('type_other', NULL, FALSE),
								'style' => 'display:none;',
								'maxlength' => 100
							);
							echo form_input($data);
							?>
						</td>
						<?php if ($type == 'event' || $booking_info->project == 1) {
							?><td>
								<?php
								$data = array(
									'name' => 'target_participants',
									'id' => 'target_participants',
									'class' => 'form-control',
									'value' => set_value('target_participants', NULL, FALSE),
									'placeholder' => 'Target or Limit',
									'maxlength' => 10
								);
								echo form_number($data);
								?>
							</td><?php
						} ?>
						<td>
							<button class='btn btn-primary btn-submit gobutton' type="button">
								Go
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="row" id="scroll-table">
		<input type="hidden" id="block_start_date" value="<?php echo $block_info->startDate ?>" />
		<input type="hidden" id="block_end_date" value="<?php echo $block_info->endDate ?>" />
		<div class="col-sm-2">
			<?php
			$options = array(
				'staff' => 'Staff - Add',
				'removestaff' => 'Staff - Remove',
				'lessonplans' => 'Scheme of Work',
				'coachaccess' => 'Coach Access to ' . $this->settings_library->get_label('customer') . ' Attachments',
				'cancellation' => 'Exception - Cancellation',
				'staffchange' => 'Exception - Staff Change',
				'duplicate' => 'Duplicate',
				'remove' => 'Remove',
				'note' => 'Note',
				'activity' => 'Activity',
				'activity_desc' => 'Activity Description',
				'location' => 'Location at Delivery Address',
				'dbs' => 'Send DBS',
				'confirmation' => 'Send Confirmation',
				'minstaff' => 'No. of Staff Required',
				'class_size' => 'Class Size',
				'group' => 'Group/Class',
				'type' => 'Session Type',
				'day' => 'Day',
				'dates' => 'Dates',
				'removedates' => 'Dates - Remove',
				'times' => 'Time',
				'offer_accept_manual' => 'Offer & Accept'
			);
			switch ($type) {
				case 'booking':
					$options['changeaddress'] = 'Address';
					$options['requirements'] = 'Requirements';
					// head coach can't change this
					if ($this->auth->user->department != 'headcoach') {
						$options['charge'] = 'Customer Charge';
					}
					break;
			}
			if ($type == 'event' || $booking_info->project == 1) {
				$options['price'] = 'Price';
				$options['target_participants'] = 'Target Participants';
				$options['booking_cutoff'] = 'Online Booking Cut Off';
				$options['min_age'] = 'Minimum Age';
				$options['max_age'] = 'Maximum Age';
			}
			if (!$this->auth->has_features('resources')) {
				unset($options['lessonplans']);
			}
			if (!$this->auth->has_features('bookings_exceptions')) {
				unset($options['cancellation']);
				unset($options['staffchange']);
			}
			if (!$this->auth->has_features('offer_accept')) {
				unset($options['offer_accept']);
			}
			if (!$this->auth->has_features('offer_accept_manual')) {
				unset($options['offer_accept_manual']);
			}
			if ($this->settings_library->get('send_dbs') != 1) {
				unset($options['dbs']);
			}
			if ($this->settings_library->get('send_new_booking') != 1) {
				unset($options['confirmation']);
			}

			// sort
			asort($options);

			$options = array(
				'' => 'Bulk Action'
			) + $options;

			$action = NULL;
			if (array_key_exists('action', $bulk_data)) {
				$action = $bulk_data['action'];
			}

			echo form_dropdown('action', $options, $action, 'id="action" class="select2 form-control"');
			?>
		</div>
		<?php
		if ($type == 'booking') {
			?><div class="col-sm-2 bulk-supplementary changeaddress">
				<?php
				$options = array(
					'' => 'Select Address'
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

				$change_addressID = NULL;
				if (array_key_exists('change_addressID', $bulk_data)) {
					$change_addressID = $bulk_data['change_addressID'];
				}

				echo form_dropdown('change_addressID', $options, $change_addressID, 'id="change_addressID" class="form-control select2"');
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary requirements">
				<?php
				$lesson_requirements_array = array();
				if (array_key_exists('lesson_requirements', $bulk_data)) {
					$lesson_requirements_array = $bulk_data['lesson_requirements'];
				}
				echo form_multiselect('lesson_requirements[]', $lesson_requirements, $lesson_requirements_array, 'id="lesson_requirements" class="form-control select2" placeholder="Select Requirements"');
				?>
			</div><?php
		}
		?>
		<div class="col-sm-2 bulk-supplementary confirmation dbs">
			<?php
			$options = array(
				'' => 'Select Contact'
			);
			if ($contacts->num_rows() > 0) {
				foreach ($contacts->result() as $row) {
					$options[$row->contactID] = $row->name;
					if ($row->isMain == 1) {
						$options[$row->contactID] .= ' (Main)';
					}
				}
			}

			$bulk_contactID = NULL;
			if (array_key_exists('bulk_contactID', $bulk_data)) {
				$bulk_contactID = $bulk_data['bulk_contactID'];
			}

			echo form_dropdown('bulk_contactID', $options, $bulk_contactID, 'id="bulk_contactID" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary removestaff">
			<?php
			$remove_staff_array = array();
			if (array_key_exists('remove_staff', $bulk_data)) {
				$remove_staff_array = $bulk_data['remove_staff'];
			}
			echo form_multiselect('remove_staff[]', $lesson_staff, $remove_staff_array, 'id="remove_staff" class="form-control select2" data-dynamic-list-url="'.site_url('sessions/get_staff_on_session/'.$bookingID).'" placeholder="Select Staff"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary lessonplans">
			<?php
			$resources_attachments_array = array();
			if (array_key_exists('resources_attachments', $bulk_data)) {
				$resources_attachments_array = $bulk_data['resources_attachments'];
			}
			echo form_multiselect('resources_attachments[]', $resources_attachments, $resources_attachments_array, 'id="resources_attachments" class="form-control select2" placeholder="Select Document"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary coachaccess">
			<?php
			$coach_access_array = array();
			if (array_key_exists('coach_access', $bulk_data)) {
				$coach_access_array = $bulk_data['coach_access'];
			}
			echo form_multiselect('coach_access[]', $coach_access, $coach_access_array, 'id="coach_access" class="form-control select2" placeholder="Select Document"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary cancellation staffchange">
			<?php
			$from = NULL;
			if (array_key_exists('from', $bulk_data)) {
				$from = $bulk_data['from'];
			}
			$data = array(
				'name' => 'from',
				'id' => 'from',
				'class' => 'form-control datepicker',
				'placeholder' => 'From',
				'value' => set_value('from', $this->crm_library->htmlspecialchars_decode($from), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary cancellation staffchange">
			<?php
			$to = NULL;
			if (array_key_exists('to', $bulk_data)) {
				$to = $bulk_data['to'];
			}
			$data = array(
				'name' => 'to',
				'id' => 'to',
				'class' => 'form-control datepicker',
				'placeholder' => 'To',
				'value' => set_value('to', $this->crm_library->htmlspecialchars_decode($to), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary staff">
			<?php
			$from_date = mysql_to_uk_date($block_info->startDate);
			if (array_key_exists('from_date', $bulk_data)) {
				$from_date = $bulk_data['from_date'];
			}
			$data = array(
				'name' => 'from_date',
				'id' => 'from_date',
				'class' => 'form-control datepicker',
				'placeholder' => 'From',
				'value' => set_value('from_date', $this->crm_library->htmlspecialchars_decode($from_date), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary staff">
			<?php
			$to_date = mysql_to_uk_date($block_info->endDate);
			if (array_key_exists('to_date', $bulk_data)) {
				$to_date = $bulk_data['to_date'];
			}
			$data = array(
				'name' => 'to_date',
				'id' => 'to_date',
				'class' => 'form-control datepicker',
				'placeholder' => 'To',
				'value' => set_value('to_date', $this->crm_library->htmlspecialchars_decode($to_date), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary staff staffchange">
			<?php
			$options = array(
				'' => 'Select Staff'
			);
			if ($staff_list->num_rows() > 0) {
				foreach ($staff_list->result() as $row) {
					$options[$row->staffID] = $row->first . ' ' . $row->surname;
				}
			}

			$staffID = NULL;
			if (array_key_exists('staffID', $bulk_data)) {
				$staffID = $bulk_data['staffID'];
			}

			echo form_dropdown('staffID', $options, $staffID, 'id="staffID" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary staffchange">
			<?php
			$options = array(
				'' => 'Select Replacement'
			);
			if ($staff_list->num_rows() > 0) {
				$options[0] = 'No Replacement Required';
				foreach ($staff_list->result() as $row) {
					$options[$row->staffID] = $row->first . ' ' . $row->surname;
				}
			}

			$replacementID = NULL;
			if (array_key_exists('replacementID', $bulk_data)) {
				$replacementID = $bulk_data['replacementID'];
			}

			echo form_dropdown('replacementID', $options, $replacementID, 'id="replacementID" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary staff">
			<?php
			$options = array(
				'' => 'Select Type',
				'head' => $this->settings_library->get_staffing_type_label('head'),
				'lead' => $this->settings_library->get_staffing_type_label('lead'),
				'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
				'participant' => $this->settings_library->get_staffing_type_label('participant'),
				'observer' => $this->settings_library->get_staffing_type_label('observer')
			);

			$staff_type = NULL;
			if (array_key_exists('staff_type', $bulk_data)) {
				$staff_type = $bulk_data['staff_type'];
			}
			echo form_dropdown('staff_type', $options, $staff_type, 'id="staff_type" class="form-control select2"');
			?>
		</div>
		<?php
		if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll')) {
            $options = array(
                '' => 'Select Pay Type',
                '0' => 'Non-Salaried Session',
                '1' => 'Salaried Session'
            );

            $salaried = NULL;
            if (array_key_exists('salaried', $bulk_data)) {
                $salaried = strval($bulk_data['salaried']);
            }
            echo '<div class="col-sm-2 bulk-supplementary staff">' .
                form_dropdown('salaried', $options, $salaried, 'id="salaried" class="form-control select2" data-minimum-results-for-search="-1"') .
                '</div>';
		}
		?>
		<?php
		if ($type == 'event' || $booking_info->project == 1) {
			?><div class="col-sm-2 bulk-supplementary price">
				<?php
				$data = array(
					'name' => 'price',
					'id' => 'price',
					'class' => 'form-control',
					'value' => NULL,
					'maxlength' => 10
				);
				if (array_key_exists('price', $bulk_data)) {
					$data['value'] = $bulk_data['price'];
				}
				echo form_input($data);
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary target_participants">
				<?php
				$data = array(
					'name' => 'bulk_target_participants',
					'id' => 'bulk_target_participants',
					'class' => 'form-control',
					'value' => NULL,
					'maxlength' => 10
				);
				if (array_key_exists('target_participants', $bulk_data)) {
					$data['value'] = $bulk_data['target_participants'];
				}
				echo form_input($data);
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary booking_cutoff">
				<?php
				$data = array(
					'name' => 'booking_cutoff',
					'id' => 'booking_cutoff',
					'class' => 'form-control',
					'value' => NULL,
					'maxlength' => 3,
					'placeholder' => 'In Hours'
				);
				if (array_key_exists('booking_cutoff', $bulk_data)) {
					$data['value'] = $bulk_data['booking_cutoff'];
				}
				echo form_input($data);
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary min_age">
				<?php
				$data = array(
					'name' => 'min_age',
					'id' => 'min_age',
					'class' => 'form-control',
					'value' => NULL,
					'maxlength' => 3,
					'placeholder' => 'In Years'
				);
				if (array_key_exists('min_age', $bulk_data)) {
					$data['value'] = $bulk_data['min_age'];
				}
				echo form_input($data);
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary max_age">
				<?php
				$data = array(
					'name' => 'max_age',
					'id' => 'max_age',
					'class' => 'form-control',
					'value' => NULL,
					'maxlength' => 3,
					'placeholder' => 'In Years'
				);
				if (array_key_exists('max_age', $bulk_data)) {
					$data['value'] = $bulk_data['max_age'];
				}
				echo form_input($data);
				?>
			</div><?php
		}
		// head coach can't change this
		if ($type == 'booking' && $this->auth->user->department != 'headcoach') {
			?><div class="col-sm-2 bulk-supplementary charge">
				<?php
				$newcharge = NULL;
				if (array_key_exists('charge', $bulk_data)) {
					$newcharge = $bulk_data['charge'];
				}
				$options = array(
					'' => 'Select',
					'default' => 'Booking Default',
					'prepaid' => 'Prepaid',
					'free' => 'Free',
					'other' => 'Other (please specify)'
				);
				echo form_dropdown('newcharge', $options, set_value('newcharge', $this->crm_library->htmlspecialchars_decode($newcharge), FALSE), 'id="newcharge" class="form-control select2" data-toggleother="newcharge_other"');
				?>
			</div>
			<div class="col-sm-2 bulk-supplementary charge">
				<div>
					<?php
					$data = array(
						'name' => 'newcharge_other',
						'id' => 'newcharge_other',
						'class' => 'form-control',
						'value' => NULL,
						'maxlength' => 10
					);
					if (array_key_exists('charge_other', $bulk_data)) {
						$data['value'] = $bulk_data['charge_other'];
					}
					echo form_input($data);
					?>
				</div>
			</div><?php
		}
		?>
		<div class="col-sm-2 bulk-supplementary activity">
			<?php
			$options = array(
				'' => 'Select Activity',
			);
			if ($activities->num_rows() > 0) {
				foreach ($activities->result() as $row) {
					$options[$row->activityID] = $row->name;
				}
			}
			$options['other'] = 'Other (Please specify)';
			$newactivityID = NULL;
			if (array_key_exists('activityID', $bulk_data)) {
				$newactivityID = $bulk_data['activityID'];
			}
			echo form_dropdown('newactivityID', $options, $newactivityID, 'id="newactivityID" class="form-control select2" data-toggleother="newactivity_other"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary activity">
			<div>
				<?php
				$newactivity_other = NULL;
				if (array_key_exists('activity_other', $bulk_data)) {
					$newactivity_other = $bulk_data['activity_other'];
				}
				$data = array(
					'name' => 'newactivity_other',
					'id' => 'newactivity_other',
					'class' => 'form-control',
					'value' => $newactivity_other,
					'maxlength' => 100
				);
				echo form_input($data);
				?>
			</div>
		</div>
        <div class="col-sm-2 bulk-supplementary offer_accept_manual">
            <?php
            $options = array(
                '' => 'Select Type',
                'groups' => 'Groups',
                'individual' => 'Individual'
            );
            echo form_dropdown('offer_accept_type', $options, null, 'id="offer_accept_type" class="form-control select2" toggle-subsections="1" data-toggleother="offer_accept_manual_groups offer_accept_manual_staff offer_accept_manual_role"');
            ?>
        </div>
        <div class="col-sm-2 bulk-supplementary offer_accept_manual_groups bulk-supplementary-subsection groups">
            <?php
            $options = array(
                '' => 'Select Group'
            );

            if (count($groups) > 0) {
                foreach ($groups as $group) {
                    $options[$group->groupID] = $group->name;
                }
            }

            echo form_dropdown('offer_accept_group', $options, null, 'id="offer_accept_manual_groups" class="form-control select2"');
            ?>
        </div>
        <div class="col-sm-2 bulk-supplementary offer_accept_manual_staff bulk-supplementary-subsection individual">
            <?php
            $options = array(
                '' => 'Select Staff'
            );
            if ($staff_list->num_rows() > 0) {
                foreach ($staff_list->result() as $row) {
                    $options[$row->staffID] = $row->first . ' ' . $row->surname;
                }
            }

            $staffID = NULL;
            if (array_key_exists('staffID', $bulk_data)) {
                $staffID = $bulk_data['staffID'];
            }

            echo form_dropdown('staff_id_offer', $options, $staffID, 'id="offer_accept_manual_staff" class="form-control select2"');
            ?>
        </div>
        <div class="col-sm-2 bulk-supplementary offer_accept_manual_role bulk-supplementary-subsection individual groups">
            <?php
            $options = array(
                '' => 'Select Type',
                'head' => $this->settings_library->get_staffing_type_label('head'),
                'lead' => $this->settings_library->get_staffing_type_label('lead'),
                'assistant' => $this->settings_library->get_staffing_type_label('assistant'),
                'participant' => $this->settings_library->get_staffing_type_label('participant'),
                'observer' => $this->settings_library->get_staffing_type_label('observer')
            );

            $staff_type = NULL;
            if (array_key_exists('staff_type', $bulk_data)) {
                $staff_type = $bulk_data['staff_type'];
            }
            echo form_dropdown('staff_type_offer', $options, $staff_type, 'id="offer_accept_manual_role" class="form-control select2"');
            ?>
        </div>
		<div class="col-sm-2 bulk-supplementary offer_accept_manual_combine bulk-supplementary-subsection individual groups">
			<div class="codersblock-container">
				<input id="toggle1" type="checkbox" name="combine-sessions">
				<label for="toggle1">Accept/Decline All</label>
			</div>
		</div>
		<?php
		if ($this->settings_library->get('salaried_sessions') == 1 && $this->auth->has_features('payroll')) {
			?><div class="col-sm-2 bulk-supplementary bulk-supplementary-subsection individual groups">
				<div class="codersblock-container">
					<input id="offer_accept_salaried" type="checkbox" name="offer_accept_salaried" value="1"<?php if (array_key_exists('offer_accept_salaried', $bulk_data) && $bulk_data['offer_accept_salaried'] == 1) {
						echo ' checked="checked"';
					} ?>>
					<label for="offer_accept_salaried">Salaried Session</label>
				</div>
			</div><?php
		}
		?>
		<div class="col-sm-2 bulk-supplementary type">
			<?php
			$options = array(
				'' => 'Select Type',
			);
			if ($lesson_types->num_rows() > 0) {
				foreach ($lesson_types->result() as $row) {
					$options[$row->typeID] = $row->name;
				}
			}
			$options['other'] = 'Other (Please specify)';
			$newtypeID = NULL;
			if (array_key_exists('typeID', $bulk_data)) {
				$newtypeID = $bulk_data['typeID'];
			}
			echo form_dropdown('newtypeID', $options, $newtypeID, 'id="newtypeID" class="form-control select2" data-toggleother="newtype_other"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary type">
			<div>
				<?php
				$newtype_other = NULL;
				if (array_key_exists('type_other', $bulk_data)) {
					$newtype_other = $bulk_data['type_other'];
				}
				$data = array(
					'name' => 'newtype_other',
					'id' => 'newtype_other',
					'class' => 'form-control',
					'value' => $newtype_other,
					'maxlength' => 100
				);
				echo form_input($data);
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary group">
			<?php
			$options = array(
				'' => 'Select Group',
			);
			$options = array_merge($options, $this->crm_library->lesson_groups());
			$options['other'] = 'Other (Please specify)';
			$newgroup = NULL;
			if (array_key_exists('group', $bulk_data)) {
				$newgroup = $bulk_data['group'];
			}
			echo form_dropdown('newgroup', $options, $newgroup, 'id="newgroup" class="form-control select2" data-toggleother="newgroup_other"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary group">
			<div>
				<?php
				$newgroup_other = NULL;
				if (array_key_exists('group_other', $bulk_data)) {
					$newgroup_other = $bulk_data['group_other'];
				}
				$data = array(
					'name' => 'newgroup_other',
					'id' => 'newgroup_other',
					'class' => 'form-control',
					'value' => $newgroup_other,
					'maxlength' => 100
				);
				echo form_input($data);
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary activity_desc">
			<div>
				<?php
				$newactivity_desc = NULL;
				if (array_key_exists('activity_desc', $bulk_data)) {
					$newactivity_desc = $bulk_data['activity_desc'];
				}
				$data = array(
					'name' => 'newactivity_desc',
					'id' => 'newactivity_desc',
					'class' => 'form-control',
					'value' => $newactivity_desc,
					'maxlength' => 200
				);
				echo form_input($data);
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary location">
			<div>
				<?php
				$newlocation = NULL;
				if (array_key_exists('location', $bulk_data)) {
					$newlocation = $bulk_data['location'];
				}
				$data = array(
					'name' => 'newlocation',
					'id' => 'newlocation',
					'class' => 'form-control',
					'value' => $newlocation,
					'maxlength' => 100
				);
				echo form_input($data);
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary class_size">
			<div>
				<?php
				$newclass_size = NULL;
				if (array_key_exists('class_size', $bulk_data)) {
					$newclass_size = $bulk_data['class_size'];
				}
				$data = array(
					'name' => 'newclass_size',
					'id' => 'newclass_size',
					'class' => 'form-control',
					'value' => $newclass_size,
					'maxlength' => 100
				);
				echo form_input($data);
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary day">
			<div>
				<?php
				$newday = NULL;
				if (array_key_exists('day', $bulk_data)) {
					$newday = $bulk_data['day'];
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
				echo form_dropdown('newday', $options, set_value('newday', $this->crm_library->htmlspecialchars_decode($newday), FALSE), 'id="newday" class="form-control select2"');
				?>
			</div>
		</div>
		<div class="col-sm-2 bulk-supplementary dates">
			<?php
			$newstartDate = NULL;
			if (array_key_exists('startDate', $bulk_data)) {
				$newstartDate = $bulk_data['startDate'];
			}
			$data = array(
				'name' => 'newstartDate',
				'id' => 'newstartDate',
				'class' => 'form-control datepicker',
				'placeholder' => 'Start Date',
				'value' => set_value('newstartDate', $this->crm_library->htmlspecialchars_decode($newstartDate), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary dates">
			<?php
			$newendDate = NULL;
			if (array_key_exists('endDate', $bulk_data)) {
				$newendDate = $bulk_data['endDate'];
			}
			$data = array(
				'name' => 'newendDate',
				'id' => 'newendDate',
				'class' => 'form-control datepicker',
				'placeholder' => 'End Date',
				'value' => set_value('newendDate', $this->crm_library->htmlspecialchars_decode($newendDate), FALSE),
				'maxlength' => 10,
				'data-mindate' => $block_info->startDate,
				'data-maxdate' => $block_info->endDate
			);
			echo form_input($data);
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary times">
			<?php
			$options = array();
			$h = 6;
			while ($h <= 23) {
				$h = sprintf("%02d",$h);
				$options[$h] = $h;
				$h++;
			}
			$newstartTimeH = NULL;
			if (array_key_exists('startTimeH', $bulk_data)) {
				$newstartTimeH = $bulk_data['startTimeH'];
			}
			echo form_dropdown('newstartTimeH', $options, set_value('newstartTimeH', $this->crm_library->htmlspecialchars_decode($newstartTimeH), FALSE), 'id="newstartTimeH" class="form-control select2"');
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
			$newstartTimeM = NULL;
			if (array_key_exists('startTimeM', $bulk_data)) {
				$newstartTimeM = $bulk_data['startTimeM'];
			}
			echo form_dropdown('newstartTimeM', $options, set_value('newstartTimeM', $this->crm_library->htmlspecialchars_decode($newstartTimeM), FALSE), 'id="newstartTimeM" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary times">
			<?php
			$options = array();
			$h = 6;
			while ($h <= 23) {
				$h = sprintf("%02d",$h);
				$options[$h] = $h;
				$h++;
			}
			$newendTimeH = 7;
			if (array_key_exists('endTimeH', $bulk_data)) {
				$newendTimeH = $bulk_data['endTimeH'];
			}
			echo form_dropdown('newendTimeH', $options, set_value('newendTimeH', $this->crm_library->htmlspecialchars_decode($newendTimeH), FALSE), 'id="newendTimeH" class="form-control select2"');
			echo '<br />';
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
			$newendTimeM = NULL;
			if (array_key_exists('endTimeM', $bulk_data)) {
				$newendTimeM = $bulk_data['endTimeM'];
			}
			echo form_dropdown('newendTimeM', $options, set_value('newendTimeM', NULL, FALSE), 'id="newendTimeM" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2 bulk-supplementary times">
			<?php
			$data = array(
				'name' => 'adjust_staff_times',
				'id' => 'adjust_staff_times',
				'value' => 1
			);
			$adjust_staff_times = NULL;
			if (array_key_exists('adjust_staff_times', $bulk_data)) {
				$adjust_staff_times = $bulk_data['adjust_staff_times'];
			}
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
				$notify_staff = 0;
				if (array_key_exists('notify_staff', $bulk_data)) {
					$notify_staff = $bulk_data['notify_staff'];
				}
				if (set_value('notify_staff', $this->crm_library->htmlspecialchars_decode($notify_staff), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				echo form_checkbox($data);
				echo ' notify them</label>';
			}
			?><br>
			<em>Only effects staff where either the start or end staffing time matches the previous times</em>
		</div>
		<?php
			foreach ($required_staff_for_session as $type => $name) { ?>
				<div class="col-sm-2 bulk-supplementary minstaff">
					<?php
					$data = array(
						'name' => 'minstaff_' . $type,
						'id' => 'minstaff_' . $type,
						'class' => 'form-control',
						'value' => NULL,
						'maxlength' => 3,
						'placeholder' => $this->settings_library->get_staffing_type_label($type)
					);
					if (array_key_exists('minstaff_' . $type, $bulk_data)) {
						$data['value'] = $bulk_data['minstaff_' . $type];
					}
					echo form_input($data);
					?>
				</div>
			<?php
			}
		?>
		<?php
		/*if ($this->auth->has_features('offer_accept')) {
			?><div class="col-sm-2 bulk-supplementary offer_accept">
				<?php
				$offer_accept_grouped = NULL;
				if (array_key_exists('offer_accept_grouped', $bulk_data)) {
					$offer_accept_grouped = $bulk_data['offer_accept_grouped'];
				}
				$options = array(
					'no' => 'Staff Can Accept/Decline Any',
					'yes' => 'Staff Must Accept All'
				);
				echo form_dropdown('offer_accept_grouped', $options, set_value('offer_accept_grouped', $this->crm_library->htmlspecialchars_decode($offer_accept_grouped), FALSE), 'id="offer_accept_grouped" class="form-control select2"');
				?>
			</div><?php
		}*/
		?>
		<div class="col-sm-2">
			<button class='btn btn-primary btn-submit gobutton btn-custom' type="button">
				Go
			</button>
		</div>
	</div>
<?php echo form_close(); ?>
<?php echo $this->pagination_library->display($page_base); ?>
<div class="tooltips">
	<?php echo implode("\n", $tooltips); ?>
</div>
<?php if($ajaxFlag == NULL){ ?>
<!-- Data Model for Model Display -->
</div>
<div class="modal fade" id="myModal_message" role="dialog">
	<div class="modal-dialog " style="width:50%; min-width:600px">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-body" style="padding:0" id="verification">
				<div style="" id="msg">
					<?php echo $message ?>
				</div>
			</div>
		</div>
	</div>
</div>
	<?php } ?>
