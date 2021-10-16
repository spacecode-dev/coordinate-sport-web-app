<?php
display_messages();
if ($search_fields["staff_id"] != NULL) {
	$data = array(
		'staffID' => $search_fields["staff_id"],
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'search-form', 'style' => 'display:none']); ?>
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
		<input type="hidden" id="year-value" name="year" value="<? echo $year ?>">
		<input type="hidden" id="week-value" name="week" value="<? echo $week ?>">
		<input type="hidden" id="own-value" name="own" value="<? echo $only_own ?>">
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' name="s" type="submit" value="search">
				<i class='far fa-search'></i> Search
			</button>
			<button class='btn btn-default' name="s" value="cancel">
				Cancel
			</button>
		</div>
	</div>
	<?php echo form_hidden('search', $search); ?>
<?php echo form_close(); ?>

<div id="results"></div>
<div class="row" id="timetable_desktop">
	<div class='col-sm-12'>
		<p><?php
		$search_extra = NULL;
		echo anchor($timetable_base . '/' . $prev_year . '/' . $prev_week, '&lt; Previous', ['week' => $prev_week, 'year' => $prev_year, 'class' => 'week_navigate']); ?> | <?php echo anchor($timetable_base . '/' . $next_year . '/' . $next_week, 'Next &gt;', ['week' => $next_week, 'year' => $next_year, 'class' => 'week_navigate']);
		if ($week != date('W') || $year != date('Y')) {
			echo ' | ' . anchor($timetable_base, 'Current Week', ['week' => date('W'), 'year' => date('Y'), 'class' => 'week_navigate']);
		}
		?></p>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered timetable'>
					<thead>
					<tr>
						<th></th>
						<?php
						$i = 0;
						foreach ($days as $day) {
							$i++;
							// if search by day, dont show other days
							if (!empty($search_fields['day']) && $search_fields['day'] != $day) {
								continue;
							}
							$date_obj = new DateTime();
							$date_obj->setISODate($year, $week, $i); //year , week num , day
							echo '<th>' . $date_obj->format('D jS M') . '</th>';
						}
						?>
					</tr>
					</thead>
					<tbody>
					<?php
					$tooltips = array();
					$counter = array();
					foreach ($time_slots as $slot => $label) {
						?><tr>
						<td><?php echo $label; ?></td>
						<?php
						$i = 0;
						foreach ($days as $day) {
							// if search by day, dont show other days
							if (!empty($search_fields['day']) && $search_fields['day'] != $day) {
								continue;
							}
							$flag = 0;
							$background_color = '';
							$tooltip = array();
							if(!isset($counter[$day]))
								$counter[$day] = 0;
							if($search_fields["staff_id"] != null && $search_fields["staff_id"] != ""){		
								$i++;
								$date = date("Y-m-d",strtotime($year."W".str_pad($week,2,0,STR_PAD_LEFT).$i));
								$new_date = date("Y-m-d H:i:s", strtotime($date." ".$slot.":00"));
								$temp_start_date = $date." 06:00:00";
								$temp_end_date = $date." 24:00:00";
								if(count($availability_exceptions) > 0){
									foreach($availability_exceptions as $exceptions){
										if($exceptions->from <= $new_date && $exceptions->to >= $new_date){
											$startdate = $exceptions->from;
											$enddate = $exceptions->to;
											if($exceptions->from <= $temp_start_date)
												$startdate = $temp_start_date;
											if($exceptions->to >= $temp_end_date)
												$enddate = $temp_end_date;
											$diff = strtotime($enddate) - strtotime($startdate);
											$hours = $diff / ( 60 * 60 );
											$background_color = 'rowspan="'.$hours.'" style="cursor:pointer" class="exceptions stripe-2" data-url='.site_url("staff/availability/".$exceptions->staffID."/exceptions/edit/".$exceptions->exceptionsID).' data-tooltip="tooltip-'.$exceptions->exceptionsID.'"';
											$tooltip[] = "<strong>Reason: </strong>".(empty($exceptions->reason)?$exceptions->note:ucfirst($exceptions->reason));
											$tooltips[] = '<div class="tooltip-' . $exceptions->exceptionsID . '">' . implode('<br />', $tooltip) . '</div>';
											$flag = 1;
											$counter[$day]++;
										}
									}
								}
							}
							if($counter[$day] != 1 && $flag == 1)
								continue;
							echo "<td ".$background_color.">";
							if (array_key_exists($day, $lessons) && array_key_exists($slot, $lessons[$day]) && $flag != 1) {
								if (count($lessons[$day][$slot]) > 0) {
									ksort($lessons[$day][$slot]);
									foreach ($lessons[$day][$slot] as $lesson) {
										if (empty($lesson['activityID'])) {
											$lesson['activityID'] = 'other';
										}
										?><a class="label label-inline <?php echo $lesson['label_classes']; ?> label-activity-<?php echo $lesson['activityID']; ?> label-brand-<?php echo $lesson['brandID']; ?>" style="<?php echo label_style($lesson['colour']); ?>" data-region="<?php echo $lesson['region']; ?>" data-area="<?php echo $lesson['area']; ?>" data-tooltip="tooltip-<?php echo $lesson['id']; ?>" data-length="<?php echo $lesson['length']; ?>" href="<?php echo $lesson['link']; ?>"><?php
										if ($lesson['booking_type'] == 'booking' && $lesson['project'] != 1) {
											echo $lesson['org'];
										} else {
											echo $lesson['event'];
										}
										if ($lesson['has_block_org'] == TRUE) {
											echo ' (' . $lesson['org'] . ')';
										}
										?></a><?php
										// build tooltip
										$tooltip = array();
										$tooltip[] = '<strong>Block:</strong> ' . $lesson['block'];
										if ($only_own !== TRUE) {
											$tooltip[] = '<strong>Block Dates:</strong> ' . mysql_to_uk_date($lesson['startDate']) . '-' . mysql_to_uk_date($lesson['endDate']);
										}
										if ($lesson['booking_type'] == 'booking') {
											$tooltip[] = '<strong>' . ucwords($lesson['org_type']) . ':</strong> ' . $lesson['org'];
										} else {
											$tooltip[] = '<strong>Venue:</strong> ' . $lesson['org'];
										}
										if (!empty($lesson['address'])) {
											$tooltip[] = '<strong>Address:</strong> ' . $lesson['address'];
										}
										if (!empty($lesson['activity_group'])) {
											$tooltip[] = '<strong>Activity/Group:</strong> ' . $lesson['activity_group'];
										}
										$tooltip[] = '<strong>Time:</strong> ' . $lesson['time'];
										if (count($lesson['headcoaches'])) {
											$label = $this->settings_library->get_staffing_type_label('head');
											if (count($lesson['headcoaches']) != 1) {
												$label = Inflect::pluralize($label);
											}
											$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['headcoaches']);
										}
										if (count($lesson['leadcoaches'])) {
											$label = $this->settings_library->get_staffing_type_label('lead');
											if (count($lesson['leadcoaches']) != 1) {
												$label = Inflect::pluralize($label);
											}
											$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['leadcoaches']);
										}
										if (count($lesson['assistantcoaches'])) {
											$label = $this->settings_library->get_staffing_type_label('assistant');
											if (count($lesson['assistantcoaches']) != 1) {
												$label = Inflect::pluralize($label);
											}
											$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['assistantcoaches']);
										}
										if (count($lesson['participants'])) {
											$label = $this->settings_library->get_staffing_type_label('participant');
											if (count($lesson['participants']) != 1) {
												$label = Inflect::pluralize($label);
											}
											$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['participants']);
										}
										if (count($lesson['observers'])) {
											$label = $this->settings_library->get_staffing_type_label('observer');
											if (count($lesson['observers']) != 1) {
												$label = Inflect::pluralize($label);
											}
											$tooltip[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['observers']);
										}
										if (!empty($lesson['offer_accept_status'])) {
											$tooltip[] = '<strong>Offer & Accept:</strong> ' . $lesson['offer_accept_status'];
										}
										if (!empty($lesson['offered_to'])) {
											$tooltip[] = '<strong>Offered To:</strong> ' . $lesson['offered_to'];
										}
										// show participants for super users only
										$tooltip[] = '<strong>Participants:</strong> ' . $lesson['participants_actual'] . '/' . $lesson['participants_target'];
										$tooltips[] = '<div class="tooltip-' . $lesson['id'] . '">' . implode('<br />', $tooltip) . '</div>';
									}
								}
							}
							?></td><?php
						}
						?>
						</tr><?php
					}
					?>
					<tr class="day_hours">
						<td>Hours</td>
						<?php
						foreach ($days as $day) {
							// if search by day, dont show other days
							if (!empty($search_fields['day']) && $search_fields['day'] != $day) {
								continue;
							}
							echo '<td>' . $day_hours[$day] . '</td>';
						}
						?>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<div class='col-sm-12' id="timetable_mobile">
	<p><?php echo anchor($timetable_base . '/' . $prev_year . '/' . $prev_week . $search_extra, '&lt; Previous Week'); ?> | <?php echo anchor($timetable_base . '/' . $next_year . '/' . $next_week . $search_extra, 'Next Week &gt;');
	if ($week != date('W') || $year != date('Y')) {
		echo ' | ' . anchor($timetable_base, 'Current Week');
	}
	?></p>
	<?php
	$i = 0;
	foreach ($days as $day) {
		$i++;
		// if search by day, dont show other days
		if (!empty($search_fields['day']) && $search_fields['day'] != $day) {
			continue;
		}

		$date_obj = new DateTime();
		$date_obj->setISODate($year, $week, $i); //year , week num , day
		echo '<h3>' . $date_obj->format('D jS M') . '</h3>';

		$day_lesson_count = 0;

		if (array_key_exists($day, $lessons)) {

			if (count($lessons[$day]) > 0) {
				// sort by time
				ksort($lessons[$day]);

				foreach ($lessons[$day] as $slot => $lesson_list) {
					foreach ($lesson_list as $lesson) {
						$day_lesson_count++;
						?><div class='card card-custom card-collapsed card-compact'>
							<div class='card-header' style="<?php echo label_style($lesson['colour']); ?>">
								<div class="card-title">
									<h3 class="card-label">
										<?php
										if ($lesson['booking_type'] == 'booking' && $lesson['project'] != 1) {
											echo anchor($lesson['link'], $lesson['time'] . ' - ' . $lesson['org'], 'style="' . link_style($lesson['colour']) . '"');
										} else {
											echo anchor($lesson['link'], $lesson['time'] . ' - ' . $lesson['event'], 'style="' . link_style($lesson['colour']) . '"');
										}
										?>
									</h3>
								</div>
								<div class="card-toolbar">
									<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
									<i class="ki ki-arrow-down icon-nm"></i>
									</a>
								</div>
							</div>
							<div class="card-body">
								<?php
								// session info
								$lesson_info = array();
								if ($only_own !== TRUE) {
									$lesson_info[] = '<strong>Block:</strong> ' . $lesson['block'];
									$lesson_info[] = '<strong>Block Dates:</strong> ' . mysql_to_uk_date($lesson['startDate']) . '-' . mysql_to_uk_date($lesson['endDate']);
								}
								if ($lesson['booking_type'] == 'booking') {
									$lesson_info[] = '<strong>' . ucwords($lesson['org_type']) . ':</strong> ' . $lesson['org'];
								} else {
									$lesson_info[] = '<strong>Venue:</strong> ' . $lesson['org'];
								}
								if (!empty($lesson['address'])) {
									$lesson_info[] = '<strong>Address:</strong> ' . $lesson['address'];
								}
								if (!empty($lesson['activity_group'])) {
									$lesson_info[] = '<strong>Activity/Group:</strong> ' . $lesson['activity_group'];
								}
								$lesson_info[] = '<strong>Time:</strong> ' . $lesson['time'];
								if (count($lesson['headcoaches'])) {
									$label = $this->settings_library->get_staffing_type_label('head');
									if (count($lesson['headcoaches']) != 1) {
										$label = Inflect::pluralize($label);
									}
									$lesson_info[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['headcoaches']);
								}
								if (count($lesson['leadcoaches'])) {
									$label = $this->settings_library->get_staffing_type_label('lead');
									if (count($lesson['leadcoaches']) != 1) {
										$label = Inflect::pluralize($label);
									}
									$lesson_info[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['leadcoaches']);
								}
								if (count($lesson['assistantcoaches'])) {
									$label = $this->settings_library->get_staffing_type_label('assistant');
									if (count($lesson['assistantcoaches']) != 1) {
										$label = Inflect::pluralize($label);
									}
									$lesson_info[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['assistantcoaches']);
								}
								if (count($lesson['participants'])) {
									$label = $this->settings_library->get_staffing_type_label('participant');
									if (count($lesson['participants']) != 1) {
										$label = Inflect::pluralize($label);
									}
									$lesson_info[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['participants']);
								}
								if (count($lesson['observers'])) {
									$label = $this->settings_library->get_staffing_type_label('observer');
									if (count($lesson['observers']) != 1) {
										$label = Inflect::pluralize($label);
									}
									$lesson_info[] = '<strong>' . $label . ':</strong> ' . implode(', ', $lesson['observers']);
								}
								if (!empty($lesson['offer_accept_status'])) {
									$lesson_info[] = '<strong>Offer/Accept Status:</strong> ' . $lesson['offer_accept_status'];
								}
								echo implode('<br />', $lesson_info);
								?>
							</div>
						</div><?php
					}
				}
			}
		}

		if ($day_lesson_count == 0) {
			?><div class="alert alert-info">
				<p>No sessions.</p>
			</div><?php
		}
	}

	?>
</div>

<div class="tooltips">
	<?php echo implode("\n", $tooltips); ?>
</div>
