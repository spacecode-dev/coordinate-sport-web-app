<?php
display_messages();
$form_classes = 'card card-custom card-search';
$d_none_class = '';
if ($projects === TRUE) {
	$d_none_class = 'd-none';
}
echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
	<div class="card-body search-filters">
		<div class='row'>
			<div class='col-sm-2 <?php echo $d_none_class;?>'>
				<p>
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_from',
					'id' => 'field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from'],
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2 <?php echo $d_none_class;?>'>
				<p>
					<strong><label for="field_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to'],
				);
				echo form_input($data);
				?>
			</div>
			<?php
			switch ($type) {
				case 'booking':
					?><div class='col-sm-2 <?php echo $d_none_class;?>'>
						<p>
							<strong><label for="field_org_id"><?php echo $this->settings_library->get_label('customer'); ?></label></strong>
						</p>
						<?php
						$options = array(
							'' => 'Select'
						);

						foreach ($orgs as $row) {
							$options[$row->orgID] = $row->name;
						}
						echo form_dropdown('search_org_id', $options, $search_fields['org_id'], 'id="field_org_id" class="select2 form-control"');
						?>
					</div>
					<div class='col-sm-2 <?php echo $d_none_class;?>'>
						<p>
							<strong><label for="field_org_type"><?php echo $this->settings_library->get_label('customer'); ?> Type</label></strong>
						</p>
						<?php
						$options = array(
							'' => 'Select',
							'school' => 'School',
							'organisation' => 'Organisation'
						);
						echo form_dropdown('search_org_type', $options, $search_fields['org_type'], 'id="field_org_type" class="select2 form-control"');
						?>
					</div><?php
					break;
				case 'event':
					?><div class='col-sm-2 '>
						<p>
							<strong><label for="field_event">Name</label></strong>
						</p>
						<?php
						$data = array(
							'name' => 'search_event',
							'id' => 'field_event',
							'class' => 'form-control',
							'value' => $search_fields['event']
						);
						echo form_input($data);
						?>
					</div>
					<div class='col-sm-2 <?php echo $d_none_class;?>'>
						<p>
							<strong><label for="field_child_id">Child</label></strong>
						</p>
						<?php
						$options = array(
							'' => 'Select'
						);
						if ($children->num_rows() > 0) {
							foreach ($children->result() as $row) {
								$options[$row->childID] = $row->first_name . ' ' . $row->last_name;
							}
						}
						echo form_dropdown('search_child_id', $options, $search_fields['child_id'], 'id="field_child_id" class="select2 form-control"');
						?>
					</div><?php
					break;
			}
			?>
			<div class='col-sm-2 <?php echo $d_none_class;?>'>
				<p>
					<strong><label for="field_confirmed">Confirmed</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_confirmed', $options, $search_fields['confirmed'], 'id="field_confirmed" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2 <?php echo $d_none_class;?>'>
				<p>
					<strong><label for="field_completed">Completed</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_completed', $options, $search_fields['completed'], 'id="field_completed" class="select2 form-control"');
				?>
			</div>
			<?php
			switch ($type) {
				case 'event':
					?><div class='col-sm-2 <?php echo $d_none_class;?>'>
						<p>
							<strong><label for="field_cancelled">Cancelled</label></strong>
						</p>
						<?php
						$options = array(
							'' => 'Select',
							'yes' => 'Yes',
							'no' => 'No'
						);
						echo form_dropdown('search_cancelled', $options, $search_fields['cancelled'], 'id="field_cancelled" class="select2 form-control"');
						?>
					</div><?php
					break;
			}
			if ($projects === TRUE) {
				?><div class='col-sm-2'>
					<p>
						<strong><label for="field_project_type_id">Project Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					foreach ($project_types as $row) {
						$options[$row->typeID] = $row->name;
					}
					echo form_dropdown('search_project_type_id', $options, $search_fields['project_type_id'], 'id="field_project_type_id" class="select2 form-control"');
					?>
				</div><?php
			}
			?>
			<div class='col-sm-2 <?php echo $d_none_class;?>'>
				<p>
					<strong><label for="field_brand_id"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);

				foreach ($brands as $row) {
					$options[$row->brandID] = $row->name;
				}

				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<?php
			if ($projects === TRUE) {
				?><div class='col-sm-2 <?php echo $d_none_class;?>'>
					<p>
						<strong><label for="field_booking_type">View</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Show All',
						'booking' => 'Show Courses Only',
						'event' => 'Show Events Only'
					);
					echo form_dropdown('search_booking_type', $options, $search_fields['booking_type'], 'id="field_booking_type" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2 <?php echo $d_none_class;?>'>
					<p>
						<strong><label for="field_register_type">Register Type</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'children' => 'Children',
						'individuals' => 'Adults',
						'adults_children' => 'Adults & Children',
						'names' => 'Names Only',
						'numbers' => 'Numbers Only'
					);

					if ($this->auth->has_features('bikeability')) {
						$options['children_bikeability'] = 'Bikeability - Children';
						$options['individuals_bikeability'] = 'Bikeability - Adults';
						$options['bikeability'] = 'Bikeability - Names Only';
					}

					if ($this->auth->has_features('shapeup')) {
						$options['children_shapeup'] = 'Shape Up - Children';
						$options['individuals_shapeup'] = 'Shape Up - Adults';
						$options['shapeup'] = 'Shape Up - Names Only';
					}
					echo form_dropdown('search_register_type', $options, $search_fields['register_type'], 'id="field_register_type" class="select2 form-control"');
					?>
				</div>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_bookings_site">Bookings Site</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'yes' => 'Yes',
						'no' => 'No'
					);
					echo form_dropdown('search_bookings_site', $options, $search_fields['bookings_site'], 'id="field_bookings_site" class="select2 form-control"');
					?>
				</div><?php

			}
			if ($this->auth->has_features('projectcode')) { ?>
				<div class='col-sm-2 <?php echo $d_none_class;?>'>
					<p>
						<strong><label for="field_event">Project Code</label></strong>
					</p>
					<?php

					$options = array(
						'' => 'Select',
						'none' => 'None'
					);
					foreach ($project_codes as $row) {
						$options[$row->codeID] = $row->code;
					}
					echo form_dropdown('search_project_code', $options, $search_fields['project_code'], 'id="field_project_code" class="select2 form-control"');
					?>
				</div>
			<?php }
			?>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<div class="d-block">
				<button class='btn btn-primary btn-submit mr-6' type="submit">
					<i class='far fa-search'></i> Search
				</button>
				<a href="javascript:void(0);" class="text-dark-75 expand-filters <?php echo empty($d_none_class)?'d-none': '';?>" data-state="0">
					<i class="rotate-arrow fas fa-chevron-up font-size-sm mr-3"></i>
					<span class="font-weight-bold">Expand Search Filters</span> </a>
			</div>
			<button class='btn btn-default' name="s" value="cancel">
				Cancel
			</button>
		</div>
	</div>
	<?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if ($bookings->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No bookings found. Do you want to <?php	if ($projects === TRUE) {
			echo 'create a ' . anchor('bookings/course/new', 'course') . ' or an ' . anchor('bookings/event/new', 'event');
		} else {
			echo anchor($add_url, 'create one');
		}
		?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-bordered'>
				<thead>
					<tr>
						<th>
							Start Date
						</th>
						<th>
							End Date
						</th>
						<?php
						switch ($type) {
							case 'booking':
								?><th>
									<?php echo $this->settings_library->get_label('customer'); ?>
								</th>
								<th>
									Activities
								</th>
								<th>
									Staff
								</th>
								<th>
									Period
								</th>
								<th class="min">
									Sessions
								</th>
								<th>
									Risk Assessed
								</th><?php
								break;
							case 'event':
								?><th>
									Name
								</th>
								<th>
									Venue
								</th>
								<th>
									Type
								</th>
								<th>
									Activities
								</th>
								<th>
									Staff
								</th>
								<?php
								break;
						}
						?>
						<th>Duplicate</th>
						<th class="text-center">Edit</th>
						<th>Remove</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($bookings->result() as $row) {
						$edit_link = 'bookings/contract/' . $row->bookingID;
						if ($row->project == 1) {
							if ($row->type == 'event') {
								$edit_link = 'bookings/event/' . $row->bookingID;
							} else {
								$edit_link = 'bookings/course/' . $row->bookingID;
							}
						}
						?>
						<tr style="<?php echo row_style($row->brand_colour); ?>">
							<td>
								<?php echo anchor($edit_link, mysql_to_uk_date($row->startDate)); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->endDate); ?>
							</td>
							<?php
							if ($type == 'event') {
								?><td class="name wrap">
									<?php
									if (!empty($row->event)) {
										echo anchor('bookings/edit/' . $row->bookingID, $row->event);
									}
									?>
								</td><?php
							}
							?>
							<td class="wrap<?php if ($type == 'booking') { echo " name"; } ?>">
								<?php echo $row->org;	?>
							</td>
							<?php
							if ($type == 'event') {
								?><td class="wrap">
									<?php
									$types = array();
									if (array_key_exists($row->bookingID, $booking_types)) {
										$types = $booking_types[$row->bookingID];
									}
									// sort and remove empty
									$types = array_filter($types);
									$types = array_unique($types);
									sort($types);
									?><span class="truncate"><?php echo implode(', ', $types); ?></span>
								</td><?php
							}
							?>
							<td class="wrap">
								<?php
								$activities = array();
								if (array_key_exists($row->bookingID, $booking_activities)) {
									$activities = $booking_activities[$row->bookingID];
								}
								// sort and remove empty
								$activities = array_filter($activities);
								$activities = array_unique($activities);
								sort($activities);
								?><span class="truncate"><?php echo implode(', ', $activities); ?></span>
							</td>
							<td class="wrap">
								<?php
								$staff_list = explode(",", $row->staff);
								if (count($staff_list) > 0) {
									$staff_abbr_list = array();
									sort($staff_list);
									/*foreach ($staff_list as $staff_person) {
										$staff_person = trim($staff_person);
										$staff_abbr_list[] = '<abbr title="' . $staff_person . '">' . preg_replace('~\b(\w)|.~', '$1', $staff_person) . '</abbr>';
									}
									echo implode(", ", $staff_abbr_list);*/
									?><span class="truncate"><?php echo implode(', ', $staff_list); ?></span><?php
								} else {
									?><em>None</em><?php
								}
								?>
							</td>
							<?php
							switch ($type) {
								case 'booking':
									?><td>
										<?php
											// get number of weeks and days
											$difference = strtotime("+1 day", strtotime($row->endDate)) - strtotime($row->startDate);
											$weeks = $difference / (7*24*60*60);
											$fullweeks = floor($weeks);
											$remainder = ($weeks - $fullweeks) * (7*24*60*60);
											$days = $remainder / (24*60*60);
											$fulldays = floor($days);
											if ($fullweeks > 0) {
												echo $fullweeks . "w";
											}
											if ($fullweeks > 0 && $fulldays > 0) {
												echo "&nbsp;";
											}
											if ($fulldays > 0) {
												echo $fulldays . "d";
											}
										?>
									</td>
									<td class="min">
										<?php
										$lessons = 0;
										if (array_key_exists($row->bookingID, $booking_blocks) && count($booking_blocks[$row->bookingID]) > 0) {
											foreach ($booking_blocks[$row->bookingID] as $block) {
												// loop through lessons
												if (array_key_exists($row->bookingID, $booking_lessons) && array_key_exists($block['blockID'], $booking_lessons[$row->bookingID])) {
													foreach ($booking_lessons[$row->bookingID][$block['blockID']] as $lessonID => $lesson) {

														// switch start and end dates depending on if session has them, else default to block
														if (!empty($lesson['startDate']) && !empty($lesson['endDate'])) {
															$date = $lesson['startDate'];
															$end_date = $lesson['endDate'];
														} else {
															$date = $block['startDate'];
															$end_date = $block['endDate'];
														}

														// loop through dates to see how many times day occurs
														while (strtotime($date) <= strtotime($end_date)) {
															if (strtolower(date('l', strtotime($date))) == $lesson['day']) {
																$lessons++;
															}
															$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
														}
													}
												}
											}
										}
										// check for cancelled lessons
										if (isset($booking_cancellations[$row->bookingID])) {
											$lessons -= count($booking_cancellations[$row->bookingID]);
										}
										echo $lessons;
										?>
									</td><?php
									break;
							}
							?>
							<?php
							if ($type == 'booking') {
								?><td class="has_icon">
									<?php
									// assume not
									$riskassessed = FALSE;

									// get delivery addresses within bookings
									$addressIDs = array();

									if (isset($booking_addresses[$row->bookingID]) && count($booking_addresses[$row->bookingID]) > 0) {
										foreach ($booking_addresses[$row->bookingID] as $addressID) {
											$addressIDs[] = $addressID;
										}
									}

									if (count($addressIDs) > 0) {

										$assessed = 0;
										$inducted = 0;

										foreach ($addressIDs as $addressID) {
											if (isset($customer_safety[$row->orgID]['risk assessment'][$addressID]) ) {
												$assessed++;
											}

											if (isset($customer_safety[$row->orgID]['school induction'][$addressID])) {
												$inducted++;
											}
										}

										if ($assessed == count($addressIDs) && $inducted == count($addressIDs)) {
											$riskassessed = TRUE;
										}
									}

									if($riskassessed == TRUE) {
										?><a class='btn btn-success btn-sm' href="<?php echo site_url('customers/safety/' . $row->orgID); ?>" title="Yes">
											<i class='far fa-check'></i>
										</a><?php
									} else {
										?><a class='btn btn-danger btn-sm' href="<?php echo site_url('customers/safety/' . $row->orgID); ?>" title="No">
											<i class='far fa-times'></i>
										</a><?php
									}
									?>
								</td><?php
							}
							?>
							<td class="has_icon center">
								<a class='btn btn-success btn-sm confirm-duplicate' href='<?php echo site_url('bookings/duplicate/' . $row->bookingID); ?>' title="Duplicate">
									<i class='far fa-copy'></i>
								</a>
							</td>
							<td class="has_icon center">
								<a class='btn btn-warning btn-sm' href='<?php echo site_url($edit_link); ?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
							<td class="has_icon center">
								<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('bookings/remove/' . $row->bookingID); ?>' title="Remove">
									<i class='far fa-trash'></i>
								</a>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
}
echo $this->pagination_library->display($page_base);
