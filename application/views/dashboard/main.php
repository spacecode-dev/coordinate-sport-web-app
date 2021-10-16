<?php
$current_lesson = $this->crm_library->get_current_lesson();

//need to check overall checkin status on current day, because lesson could be over but checkout should be available
$checked_in_status = $this->crm_library->get_checkin_status();
$current_checked_in_status = false;

if ($current_lesson) {
	// get times
	$start_time = $current_lesson->startTime;
	$end_time = $current_lesson->endTime;
	// check for times from staff
	if (!empty($current_lesson->staff_start_time)) {
		$start_time = $current_lesson->staff_start_time;
	}
	if (!empty($current_lesson->staff_end_time)) {
		$end_time = $current_lesson->staff_end_time;
	}
	// org
	$org_name = $current_lesson->org;
	if (!empty($current_lesson->block_org)) {
		$org_name = $current_lesson->block_org;
	}

	//checkin status for current lesson
	$current_checked_in_status = $this->crm_library->get_current_checkin_status($current_lesson);
}

if ($this->auth->has_features('lesson_checkins') && $checked_in_status == 'checked_in') {
	?><div class="alert alert-success">
	<h4><i class="far fa-map-marker-alt"></i> <span>Sessions Check-out</span></h4>
	<p><span>Check-out now</span> from your sessions.</p>
	<a href="<?php echo site_url('dashboard/checkout'); ?>" class="btn btn-success check-out">Check-out</a>
	</div><?php
} else if ($this->auth->has_features('lesson_checkins') && $current_lesson && $current_lesson->provisional != 1 && (in_array($checked_in_status, ['not_checked_in']) || in_array($current_checked_in_status, ['not_checked_in']))) {
	echo form_open();
	?><div class="alert alert-success">
		<h4><i class="far fa-map-marker-alt"></i> <span>Session Check-in</span></h4>
		<p><span>Check-in now</span> for your <em><?php echo substr($start_time, 0, 5) . ' to ' . substr($end_time, 0, 5); ?></em> session at <em><?php echo $org_name; ?></em>.</p>
		<a href="<?php echo site_url('dashboard/checkin'); ?>" class="btn btn-success check-in">Check-in</a>
	</div><?php
	echo form_close();
}

