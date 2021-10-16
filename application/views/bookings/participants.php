<?php
if ($print_view === 2 || $print_view === 3) {
?><!DOCTYPE HTML>
<html>
<head>
	<title><?php echo $title; ?></title>
	<meta content='width=device-width, initial-scale=1' name='viewport'>
	<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css " />


</head>
<body>
<?php
if (in_array($booking_info->register_type, array('children', 'individuals', 'individuals_bikeability', 'children_bikeability')) && $print_view == 2) { echo "<h3>".$booking_info->name." - ".$block_info->name."</h3>"; } ?>
<?php if($print_view == 2){ ?>
<div class="intro">
	<h1><?php echo $title; ?></h1>
	<?php
	if (array_key_exists($lessonID, $tabs)) {
		?><p><?php echo $tabs[$lessonID]['desc']; ?></p><?php
	}
	?>
	<p class="noprint"><a href="#" class="print">Print</a></p>
</div><?php }
}else {
	display_messages();
	if ($bookingID != NULL) {
		$data = array(
			'bookingID' => $bookingID,
			'tab' => $tab,
			'type' => $type,
			'is_project' => $booking_info->project,
		'type' => $booking_info->type
		);
		$this->load->view('bookings/tabs.php', $data);
	}
}
if($print_view != 3){
	if ($print_view !== 2) {
		$form_classes = 'card card-custom card-search';
		if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
		echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
		<div class="card-header">
			<div class="card-title">
				<h3 class="card-label">Search</h3>
			</div>
			<div class="card-toolbar">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<?php
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					?><div class='col-sm-2'>
					<p>
						<strong><label for="field_child">Child</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_child',
						'id' => 'field_child',
						'class' => 'form-control',
						'value' => $search_fields['child']
					);
					echo form_input($data);
					?>
					</div><?php
				}
				?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_contact">Contact</label></strong>
					</p>
					<?php
					$data = array(
						'name' => 'search_contact',
						'id' => 'field_contact',
						'class' => 'form-control',
						'value' => $search_fields['contact']
					);
					echo form_input($data);
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
		<div id="results"></div><?php
	}

	// tabs
	$data = array(
		'print_view' => $print_view,
		'lessonID' => $lessonID,
		'page_base' => $page_base,
		'tabs' => $tabs,
	);



	$this->load->view('bookings/participants-print-tabs');
	if (count($items) == 0 && $print_view !== 2) {
		?>
		<div class="alert alert-info">
			No participants found.<?php if ($print_view !== 2) { ?> Do you want to <?php echo anchor('booking/book/' . $blockID, 'create one'); ?>?<?php } ?>
		</div>
		<?php
	} else {
		?>
		<?php echo form_open(site_url($page_base)); ?>
		<div class='card card-custom'>
		<?php if($print_view !== 1){ ?>
			<div class="visible-xs">
				<div class="col-md-3">
					<label> Date: </label>
					<select class="form-control" name="filterdate" id="filterdate">
						<?php
						$where = array(
							'lessonID' => $lessonID,
							'accountID' => $this->auth->user->accountID
						);
						$res = $this->db->from('bookings_lessons')->where($where)->get();
						$firstdate = '';
						if ($res->num_rows() > 0) {
							foreach ($res->result() as $row) {
								$lesson_info = $row;
							}
							if ($booking_info->type == 'booking') {
								$x =0;
								$date = $block_info->startDate;
								while (strtotime($date) <= strtotime($block_info->endDate)) {
									$day = strtolower(date("l", strtotime($date)));
									if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
										if($date != null)
											echo '<option value="'.str_replace("/","",mysql_to_uk_date($date)).'">'.mysql_to_uk_date($date).'</option>';
										if($x == 0) { $x++; $firstdate = str_replace("/","",mysql_to_uk_date($date)); }
									}
									$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
								}

							} else {
								echo '<option value="'.ucwords($lesson_info->day).'">'.ucwords($lesson_info->day).'</option>';
								$firstdate = $lesson_info->day;
							}
						}
						?>
					</select>
				</div>
				<br />
			</div>

			<div class="visible-xs" style="margin-bottom:25px">
				<div class="col-md-3">
					<label> Filters: </label><br />
					<select class="select2" data-placeholder="Please select" style="width:100%" name="filter" multiple id="filter">
						<option value="signin"> Signed In </option>
						<option value="notsignin"> Not Signed In </option>
						<option value="signout"> Signed Out </option>
						<option value="notsignout"> Not Signed Out </option>
						<option value="medical"> Medical Information </option>
						<option value="paymentdue"> Payment Due </option>
					</select>
				</div>
			</div>


		<?php } ?>
			<div class='table-responsive'>
				<div class='scrollable-area'>
				<!-- For Desktop View -->
					<table class='table table-striped table-bordered <?php echo ($print_view !== 1)?'hidden-xs':'' ?>' id="participants_indivisual">
						<thead>
						<tr>
							<th class=""></th>
							<th class=""></th>
							<?php

							$array = array();
							if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
								?><th></th><?php
							}
							?>
							<th  class=""></th>
							<?php
							if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
								?><th  class=""></th><?php
							}
							?>
							<th  class=""></th>
							<?php
							for($i=1;$i<=20;$i++) {
								if (!empty($booking_info->{"monitoring".$i})) {
									echo "<th  class=''></th>";
								}
							}
							$where = array(
								'lessonID' => $lessonID,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->from('bookings_lessons')->where($where)->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$lesson_info = $row;
								}
								if ($booking_info->type == 'booking') {
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$day = strtolower(date("l", strtotime($date)));
										if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
											?><th colspan="2"><?php echo mysql_to_uk_date($date); ?></th><?php

										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}

								} else {
									?><th colspan="2"><?php echo ucwords($lesson_info->day); ?></th><?php
								}
							}
							?>
						</tr>
						<tr>
							<th class="">Name</th>
							<th class="">Age</th>
							<?php
							if(!in_array(2, $array))
								$array[] = 2;
							if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
								?><th>Account Holder</th><?php
							}
							?>
							<th class="">Telephone</th>
							<?php
							if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
								?><th class="">Photo Consent</th><?php
							}
							?><th class="">Medical</th>
							<?php
							$cols = 6;
							for($i=1;$i<=20;$i++) {
								if (!empty($booking_info->{"monitoring".$i})) {
									echo "<th class=''>".$booking_info->{"monitoring".$i}."</th>";
									$cols++;
								}
							}
							// get session day
							$where = array(
								'lessonID' => $lessonID,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->from('bookings_lessons')->where($where)->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$lesson_info = $row;
								}
								if ($booking_info->type == 'booking') {
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$day = strtolower(date("l", strtotime($date)));
										if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
											?><th>Sign In</th>
											<th>Sign Out</th><?php

											if(!in_array(($cols), $array))
												$array[] = $cols;
											if(!in_array(($cols + 1), $array))
												$array[] = $cols + 1;
											$cols += 2;
										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}

								} else {
									?><th>Sign In</th>
									<th>Sign Out</th><?php

									if(!in_array(($cols), $array))
										$array[] = $cols;
									if(!in_array(($cols + 1), $array))
										$array[] = $cols + 1;
									$cols += 2;
								}
							}
							?>
						</tr>
						</thead>
						<tbody>
						<?php

						if (count($items) > 0) {
							foreach ($items as $itemNumber => $row) {

								?>
								<tr id="" class="<?php echo date("Ymd",strtotime($block_info->startDate)) ?>">
									<?php

									if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										$row->contact_first = $row->booker_first;
										$row->contact_last = $row->booker_last;
										$row->contact_title = $row->booker_title;
										$image_data = @unserialize($row->child_profile_pic);
										?><td  class="">
										<?php
										if ($image_data !== FALSE) {
											echo "<a href='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$row->childID)."' data-fancybox='gallery' class='profileimage' >".trim($row->child_first . ' ' . $row->child_last)."</a>";
										}else{
											echo trim($row->child_first . ' ' . $row->child_last);
										}?>
										</td>
										<td class="center ">
										<?php echo calculate_age($row->dob); ?>
										</td><?php
									}
									?>
									<td>
										<?php

										if ($booking_info->register_type === "adults_children") {

											if($row->contactID != ''){
												$image_data = @unserialize($row->profile_pic);
												if ($image_data !== FALSE) {
													echo "<a href='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$row->contactID)."' data-fancybox='gallery' class='profileimage' >";
													echo $row->contact_first . ' ' . $row->contact_last;
													if (!empty($row->contact_title)) {
														echo ' (' . ucwords($row->contact_title) . ')';
													}
													echo "</a>";
												}else{
													echo $row->contact_first . ' ' . $row->contact_last;
													if (!empty($row->contact_title)) {
														echo ' (' . ucwords($row->contact_title) . ')';
													}
												}
											}else{
												$image_data = @unserialize($row->child_profile_pic);
												if ($image_data !== FALSE) {
													echo "<a href='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$row->childID)."' data-fancybox='gallery' class='profileimage' >";
													echo $row->child_first . ' ' . $row->child_last;
													if (!empty($row->child_title)) {
														echo ' (' . ucwords($row->child_title) . ')';
													}
													echo "</a>";
												}else{
													echo $row->child_first . ' ' . $row->child_last;
													if (!empty($row->child_title)) {
														echo ' (' . ucwords($row->child_title) . ')';
													}
												}
											}
										}else {
											$image_data = @unserialize($row->profile_pic);
											if ($image_data !== FALSE) {
												echo "<a href='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$row->contactID)."' data-fancybox='gallery' class='profileimage' >";
												echo $row->contact_first . ' ' . $row->contact_last;
												if (!empty($row->contact_title)) {
													echo ' (' . ucwords($row->contact_title) . ')';
												}
												echo "</a>";
											}else{
												echo $row->contact_first . ' ' . $row->contact_last;
												if (!empty($row->contact_title)) {
													echo ' (' . ucwords($row->contact_title) . ')';
												}
											}
										}
										?>
									</td>
										<?php
										if ($booking_info->register_type === "adults_children") {
											if($row->contactID != ''){
												echo "<td class=''>".calculate_age($row->contact_dob)."</td>";
											}else{
												echo "<td class=''>".calculate_age($row->child_dob)."</td>";
											}
										}
										?>
									<?php
									if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
										?><td class="center ">
										<?php echo calculate_age($row->contact_dob); ?>
										</td><?php
									}
									?>
									<td class="">
										<?php
										$numbers = array();
										// use contact phone if set, else booker
										if (!empty($row->contactID)) {
											if (!empty($row->mobile)) {
												$numbers[] = $row->mobile;
											}
											if (!empty($row->phone)) {
												$numbers[] = $row->phone;
											}
											if (!empty($row->workPhone)) {
												$numbers[] = $row->workPhone;
											}
										} else {
											if (!empty($row->booker_mobile)) {
												$numbers[] = $row->booker_mobile;
											}
											if (!empty($row->booker_phone)) {
												$numbers[] = $row->booker_phone;
											}
											if (!empty($row->booker_workPhone)) {
												$numbers[] = $row->booker_workPhone;
											}
										}
										echo implode(", ", $numbers);
										?>
									</td>
									<?php
									if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										?><td class="has_icon ">
										<?php
										if ($row->type == 'child') {
											if($row->photoConsent == 1) {
												?><span class='btn btn-success btn-sm no-action' title="Yes">
																<i class='far fa-check'></i>
															</span><?php
											} else {
												?><span class='btn btn-danger btn-sm no-action' title="No">
																<i class='far fa-times'></i>
															</span><?php
											}
										}
										?>
										</td><?php
									}
									?><td class="">
										<?php
										if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {													echo $row->medical;
										} else {
											echo $row->contact_medical;
										}
										?>
									</td><?php
									$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
									if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }

									if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
										$field = 'contactID';
									} else {
										if ($booking_info->register_type=="adults_children") {
											$field = !empty($items[$itemNumber]->childID) ? "childID" : "contactID";
										}
										else {
											$field = 'childID';
										}
									}

									for($i=1;$i<=20;$i++) {
										if (!empty($booking_info->{"monitoring".$i})) {
											if ($booking_info->{"monitoring".$i."_entry_type"}=="2") {
												echo "<td class=''>".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i} : "")."</td>";
											}
											else {
												echo "<td class=''> <textarea name='monitor_register_value' data-url='".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/".$i)."' rows='1' style='min-height:auto; min-width:auto; resize: none;' class='break-word'>".set_value("monitor_register_value",isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i} : "")."</textarea>";
											}
										}
									}

									if (isset($lesson_info)) {
										if ($booking_info->type == 'booking') {
											$date = $block_info->startDate;
											while (strtotime($date) <= strtotime($block_info->endDate)) {
												$day = strtolower(date("l", strtotime($date)));
												if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
													?><td class="has_icon register_toggle"><?php
													if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
														if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 'staff') {
															?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																		<i class='far fa-user'></i>
																	</span><?php
														} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 1) {
															?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-success btn-sm' data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" title="Attended">
																<i class='far fa-check'></i>
															</a><br />
															<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
															echo "</span>";
														} else {
															?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-warning btn-sm' data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" title="Attending">
																<i class='far fa-times'></i>
															</a><br />
															<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
															echo "</span>";

														}
													}
													?></td><?php

													?><td class="has_icon register_toggle"><?php

													if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
														if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
															echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='".$row->pin."' />";
														else
															echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='0' />";
														if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 'staff') {
															?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																		<i class='far fa-user'></i>
																	</span><?php
														} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 1) {
															?>
															<a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" id="click_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
																<i class='far fa-check'></i>
															</a><br />
															<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>">
															<?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
															echo "</span>";

														} else {
															?>
															<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" id="click_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="Signout">
																<i class='far fa-times'></i>
															</a><br />
															<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
															echo "</span>";

														}
													}
													?></td><?php
												}
												$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
											}
										} else {
											$days_booked = array();
											foreach ($lesson_ids as $day => $lessons) {
												foreach ($lessons as $lessonIDs=>$name) {
													if (array_key_exists($lessonIDs, $booked_lessons[$row->cartID][$row->$field])) {
														foreach ($booked_lessons[$row->cartID][$row->$field][$lessonIDs] as $day => $session) {
															$days_booked[$day] = $session;
														}
													}
												}
											}
											?><td class="has_icon register_toggle"><?php
											if (array_key_exists($lesson_info->day, $days_booked)) {
												if ($days_booked[$lesson_info->day]['attended'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if ($days_booked[$lesson_info->day]['attended'] == 1) {
													?><a href='<?php echo site_url('bookings/participants/unattend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-success btn-sm' title="Attended" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
													if($days_booked[$lesson_info->day]['attend_time'] != null)
															echo date("d-m-Y H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
													echo "</span>";
												} else {
													?><a href='<?php echo site_url('bookings/participants/attend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Attending" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
													if($days_booked[$lesson_info->day]['attend_time'] != null)
															echo date("d-m-Y H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
													echo "</span>";
												}
												?></td><?php

											}?>
											<td class="has_icon register_toggle"><?php

											if (array_key_exists($lesson_info->day, $days_booked)) {
												if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
													echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='".$row->pin."' />";
												else
													echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='0' />";
												if ($days_booked[$lesson_info->day]['signout'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if ($days_booked[$lesson_info->day]['signout'] == 1) {
													?>
													<a href='<?php echo site_url('bookings/participants/notsignout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-success btn-sm' id="click_<?php echo $days_booked[$lesson_info->day]['sessionID']?>" data-id = "<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>" title="NotSignout">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
													<?php
													if($days_booked[$lesson_info->day]['signout_time'] != null)
														echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
													echo "</span>";
												} else {

													?>
													<a href='<?php echo site_url('bookings/participants/signout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-warning btn-sm' id="click_<?php echo $days_booked[$lesson_info->day]['sessionID']?>" data-id = "<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>" title="Signout">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
													<?php
														if($days_booked[$lesson_info->day]['signout_time'] != null)
															echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
														echo "</span>";

												}
												?></td><?php

											}
										}
									}
									?>
								</tr>
								<?php
							}
						}
						if ($print_view === 2) {
							if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup', 'adults_children'))) {
								$cols -= 2;
							}
							for ($i = 0; $i < 30; $i++) {
								?><tr><?php
								for ($col = 0; $col < $cols; $col++) {

									?><td class="<?php echo (!in_array($col, $array)?'':'') ?>">&nbsp;</td><?php
								}
								?></tr><?php
							}
						}
						?>
						</tbody>
					</table>

					<!-- For Mobile View -->
					<?php if($print_view !== 1){ ?>
					<table class='table table-striped table-bordered visible-xs' id="participants_indivisual1">
						<thead>
						<tr>
							<th class=""></th>
							<?php
							$where = array(
								'lessonID' => $lessonID,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->from('bookings_lessons')->where($where)->get();
							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$lesson_info = $row;
								}
								if ($booking_info->type == 'booking') {
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$day = strtolower(date("l", strtotime($date)));
										if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
											?><th class="<?php echo (str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'' ?> mobilescreen" id="th1_<?php echo str_replace("/","",mysql_to_uk_date($date)) ?>" colspan="2"><?php echo mysql_to_uk_date($date); ?></th><?php

										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}

								} else {
									?><th colspan="2"><?php echo ucwords($lesson_info->day); ?></th><?php
								}
							}
							?>
						</tr>
						<tr>
							<th class="">Name</th>
							<?php
							// get session day
							$where = array(
								'lessonID' => $lessonID,
								'accountID' => $this->auth->user->accountID
							);
							$res = $this->db->from('bookings_lessons')->where($where)->get();

							if ($res->num_rows() > 0) {
								foreach ($res->result() as $row) {
									$lesson_info = $row;
								}
								if ($booking_info->type == 'booking') {
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$day = strtolower(date("l", strtotime($date)));
										if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
											?><th class="<?php echo (str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'' ?> mobilescreen" id="th2_<?php echo str_replace("/","",mysql_to_uk_date($date)) ?>">Sign In</th>
											<th class="<?php echo (str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'' ?> mobilescreen" id="th3_<?php echo str_replace("/","",mysql_to_uk_date($date)) ?>">Sign Out</th><?php

											if(!in_array(($cols), $array))
												$array[] = $cols;
											if(!in_array(($cols + 1), $array))
												$array[] = $cols + 1;
											$cols += 2;
										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}

								} else {
									?><th>Sign In</th>
									<th>Sign Out</th><?php

									if(!in_array(($cols), $array))
										$array[] = $cols;
									if(!in_array(($cols + 1), $array))
										$array[] = $cols + 1;
									$cols += 2;
								}
							}
							?>
						</tr>
						</thead>
						<tbody>
						<?php

						if (count($items) > 0) {
							$count = 0;
							foreach ($items as $itemNumber => $row) {
								$count++;
								if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
									$medical = $row->medical;
								} else {
									$medical = $row->contact_medical;
								}
								$medical = ($medical != '' && $medical != null)?'medical':'';

								$paid = '';
								if ($row->participant_total == 0) {
									$paid = "";
								} else if ($row->participant_balance == 0) {
									$paid = "";
								} else {
									$paid = "paymentdue";
								}

								$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
								if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }

								if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
									$field = 'contactID';
								} else {
									if ($booking_info->register_type=="adults_children") {
										$field = !empty($items[$itemNumber]->childID) ? "childID" : "contactID";
									}
									else {
										$field = 'childID';
									}
								}
								$sign = '';
								$signout = '';
								if (isset($lesson_info)) {
									if ($booking_info->type == 'booking') {
										$date = $block_info->startDate;
										while (strtotime($date) <= strtotime($block_info->endDate)) {
											$day = strtolower(date("l", strtotime($date)));
											if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
												if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
													if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 'staff') {

													}else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 1) {
														$sign .= ' signin'.date("dmY",strtotime($date));
													}else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == null) {

													}else{
														$sign .= ' notsignin'.date("dmY",strtotime($date));
													}

													if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 'staff') {

													}else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 1) {
														$signout .= ' signout'.date("dmY",strtotime($date));
													}else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == null) {

													}else{
														$signout .= ' notsignout'.date("dmY",strtotime($date));
													}
												}
											}
											$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
										}
									}else{
										$days_booked = array();
										foreach ($lesson_ids as $day => $lessons) {
											foreach ($lessons as $lessonIDs=>$name) {
												if (array_key_exists($lessonIDs, $booked_lessons[$row->cartID][$row->$field])) {
													foreach ($booked_lessons[$row->cartID][$row->$field][$lessonIDs] as $day => $session) {
														$days_booked[$day] = $session;
													}
												}
											}
										}

										if (array_key_exists($lesson_info->day, $days_booked)) {
											if ($days_booked[$lesson_info->day]['attended'] == 'staff') {
											} else if ($days_booked[$lesson_info->day]['attended'] == 1) {
												$sign .= ' signin'.ucwords($lesson_info->day);
											} else {
												$sign .= ' notsignin'.ucwords($lesson_info->day);
											}
										}
										if (array_key_exists($lesson_info->day, $days_booked)) {
											if ($days_booked[$lesson_info->day]['signout'] == 'staff') {
											}else if ($days_booked[$lesson_info->day]['signout'] == 1) {
												$signout .= ' signout'.ucwords($lesson_info->day);
											}else {
												$signout .= ' notsignout'.ucwords($lesson_info->day);
											}
										}
									}
								}

								?>
								<tr id="tr_<?php echo $count ?>" class="<?php echo $medical ?> <?php echo $paid ?> <?php echo $sign ?> <?php echo $signout ?>">

									<td>
									<?php
									if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										$row->contact_first = $row->booker_first;
										$row->contact_last = $row->booker_last;
										$row->contact_title = $row->booker_title;
										echo "<a href='".site_url('bookings/participants/viewdetail/' .$blockID."/".$lessonID."/".$row->childID)."' >".trim($row->child_first . ' ' . $row->child_last)."</a>";

									}else{

										if ($booking_info->register_type === "adults_children") {
											if($row->contactID != ''){
												echo "<a href='".site_url('bookings/participants/viewdetail/' .$blockID."/".$lessonID."/".$row->contactID)."' >";
												echo $row->contact_first . ' ' . $row->contact_last;
												if (!empty($row->contact_title)) {
													echo ' (' . ucwords($row->contact_title) . ')';
												}
												echo "</a>";
											}else{
												echo "<a href='".site_url('bookings/participants/viewdetail/' .$blockID."/".$lessonID."/".$row->childID)."' >";
												echo $row->child_first . ' ' . $row->child_last;
												if (!empty($row->child_title)) {
													echo ' (' . ucwords($row->child_title) . ')';
												}
												echo "</a>";
											}
										}else {
											echo "<a href='".site_url('bookings/participants/viewdetail/' .$blockID."/".$lessonID."/".$row->contactID)."' >";
											echo $row->contact_first . ' ' . $row->contact_last;
											if (!empty($row->contact_title)) {
												echo ' (' . ucwords($row->contact_title) . ')';
											}
											echo "</a>";
										}
									}
									echo "</td>";
									if (isset($lesson_info)) {
										if ($booking_info->type == 'booking') {
											$date = $block_info->startDate;
											while (strtotime($date) <= strtotime($block_info->endDate)) {
												$day = strtolower(date("l", strtotime($date)));
												if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
													?><td class="has_icon register_toggle mobilescreen <?php echo (str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'' ?>" id="<?php echo $count ?>td1_<?php echo str_replace("/","",mysql_to_uk_date($date)) ?>"><?php
													if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
														if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 'staff') {
															?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																		<i class='far fa-user'></i>
																	</span><?php
														} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 1) {
															?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-success btn-sm' data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" title="Attended">
																<i class='far fa-check'></i>
															</a><br />
															<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
															echo "</span>";
														} else {
															?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Attending" data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>">
																<i class='far fa-times'></i>
															</a><br />
															<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
															echo "</span>";

														}
													}
													?></td><?php

													?><td class="has_icon register_toggle mobilescreen <?php echo (str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'' ?>" id="<?php echo $count ?>td2_<?php echo str_replace("/","",mysql_to_uk_date($date)) ?>"><?php

													if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
														if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
															echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='".$row->pin."' />";
														else
															echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='0' />";
														if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 'staff') {
															?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																		<i class='far fa-user'></i>
																	</span><?php
														} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 1) {
															?>
															<a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' id="click_mini_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
																<i class='far fa-check'></i>
															</a><br />
															<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
															echo "</span>";

														} else {
															?>
															<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' id="click_mini_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="Signout">
																<i class='far fa-times'></i>
															</a><br />
															<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
															if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
																echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
															echo "</span>";
														}
													}
													?></td><?php
												}
												$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
											}
										} else {

											$days_booked = array();
											foreach ($lesson_ids as $day => $lessons) {
												foreach ($lessons as $lessonID=>$name) {
													if (array_key_exists($lessonID, $booked_lessons[$row->cartID][$row->$field])) {
														foreach ($booked_lessons[$row->cartID][$row->$field][$lessonID] as $day => $session) {
															$days_booked[$day] = $session;
														}
													}
												}
											}
											?>

											<?php
											if (array_key_exists($lesson_info->day, $days_booked)) {
												?>
												<td class="has_icon register_toggle mobilescreen <?php echo ($lesson_info->day != $firstdate)?'d-none':'' ?>" id="<?php echo $count ?>td1_<?php echo $lesson_info->day ?>">
												<?php
												if ($days_booked[$lesson_info->day]['attended'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if ($days_booked[$lesson_info->day]['attended'] == 1) {
													?><a href='<?php echo site_url('bookings/participants/unattend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-success btn-sm' title="Attended" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
													if($days_booked[$lesson_info->day]['attend_time'] != null)
															echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
													echo "</span>";
												} else {
													?><a href='<?php echo site_url('bookings/participants/attend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Attending" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
													if($days_booked[$lesson_info->day]['attend_time'] != null)
															echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
													echo "</span>";
												}
												?></td><?php

											}?>


											<?php

											if (array_key_exists($lesson_info->day, $days_booked)) {
												?>
												<td class="has_icon register_toggle mobilescreen <?php echo ($lesson_info->day != $firstdate)?'d-none':'' ?>" id="<?php echo $count ?>td2_<?php echo $lesson_info->day ?>">
												<?php
												if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
													echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='".$row->pin."' />";
												else
													echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='0' />";
												if ($days_booked[$lesson_info->day]['signout'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if ($days_booked[$lesson_info->day]['signout'] == 1) {
													?>
													<a href='<?php echo site_url('bookings/participants/notsignout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' id="click_mini_<?php echo $days_booked[$lesson_info->day]['sessionID']?>" class='btn btn-warning btn-sm' data-id = "<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>" title="NotSignout">
														<i class='far fa-check'></i>
													</a><br /><span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
													<?php
													if($days_booked[$lesson_info->day]['signout_time'] != null)
														echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
													echo "</span>";

												} else {
													?>
													<a href='<?php echo site_url('bookings/participants/signout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' id="click_mini_<?php echo $days_booked[$lesson_info->day]['sessionID']?>" class='btn btn-warning btn-sm' data-id = "<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>" title="Signout">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
														if($days_booked[$lesson_info->day]['signout_time'] != null)
															echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
													echo "</span>";

												}
												echo "</td>";
											}
										}
									}
									?>
								</tr>
								<?php
							}
							echo "<input type='hidden' name='count' id='count' value='".$count."' />";
						}

						?>
						</tbody>
					</table>
					<?php } ?>
				</div>
			</div>
		</div>



		<?php echo form_close(); ?>
		<?php
	}
}else{
	echo "
	<div style='margin-bottom:25px;'>
		<a href='".site_url('bookings/participants/print/' .$blockID."/".$lessonID)."'>Back</a>
	</div>";

	if (count($items) > 0) {
		$count = 0;
		foreach ($items as $itemNumber => $row) {
			if($row->childID == $familyID || $row->contactID == $familyID){
				echo "<div style='text-align:center; margin-bottom:20px;'>";
				$image_data = @unserialize($row->child_profile_pic);
				if ($image_data !== FALSE) {
					echo "<img src='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$row->childID)."' width='150px' />
						<br />";
				}else{
					$image_data = @unserialize($row->profile_pic);
					if ($image_data !== FALSE) {
						echo "<img src='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$row->contactID)."' width='150px' />
							<br />";
					}
				}
				$contactName = '';
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$row->contact_first = $row->booker_first;
					$row->contact_last = $row->booker_last;
					$row->contact_title = $row->booker_title;
					echo "<b>".trim($row->child_first . ' ' . $row->child_last)."</b>";

				}else{
					if ($booking_info->register_type === "adults_children") {
						if($row->contactID != ''){
							echo $row->contact_first . ' ' . $row->contact_last;
							if (!empty($row->contact_title)) {
								echo ' (' . ucwords($row->contact_title) . ')';
							}
						}else{
							echo $row->child_first . ' ' . $row->child_last;
							if (!empty($row->child_title)) {
								echo ' (' . ucwords($row->child_title) . ')';
							}
						}
					}else {
						echo $row->contact_first . ' ' . $row->contact_last;
						if (!empty($row->contact_title)) {
							echo ' (' . ucwords($row->contact_title) . ')';
						}
					}
				}
				if ($booking_info->register_type === "adults_children") {
					if($row->contactID != ''){
						$contactName = $row->contact_first . ' ' . $row->contact_last;
						if (!empty($row->contact_title)) {
							$contactName .= ' (' . ucwords($row->contact_title) . ')';
						}
					}else{
						$contactName = $row->child_first . ' ' . $row->child_last;
						if (!empty($row->child_title)) {
							$contactName .= ' (' . ucwords($row->child_title) . ')';
						}
					}
				}else {

					$contactName = $row->contact_first . ' ' . $row->contact_last;
					if (!empty($row->contact_title)) {
						$contactName .= ' (' . ucwords($row->contact_title) . ')';
					}
				}


				$age = '';

				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$age = calculate_age($row->dob);
				}

				if ($booking_info->register_type === "adults_children") {
					if($row->contactID != ''){
						$age = calculate_age($row->contact_dob);
					}else{
						$age = calculate_age($row->child_dob);
					}
				}

				if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$age = calculate_age($row->contact_dob);
				}

				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$medical = $row->medical;
				} else {
					$medical = $row->contact_medical;
				}

				$numbers = array();
				// use contact phone if set, else booker
				if (!empty($row->contactID)) {
					if (!empty($row->mobile)) {
						$numbers[] = $row->mobile;
					}
					if (!empty($row->phone)) {
						$numbers[] = $row->phone;
					}
					if (!empty($row->workPhone)) {
						$numbers[] = $row->workPhone;
					}
				} else {
					if (!empty($row->booker_mobile)) {
						$numbers[] = $row->booker_mobile;
					}
					if (!empty($row->booker_phone)) {
						$numbers[] = $row->booker_phone;
					}
					if (!empty($row->booker_workPhone)) {
						$numbers[] = $row->booker_workPhone;
					}
				}
				$photo = '';
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					if ($row->type == 'child') {
						if($row->photoConsent == 1) {
							$photo = "<span class='btn btn-success btn-sm no-action' style='padding:0' title='Yes'>
											<i class='far fa-check'></i>
										</span>";
						} else {
							$photo = "<span class='btn btn-danger btn-sm no-action' style='padding:0' title='No'>
											<i class='far fa-times'></i>
										</span>";
						}
					}
				}
				$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
				if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }

				if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$field = 'contactID';
				} else {
					if ($booking_info->register_type=="adults_children") {
						$field = !empty($items[$itemNumber]->childID) ? "childID" : "contactID";
					}
					else {
						$field = 'childID';
					}
				}

				$notes = "";
				if($prefix == "child"){
					$notes = $row->child_notes;
				}else if($prefix == "contact"){
					$notes = $row->contact_notes;
				}

				$monitor = '';
				for($i=1;$i<=20;$i++) {
					if (!empty($booking_info->{"monitoring".$i})) {
						if ($booking_info->{"monitoring".$i."_entry_type"}=="2") {
							$monitor.="<p style='padding:2px'><a href='javascript:void(0)' style='text-decoration:none; color:#000' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\")'><b>".$booking_info->{"monitoring".$i}.":</b> </a>".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i} : "")."</p>";
						}else {
						}
					}
				}
				$j = 0;
				for($i=1;$i<=20;$i++) {
					if (!empty($booking_info->{"monitoring".$i})) {
						if ($booking_info->{"monitoring".$i."_entry_type"}=="2") {
						}else {
							$j++;
							if($j > 2){
								if($j == 3)
									$monitor.= '<p style="text-align:center; font-size:20px" id="collapse"><a href="javascript:void(0)" onClick="hide_show(\'collapse\')"><b><i class="far fa-angle-down"> </i> </b></a></p>';
								$monitor.="<p style='padding:2px' class='expand d-none'><a href='javascript:void(0)' style='text-decoration:none' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\",\"".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/".$i)."\")'><b><i class='far fa-circle'></i>&nbsp;&nbsp;".$booking_info->{"monitoring".$i}.":</b></a> ".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."</p>";
							}else{
								$monitor.="<p style='padding:2px'><a href='javascript:void(0)' style='text-decoration:none' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\",\"".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/".$i)."\")'><b><i class='far fa-circle'></i>&nbsp;&nbsp;".$booking_info->{"monitoring".$i}.":</b></a> ".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."</p>";
							}

						}
					}
				}
				$monitor.= '<p id="expand" style="text-align:center; font-size:20px" class="d-none"><a href="javascript:void(0)" onClick="hide_show(\'expand\')"><b><i class="far fa-angle-up"> </i> </b></a></p>';

				echo "</div>
				<hr style='margin:0 0 5% 0'>
				<div>
					<p style='padding:2px'> <b> Age: </b> ".$age."</p>
					<p style='padding:2px'> <b> Contact: </b> ".$contactName."</p>
					<p style='padding:2px'> <b> Telephone: </b> ".implode(", ", $numbers)."</p>
					<p style='padding:2px'> <b> Photo consent: </b> ".$photo."</p>
					<p style='padding:2px;'> <b> Medical: </b> ".$medical."</p>";
				echo $monitor;
				echo "<hr style='margin:5% 0 5% 0'>";
				//echo "<p style='padding:2px'> <b> Notes: </b> ".$notes." </p>";
				//echo "<hr style='margin:5% 0 5% 0'>";
				echo "</div>";
			}
		}
	}
	?>
	<table class='table table-striped table-bordered' style="margin-top:20px;" id="participants_indivisual">
		<thead>
			<tr>
				<th> Date </th>
				<th> Time </th>
				<th> Sign in </th>
				<th> Sign out </th>
			</tr>
		</thead>
		<tbody>
			<?php
			// get session day
			$where = array(
				'lessonID' => $lessonID,
				'accountID' => $this->auth->user->accountID
			);
			$res = $this->db->from('bookings_lessons')->where($where)->get();

			if ($res->num_rows() > 0) {
				foreach ($res->result() as $row) {
					$lesson_info = $row;
				}
				if ($booking_info->type == 'booking') {
					$date = $block_info->startDate;
					while (strtotime($date) <= strtotime($block_info->endDate)) {
						$day = strtolower(date("l", strtotime($date)));
						if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
							?><?php
						}
						$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
					}
				} else {

				}
			}



			if (count($items) > 0) {
				$count = 0;
				foreach ($items as $itemNumber => $row) {
					$count++;
					$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
					if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }

					if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
						$field = 'contactID';
					} else {
						if ($booking_info->register_type=="adults_children") {
							$field = !empty($items[$itemNumber]->childID) ? "childID" : "contactID";
						}
						else {
							$field = 'childID';
						}
					}
					if($familyID == $row->childID || $row->contactID == $familyID){
						if (isset($lesson_info)) {

							if ($booking_info->type == 'booking') {
								$date = $block_info->startDate;
								while (strtotime($date) <= strtotime($block_info->endDate)) {
									$day = strtolower(date("l", strtotime($date)));
									if ($day ==  $lesson_info->day && !isset($cancellations['lessonID'][$date])) {
										if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
											echo "<tr>
											<td>".mysql_to_uk_date($date)."</td>
											<td>".date("H:i",strtotime($lesson_info->startTime))."-".date("H:i",strtotime($lesson_info->endTime))."</td>";
											?><td class="has_icon register_toggle"><?php
											if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
												if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attended'] == 1) {
													?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-success btn-sm' data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" title="Attended">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
														echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
													echo "</span>";
												} else {
													?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-warning btn-sm' data-id="<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" title="Attending">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time1_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time'] != null)
														echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['attend_time']));
													echo "</span>";

												}
											}
											?></td><?php

											?><td class="has_icon register_toggle"><?php

											if (isset($booked_lessons[$row->cartID][$row->$field][$lessonID][$date])) {
												if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
													echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='".$row->pin."' />";
												else
													echo "<input type='hidden' id='hidden_".$booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']."' value='0' />";
												if ($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 'staff') {
													?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
																<i class='far fa-user'></i>
															</span><?php
												} else if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout'] == 1) {
													?>
													<a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
														echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
													echo "</span>";

												} else {
													?>
													<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" data-id = "<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="Signout">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time_<?php echo $booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time'] != null)
														echo date("H:i:s",strtotime($booked_lessons[$row->cartID][$row->$field][$lessonID][$date]['signout_time']));
													echo "</span>";

												}
											}
											?></td><?php
											echo "</tr>";
										}
									}

									$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
								}
							} else {
								$days_booked = array();
								foreach ($lesson_ids as $day => $lessons) {
									foreach ($lessons as $lessonID=>$name) {
										if (array_key_exists($lessonID, $booked_lessons[$row->cartID][$row->$field])) {
											foreach ($booked_lessons[$row->cartID][$row->$field][$lessonID] as $day => $session) {
												$days_booked[$day] = $session;
											}
										}
									}
								}
								?><td class="has_icon register_toggle"><?php
								if (array_key_exists($lesson_info->day, $days_booked)) {
									if ($days_booked[$lesson_info->day]['attended'] == 'staff') {
										?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
													<i class='far fa-user'></i>
												</span><?php
									} else if ($days_booked[$lesson_info->day]['attended'] == 1) {
										?><a href='<?php echo site_url('bookings/participants/unattend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-success btn-sm' title="Attended" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
											<i class='far fa-check'></i>
										</a><br />
										<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
										<?php
										if($days_booked[$lesson_info->day]['attend_time'] != null)
												echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
										echo "</span>";
									} else {
										?><a href='<?php echo site_url('bookings/participants/attend/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Attending" data-id="<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
											<i class='far fa-times'></i>
										</a><br />
										<span class="time1_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>"><?php
										if($days_booked[$lesson_info->day]['attend_time'] != null)
												echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['attend_time']));
										echo "</span>";
									}
									?></td><?php

								}?>
								<td class="has_icon register_toggle"><?php
								if (array_key_exists($lesson_info->day, $days_booked)) {
									if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
										echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='".$row->pin."' />";
									else
										echo "<input type='hidden' id='hidden_".$days_booked[$lesson_info->day]['sessionID']."' value='0' />";
									if ($days_booked[$lesson_info->day]['signout'] == 'staff') {
										?><span class='btn btn-success btn-sm btn-noclick' title="Staff">
													<i class='far fa-user'></i>
												</span><?php
									} else if ($days_booked[$lesson_info->day]['signout'] == 1) {
										?>
										<a href='<?php echo site_url('bookings/participants/notsignout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-success btn-sm' title="NotSignout">
											<i class='far fa-check'></i>
										</a><br />
										<span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
										<?php
										if($days_booked[$lesson_info->day]['signout_time'] != null)
											echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
										echo "</span>";

									} else {
										?>
										<a href='<?php echo site_url('bookings/participants/signout/' . $days_booked[$lesson_info->day]['sessionID']); ?>' class='btn btn-warning btn-sm' id="click_<?php echo $days_booked[$lesson_info->day]['sessionID']?>" title="Signout">
											<i class='far fa-times'></i>
										</a><br />
										<span class="time_<?php echo $days_booked[$lesson_info->day]['sessionID'] ?>">
										<?php
											if($days_booked[$lesson_info->day]['signout_time'] != null)
												echo date("H:i:s",strtotime($days_booked[$lesson_info->day]['signout_time']));
										echo "</span>";

									}
									?></td><?php

								}
							}
						}
					}
				}
			}
			?>
		</tbody>
	</table>

	<div class="modal fade" id="myModal1" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body">
					<div style="padding:5% 0;">
						<a href="javascript:void(0)" data-dismiss="modal" style="float:right; color:#000"> <i class="far fa-times-circle"> </i></a>
						<br />
						<input type="text" name="monitor_register_value_popup" style="width:100%;padding:5% 0; font-size:16px;" id="monitor_register_value_popup" />
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>
	<!-- Data Model for Profile Picture -->
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body" id="verification" style="padding:15% 0">
					<div style="">
						<span style="padding:0 4%">
							<a href="javascript:void(0)" onClick="pickupPin()" class="btn btn-primary" style="width:40%; padding:5% 0; font-size:20px; text-align: center"> Pickup Pin </a>
						</span>
						<span style="padding:0 4%">
							<a href="javascript:void(0)" onClick="singoutModel()"  class="btn btn-primary" style="width:40%; padding:5% 0; font-size:20px; text-align: center"> Sign Out </a>
						</span>

					</div>
				</div>
				<div id="verification_pin" style="display:none">
					<div class="modal-body">
						<div style="text-align:center; color:#fff; background-color:#000; padding: 2% 0">
							<h4 class="modal-title">Please enter pick up PIN to sign out</h4>
						</div>
						<br />
						<input type="hidden" name="familyID" id="familyID" />
						<input type="hidden" name="URL" id="URL" />
						<span id="errormsg" style="color:red; display:none"> Please enter a valid PIN <br /></span>
						<div style="padding:5% 0">
							<span style="margin:4%;">
								<input type="text" autofocus name="pin1" class="inputs" id="pin1" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin2" class="inputs" id="pin2" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin3" class="inputs" id="pin3" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin4" class="inputs" id="pin4" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
						</div>
					</div>
					<div class="modal-footer">
						<a href="javascript:void(0)" onClick="resetPin()" class="btn btn-default">CANCEL</button>
						<a href="javascript:void(0)" onClick="pinVerified()" class="btn btn-primary">OK</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" id="hidden_flag_skip" value="0" />
<?php
if ($print_view === 2 || $print_view === 3) {
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<script src="<?php echo $this->crm_library->asset_url('dist/js/components/print.js'); ?>"></script>

</body>
</html><?php
}