if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
	?><div class='dashboard-boxes' id="highlights" data-url="<?php echo site_url('dashboard/ajax/highlights'); ?>">
		<div class="results row">
			<div class="col-xs-4 col-sm-2">
				<p class="loading">Loading...</p>
			</div>
		</div>
	</div><?php
}
?>
<div class='row'>
	<div class="col-sm-8 mh-widget-outer">
		<div class="row">
			<div class='col-sm-12'>
				<?php
				display_messages();

				if ($is_birthday === TRUE) {
					?><div class="alert alert-success">
						<h4><i class="far fa-birthday-cake"></i> Happy Birthday <?php echo $this->auth->user->first; ?>!</h4>
						<p>Have a great day!</p>
					</div><?php
				}

				if ($this->auth->has_features('messages') && $unread_messages > 0) {
					?><div class="alert alert-info">
						<h4><i class="far fa-envelope"></i> New Message<?php if ($unread_messages != 1) { echo 's'; } ?></h4>
						<p>You have <?php echo $unread_messages; ?> unread message<?php if ($unread_messages != 1) { echo 's'; } ?> - <?php echo anchor('messages', 'Go to inbox'); ?></p>
					</div><?php
				}

				if (($this->auth->has_features('offer_accept') || $this->auth->has_features('offer_accept_manual')) && $session_offers > 0) {
					?><div class="alert alert-info">
						<h4><i class="far fa-check-square"></i> New Session Offer<?php if ($session_offers != 1) { echo 's'; } ?></h4>
						<p>You have <?php echo $session_offers; ?> new session offer<?php if ($session_offers != 1) { echo 's'; } ?> - <?php echo anchor('acceptance', 'Go to accept sessions'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('equipment') && $late_equipment > 0) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-futbol"></i> Overdue Equipment</h4>
						<p>You have equipment which is overdue. Please check the <?php echo anchor('dashboard/equipment#late_equipment', 'equipment'); ?> page to view what is outstanding.</p>
					</div><?php
				}

				if ($this->auth->has_features(array('bookings_timetable', 'bookings_timetable_own', 'bookings_timetable_confirmation')) && $confirmed_timetable !== TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-calendar-check"></i> Unconfirmed Timetable</h4>
						<p>You have not confirmed your timetable for <?php echo $confirmed_timetable_week; ?> week - <?php echo anchor($confirmed_timetable_link, 'Go to timetable'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('timesheets') && $unsubmitted_timesheets === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-clock"></i> Unsubmitted Timesheet(s)</h4>
						<p>You have one or more unsubmitted timesheets - <?php echo anchor('finance/timesheets/own', 'Go to timesheets'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('timesheets') && $unapproved_timesheet_items === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-clock"></i> Unapproved Timesheet Item(s)</h4>
						<p>You have one or more timesheet items to approve - <?php echo anchor('finance/approvals', 'Go to approvals'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('session_evaluations') && $unsubmitted_evaluations === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-clipboard"></i> Unsubmitted or Rejected Session Evaluations</h4>
						<p>You have one or more evaluations to submit - <?php echo anchor('evaluations', 'Go to evaluations'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('session_evaluations') && $unapproved_evaluations === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-clipboard"></i> Unapproved Evaluations</h4>
						<p>You have one or more evaluations to approve - <?php echo anchor('evaluations/approvals', 'Go to evaluations'); ?></p>
					</div><?php
				}

				if ($this->auth->has_features('resources') && $unread_policies === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-book"></i> Unread Policies</h4>
						<p>You have not read and accepted our policies. Please <?php echo anchor('#policies', 'scroll down'); ?> to confirm them.</p>
					</div><?php
				}

				if ($this->auth->has_features('safety') && $unread_safety === TRUE) {
					?><div class="alert alert-danger">
						<h4><i class="far fa-book"></i> Unread Safety Documents</h4>
						<p>You have not read and accepted all of your safety documents. Please <?php echo anchor('dashboard/safety#safety_unread_own', 'view them'); ?> to confirm them.</p>
					</div><?php
				}
				?>
			</div>
		</div>
		<?php
		$boxes = array();
		if ($this->auth->has_features('dashboard_bookings') && !in_array($this->auth->user->department, array('coaching', 'headcoach', 'fulltimecoach'))) {
			$boxes[] = '<div class="card card-custom card-compact" id="booking_alerts" data-url="' . site_url('dashboard/ajax/summary/bookings') . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-calendar-alt text-contrast"></i></span>
							<h3 class="card-label"><a href="' . site_url('dashboard/bookings') . '">Bookings</a></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
			</div>';
		}
		if ($this->auth->has_features('dashboard_staff') && !in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			$boxes[] = '<div class="card card-custom card-compact" id="staff_alerts" data-url="' . site_url('dashboard/ajax/summary/staff') . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-sitemap text-contrast"></i></span>
							<h3 class="card-label"><a href="' . site_url('dashboard/staff') . '">Staff</a></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
			</div>';
		}
		if ($this->auth->has_features(array('dashboard_participants', 'participants')) && !in_array($this->auth->user->department, array('coaching', 'headcoach', 'fulltimecoach'))) {

			$boxes[] = '<div class="card card-custom card-compact" id="participant_alerts" data-url="' . site_url('dashboard/ajax/summary/participants') . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-users text-contrast"></i></span>
							<h3 class="card-label"><a href="' . site_url('dashboard/participants') . '">' . $this->settings_library->get_label('participants') . '</a></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
			</div>';
		}
		if ($this->auth->has_features(array('dashboard_health_safety', 'safety'))) {
			$boxes[] = '<div class="card card-custom card-compact" id="safety_alerts" data-url="' . site_url('dashboard/ajax/summary/safety') . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-medkit text-contrast"></i></span>
							<h3 class="card-label"><a href="' . site_url('dashboard/safety') . '">Health &amp; Safety</a></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
			</div>';
		}
		if ($this->auth->has_features(array('dashboard_equipment', 'equipment'))) {
			$boxes[] = '<div class="card card-custom card-compact" id="equipment_alerts" data-url="' . site_url('dashboard/ajax/summary/equipment') . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-futbol text-contrast"></i></span>
							<h3 class="card-label"><a href="' . site_url('dashboard/equipment') . '">Equipment</a></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<div class="results">
							<p class="loading">Loading...</p>
						</div>
					</div>
			</div>';
		}
		if ($this->auth->has_features('dashboard_availability') && !in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
			$box = '<div class="card card-custom card-compact" id="availability">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-clock text-contrast"></i></span>
							<h3 class="card-label">Availability</h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body">
					' . form_open('dashboard/availability')
						. form_fieldset()
							. '<div class="form-group">'
								. form_label('Date <em>*</em>', 'date');
								$data = array(
									'name' => 'date',
									'id' => 'date',
									'class' => 'form-control datepicker',
									'value' => date('d/m/Y'),
									'maxlength' => 10
								);
								$box .= form_input($data)
							. '</div>
							<div class="form-group">'
								. form_label('Start Time <em>*</em>', 'startTimeH');
								$options = array();
								$h = 6;
								while ($h <= 23) {
									$h = sprintf("%02d",$h);
									$options[$h] = $h;
									$h++;
								}
								$box .= form_dropdown('startTimeH', $options, 13, 'id="startTimeH" class="form-control select2"');
								$options = array();
								$m = 0;
								while ($m <= 59) {
									$m = sprintf("%02d",$m);
									if ($m % 5 == 0) {
										$options[$m] = $m;
									}
									$m++;
								}
								$box .= form_dropdown('startTimeM', $options, '00', 'id="startTimeM" class="form-control select2"')
							. '</div>
							<div class="form-group">'
								. form_label('End Time <em>*</em>', 'endTimeH');
								$options = array();
								$h = 6;
								while ($h <= 23) {
									$h = sprintf("%02d",$h);
									$options[$h] = $h;
									$h++;
								}
								$box .= form_dropdown('endTimeH', $options, 14, 'id="endTimeH" class="form-control select2"');
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
								$box .= form_dropdown('endTimeM', $options, '00', 'id="endTimeM" class="form-control select2"')
							. '</div>
							<div class="form-group">'
								. form_label('Post Code', 'postcode');
								$data = array(
									'name' => 'postcode',
									'id' => 'postcode',
									'class' => 'form-control',
									'maxlength' => 8
								);
								$box .= form_input($data)
							. '<small class="text-muted form-text">If entered, travel time from previous/next session will be checked.</small></div>'
						. form_fieldset_close()
						. '<div class="form-group">
							<button class="btn btn-primary btn-submit" type="submit">
								Check Availability
							</button>
						</div>'
					. form_close()
				. '</div>
			</div>';
			$boxes[] = $box;
		}
		// custom widgets
		$custom_widgets = array();
		for ($i=1; $i <= 3; $i++) {
			$widget_title = $this->settings_library->get('dashboard_custom_widget_' . $i . '_title');
			$widget_html = $this->settings_library->get('dashboard_custom_widget_' . $i . '_html');
			if (!empty($widget_html)) {
				if (empty($widget_title)) {
					$widget_title = 'Custom Widget';
				}
				$box = '<div class="card card-compact card-custom" id="custom_widget_' . $i . '">
					<div class="card-header ui-sortable-handle">
						<div class="card-title">
							<span class="card-icon"><i class="far fa-cog text-contrast"></i></span>
							<h3 class="card-label">' . $widget_title . '</h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body">
						' . $widget_html . '
					</div>
				</div>';
				$custom_widgets[$i] = $box;
			}
		}
		$split_on = ceil(count($boxes)/2);
		?><div class="row">
			<div class="col-sm-6 widget_area mh-widget-inner"><?php
				$i = 0;
				foreach ($boxes as $key => $box) {
					if ($i == $split_on) {
						break;
					}
					echo $box;
					unset($boxes[$key]);
					$i++;
				}
				if (array_key_exists(1, $custom_widgets)) {
					echo $custom_widgets[1];
				}
			?></div>
			<div class="col-sm-6 widget_area mh-widget-inner"><?php
				foreach ($boxes as $box) {
					echo $box;
				}
				if (array_key_exists(2, $custom_widgets)) {
					echo $custom_widgets[2];
				}
			?></div>
		</div>
	</div>
	<div class="col-sm-4 widget_area mh-widget-outer">
		<?php
		if ($this->auth->has_features('dashboard_employee_of_month')) {
			if ($employee_of_month !== FALSE) {
				?><div class='card card-custom card-compact' id="employee_of_month">
					<div class='card-header ui-sortable-handle'>
						<div class="card-title flex-nowrap">
							<span class="card-icon"><i class='far fa-star text-contrast'></i></span>
							<h3 class="card-label">Employee of the Month</h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body">
						<?php
						echo '<p class="employee_of_month">';
						if (!empty($employee_of_month->id_photo_path)) {
							$data = array(
								'src' => site_url('attachment/staff-id/' . $employee_of_month->id_photo_path),
								'alt' => $employee_of_month->first . ' ' . $employee_of_month->surname,
								'height' => 170,
								'width' => 130
							);
							echo img($data) . '<br />';
						}
						echo $employee_of_month->first . ' ' . $employee_of_month->surname;
						echo '</p>';
						?>
					</div>
				</div><?php
			}
		}
		if ($this->auth->has_features(array('reports', 'staff_performance'))) {
			$search_args = array();
			$search_args['search'] = 'true';
			$search_args['is_active'] = 'yes';
			$search_args['exclude_non_delivery'] = 'yes';
			if (!empty($this->auth->user->brandID)) {
				$search_args['brand_id'] = $this->auth->user->brandID;
			}
			$top_staff = $this->reports_library->calc_performance('top', $search_args);
			if (count($top_staff) > 0) {
				?><div class='card card-custom card-compact' id="top_staff">
					<div class='card-header ui-sortable-handle'>
						<div class="card-title">
							<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
							<h3 class="card-label"><?php
							if (in_array($this->auth->user->department, array('directors', 'management'))) {
								echo '<a href="' . site_url('reports/performance') . '">';
							}
							echo 'Top ';
							if (!empty($this->auth->user->brand)) {
								echo $this->auth->user->brand . ' ';
							}
							echo 'Performers';
							if (in_array($this->auth->user->department, array('directors', 'management'))) {
								echo '</a>';
							}
							?></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body no-padding">
						<?php
						$data = array(
							'id' => 'top_staff',
							'items' => array()
						);
						$i = 1;
						foreach ($top_staff as $staff_name => $score) {
							$item_data = array();
							$item_data['text'] = $i . '. ' . $staff_name;
							$item_data['status'] = 'green';
							$item_data['link'] = NULL;
							$data['items'][] = $item_data;
							$i++;
						}

						$view = 'dashboard/items';
						$this->load->view($view, $data);
						?>
					</div>
				</div><?php
			}
		}
		if ($this->auth->has_features('dashboard_staff_birthdays') && $this->settings_library->get('dashboard_staff_birthdays') == 1) {
			?><div class='card card-custom card-compact' id="staff_birthdays" data-url="<?php echo site_url('dashboard/ajax/section/birthdays'); ?>">
				<div class='card-header ui-sortable-handle'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-birthday-cake text-contrast'></i></span>
						<h3 class="card-label">Staff Birthdays</h3>
					</div>
					<div class="card-toolbar">
						<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
						<i class="ki ki-arrow-down icon-nm"></i>
						</a>
					</div>
				</div>
				<div class="card-body no-padding">
					<div class="results">
						<p class="loading">Loading...</p>
					</div>
				</div>
			</div><?php
		}
		if (count($upcoming_events) > 0) {
			foreach ($upcoming_events as $typeID => $upcoming_event) {
				?><div class='card card-custom card-compact<?php if ($upcoming_event['data']->num_rows() == 0) { echo ' card-collapsed'; } ?>' id="upcoming_events_<?php echo $typeID; ?>">
					<div class='card-header ui-sortable-handle'>
						<div class="card-title">
							<span class="card-icon"><i class='far fa-calendar-alt text-contrast'></i></span>
							<h3 class="card-label"><?php echo $upcoming_event['label']; ?></h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
							<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body">
						<?php
						if ($upcoming_event['data']->num_rows() == 0) {
							?><div class="card-actions"><p>None upcoming.</p></div><?php
						} else {
							$data = array(
								'id' => NULL,
								'items' => array()
							);
							foreach ($upcoming_event['data']->result() as $row) {
								// loop through all days in booking range
								$date = $row->startDate;
								while (strtotime($date) <= strtotime($row->endDate)) {
									if (strtolower(date('l', strtotime($date))) == $row->day) {
										$item_data = array();
										$item_data['text'] = mysql_to_uk_date($date) . ' ' . substr($row->startTime, 0, 5) . ' to ' . substr($row->endTime, 0, 5) . ' - ' . $row->name;
										$item_data['status'] = 'amber';
										$item_data['link'] = NULL;
										$data['items'][] = $item_data;
									}
									$date = date("Y-m-d", strtotime("+1 day", strtotime($date)));
								}
							}

							$view = 'dashboard/items';
							$this->load->view($view, $data);
						}
						?>
					</div>
				</div><?php
			}
		}
		if ($this->auth->has_features('resources')) {
			?><div class="card card-custom card-compact<?php if ($unread_policies !== TRUE) { echo ' card-collapsed'; } ?>" id="policies">
				<div class='card-header ui-sortable-handle'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label">Policies</h3>
					</div>
					<div class="card-toolbar">
						<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
						<i class="ki ki-arrow-down icon-nm"></i>
						</a>
					</div>
				</div>
				<div class="card-body no-padding">
					<?php
					if (is_null($policies) || $policies->num_rows() == 0) {
						?><div class="alert alert-info" role="alert">
							No policies found
						</div><?php
					} else {
						$data = array(
							'items' => array()
						);
						foreach ($policies->result() as $row) {
							$item_data = array();
							$item_data['text'] = $row->name;
							$item_data['status'] = 'amber';
							if ($unread_policies !== TRUE ||
								(!($this->auth->user->accept_policies == "0000-00-00 00:00:00" || empty($this->auth->user->accept_policies)) && strtotime($row->modified)<=strtotime($this->auth->user->accept_policies))) {
								$item_data['status'] = 'green';
							}
							$item_data['link'] = site_url('attachment/files/' . $row->path);
							$item_data['target'] = '_blank';
							$data['items'][] = $item_data;
						}

						$view = 'dashboard/items';
						$this->load->view($view, $data);
						?><div class="card-actions"><?php
							if ($unread_policies === TRUE) {
								?><p><a href="<?php echo site_url('policies/confirm'); ?>" class="confirm">Confirm you have read and accept the above policies</a></p><?php
							} else {
								?><p>Policies confirmed on <?php echo mysql_to_uk_datetime($this->auth->user->accept_policies); ?></p><?php
							}
						?></div><?php
					}
					?>
				</div>
			</div><?php
		}
		?>
		<div class='card card-custom card-compact' id="your_tasks">
			<div class='card-header ui-sortable-handle'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Your Tasks</h3>
				</div>
				<div class="card-toolbar">
					<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
					</a>
				</div>
			</div>
			<div class="card-body todo-list">
				<?php echo form_open('tasks/new'); ?>
					<div class="input-group">
						<input class='form-control' id='todo_name' name='task' placeholder='Type your new todo here...' type='text' maxlength="100">
						<span class="input-group-btn input-group-append">
							<button class='btn btn-success' type='submit'>
								<i class='far fa-plus'></i>
							</button>
						</span>
					</div>
				<?php echo form_close(); ?>
				<div class="actions text-right card-actions">
					<a href="#" class="toggle_completed">Toggle Completed</a>
				</div>
				<ol class='item-list'>
					<?php
					if ($tasks->num_rows() > 0) {
						foreach ($tasks->result() as $task) {
							?><li class='item<?php if ($task->complete == 1) { echo " done"; } ?>'>
								<label class='check pull-left todo'>
									<input type='checkbox' data-action="<?php echo site_url('tasks/status/' . $task->taskID); if ($task->complete == 1) { echo '/uncomplete'; } else { echo '/complete'; } ?>"<?php if ($task->complete == 1) { echo " checked=\"checked\""; } ?>>
									<span><?php echo $task->task; ?></span>
								</label>
								<div class='actions pull-right'>
									<a class='text-info edit has-tooltip' data-placement='top' href='<?php echo site_url('tasks/edit/' . $task->taskID); ?>' title='Edit'>
										<i class='far fa-pencil'></i>
									</a>
									<a class='text-danger remove has-tooltip' data-placement='top' href='<?php echo site_url('tasks/remove/' . $task->taskID); ?>' title='Remove'>
										<i class='far fa-trash'></i>
									</a>
								</div>
							</li><?php
						}
					}
					?>
				</ol>
			</div>
		</div>
		<?php
		if (in_array($this->auth->user->department, array('management', 'directors'))) {
			?><div class='card card-custom card-compact' id="others_tasks">
				<div class='card-header ui-sortable-handle'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label">Other's Tasks</h3>
					</div>
					<div class="card-toolbar">
						<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
						<i class="ki ki-arrow-down icon-nm"></i>
						</a>
					</div>
				</div>
				<div class="card-body no-padding todo-list">
					<div class="card-actions">
						<a href="#" class="toggle_completed pull-right">Toggle Completed</a>
						<a href="<?php echo site_url('tasks/new/'); ?>">Add New</a>
					</div>
					<ol class='item-list'>
						<?php
						if ($tasks_others->num_rows() > 0) {
							foreach ($tasks_others->result() as $task) {
								?><li class='item<?php if ($task->complete == 1) { echo " done"; } ?>'>
									<label class='check pull-left todo'>
										<input type='checkbox' data-action="<?php echo site_url('tasks/status/' . $task->taskID); if ($task->complete == 1) { echo '/uncomplete'; } else { echo '/complete'; } ?>"<?php if ($task->complete == 1) { echo " checked=\"checked\""; } ?>>
										<span><abbr title="<?php echo $task->first . ' ' . $task->surname; ?>"><?php echo substr($task->first, 0, 1) . substr($task->surname, 0, 1); ?></abbr>: <?php echo $task->task; ?></span>
									</label>
									<div class='actions pull-right'>
										<a class='text-info edit has-tooltip' data-placement='top' href='<?php echo site_url('tasks/edit/' . $task->taskID); ?>' title='Edit'>
											<i class='far fa-pencil'></i>
										</a>
										<a class='text-danger remove has-tooltip' data-placement='top' href='<?php echo site_url('tasks/remove/' . $task->taskID); ?>' title='Remove'>
											<i class='far fa-trash'></i>
										</a>
									</div>
								</li><?php
							}
						}
						?>
					</ol>
				</div>
			</div><?php
		}
		if (array_key_exists(3, $custom_widgets)) {
			echo $custom_widgets[3];
		}
		?>
	</div>
</div>
<script>
	var dashboard_config = '<?php echo $dashboard_config; ?>';
</script>
