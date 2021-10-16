<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search != TRUE) { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'search-form', 'method' => 'get']); ?>
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
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_from',
					'id' => 'field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_booking_id">Name</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($projects_list->num_rows() > 0) {
					foreach ($projects_list->result() as $row) {
						$options[$row->bookingID] = $row->name;
					}
				}
				echo form_dropdown('booking_id', $options, $search_fields['booking_id'], 'id="field_booking_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_brand_id"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('brand_id', $options, $search_fields['brand_id'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_org_id"><?php echo $this->settings_library->get_label('customer'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($orgs->num_rows() > 0) {
					foreach ($orgs->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('org_id', $options, $search_fields['org_id'], 'id="field_org_id" class="select2 form-control"');
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
				echo form_dropdown('activity_id', $options, $search_fields['activity_id'], 'id="field_activity_id" class="select2 form-control"');
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
				if ($staff->num_rows() > 0) {
					foreach ($staff->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' .$row->surname;
					}
				}
				echo form_dropdown('staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_unstaffed">Unstaffed Sessions</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('unstaffed', $options, $search_fields['unstaffed'], 'id="field_unstaffed" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_terms_accepted">Terms &amp; Conditions</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('terms_accepted', $options, $search_fields['terms_accepted'], 'id="field_terms_accepted" class="select2 form-control"');
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
<?php echo form_close(); ?>
<div id="results"></div>
<?php
foreach ($booking_types as $type => $label) {
	?><div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label"><?php echo $label; ?></h3>
			</div>
		</div>
		<?php
		$items_shown = 0;
		$cols = 7;
		if ($$type->num_rows() > 0) {
			foreach ($$type->result() as $row) {
				// only show if some blocks
				if (!array_key_exists($row->bookingID, $blocks)) {
					continue;
				}
				// check if filtering by staffed
				$unstaffed = 0;
				if (array_key_exists($row->bookingID, $unstaffed_lessons)) {
					foreach ($unstaffed_lessons[$row->bookingID] as $unstaffed_blocks) {
						$unstaffed += count($unstaffed_blocks);
					}
				}
				switch ($search_fields['unstaffed']) {
					case 'yes':
						if ($unstaffed === 0) {
							continue 2;
						}
						break;
					case 'no':
						if ($unstaffed > 0) {
							continue 2;
						}
						break;
				}
				if ($items_shown === 0) {
					?><div class="table-responsive">
						<table class='table table-striped table-bordered table-pages'>
							<thead>
								<tr>
									<?php
									if ($type == 'projects') {
										?><th scope="col">Project</th><?php
										$cols++;
									}
									?>
									<th scope="col">School or Organisation</th>
									<th scope="col">Department</th>
									<th scope="col">Start Date</th>
									<th scope="col">End Date</th>
									<th scope="col">Activities</th>
									<?php
									if ($type == 'projects') {
										?><th scope="col">Participants</th><?php
										$cols++;
									}
									?>
									<th scope="col"></th>
								</tr>
							</thead>
							<tbody>
								<?php
				}
				$items_shown++;
				?><tr style="<?php echo row_style($row->brand_colour); ?>">
					<?php
					if ($type == 'projects') {
						?><td><?php echo $row->name; ?></td><?php
					}
					?>
					<td><?php echo $row->org; ?></td>
					<td><?php echo $row->brand; ?></td>
					<td><?php echo mysql_to_uk_date($row->startDate); ?></td>
					<td><?php echo mysql_to_uk_date($row->endDate); ?></td>
					<td><?php
					$row->activities = explode(',', $row->activities . ',' . $row->activities_other);
					if (is_array($row->activities)) {
						$row->activities = array_filter($row->activities); // remove empty vals
						if (count($row->activities) > 0) {
							sort($row->activities);
							echo implode(', ', $row->activities);
						} else {
							echo 'None';
						}
					} else {
						echo 'None';
					}
					?></td>
					<?php
					if ($type == 'projects') {
						?><td><?php echo $bookings_participants[$row->bookingID]; ?></td><?php
					}
					?>
					<td>
						<a class="btn btn-light btn-sm collapsed" data-toggle="collapse" href="#booking<?php echo $row->bookingID; ?>" title="Toggle Blocks" aria-expanded="false" aria-controls="booking<?php echo $row->bookingID; ?>">
							<i class="far fa-angle-down show-when-collapsed"></i>
							<i class="far fa-angle-up hide-when-collapsed"></i>
						</a>
					</td>
				</tr><?php
				// check for blocks
				if (array_key_exists($row->bookingID, $blocks)) {
					?><tr id="booking<?php echo $row->bookingID; ?>" class="collapse">
						<td colspan="<?php echo $cols; ?>">
							<div class="table-responsive">
								<table class='table table-striped table-bordered table-pages'>
									<thead>
										<tr>
											<th scope="col">Block</th>
											<th scope="col"></th>
											<th scope="col">School or Organisation</th>
											<th scope="col">Start Date</th>
											<th scope="col">End Date</th>
											<th scope="col">Activities</th>
											<th scope="col">Staff</th>
											<th scope="col">Unstaffed Sessions</th>
											<?php
											if ($type == 'projects') {
												?><th scope="col">Participants</th><?php
											}
											?>
											<th scope="col">Terms &amp; Conditions</th>
											<th scope="col">Main Contact Details</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($blocks[$row->bookingID] as $block) {
											$unstaffed = 0;
											if (array_key_exists($block->bookingID, $unstaffed_lessons) && array_key_exists($block->blockID, $unstaffed_lessons[$block->bookingID])) {
												$unstaffed = count($unstaffed_lessons[$block->bookingID][$block->blockID]);
											}
											switch ($search_fields['unstaffed']) {
												case 'yes':
													if ($unstaffed === 0) {
														continue 2;
													}
													break;
												case 'no':
													if ($unstaffed > 0) {
														continue 2;
													}
													break;
											}
											?><tr>
												<td><?php echo $block->name; ?></td>
												<td><a href="<?php echo site_url($page_base . '/' . $block->blockID); ?>" class="btn btn-light btn-sm"><i class="far fa-eye"></i></a></td>
												<td><?php
												if (!empty($block->org)) {
													echo $block->org;
												} else {
													echo $row->org;
												}
												?></td>
												<td><?php echo mysql_to_uk_date($block->startDate); ?></td>
												<td><?php echo mysql_to_uk_date($block->endDate); ?></td>
												<td><?php
												$block->activities = explode(',', $block->activities . ',' . $block->activities_other);
												if (is_array($block->activities)) {
													$block->activities = array_filter($block->activities); // remove empty vals
													if (count($block->activities) > 0) {
														sort($block->activities);
														echo implode(', ', $block->activities);
													} else {
														echo 'None';
													}
												} else {
													echo 'None';
												}
												?></td>
												<td><?php
												$block->staff = explode(',', $block->staff);
												if (is_array($block->staff)) {
													$block->staff = array_filter($block->staff); // remove empty vals
													if (count($block->staff) > 0) {
														sort($block->staff);
														echo implode(', ', $block->staff);
													} else {
														echo 'None';
													}
												} else {
													echo 'None';
												}
												?></td>
												<td><?php echo $unstaffed; ?></td>
												<?php
												if ($type == 'projects') {
													?><td><?php echo $block->participants; ?></td><?php
												}
												?>
												<td><?php
												if ($block->terms_accepted == 1) {
													echo 'Yes';
												} else {
													echo 'No';
												}
												?></td>
												<td><?php
												$contact_bits = [];
												if (!empty($block->orgID)) {
													// display from block
													if (!empty($block->contact_name)) {
														$contact_bits[] = 'Name: ' . $block->contact_name;
													}
													if (!empty($block->contact_tel)) {
														$contact_bits[] = 'Phone: <a href="tel:' . $block->contact_tel . '">' . $block->contact_tel . '</a>';
													}
													if (!empty($block->contact_mobile)) {
														$contact_bits[] = 'Mobile: <a href="tel:' . $block->contact_mobile . '">' . $block->contact_mobile . '</a>';
													}
													if (!empty($block->contact_email)) {
														$contact_bits[] = 'Email: <a href="mailto:' . $block->contact_email . '">' . $block->contact_email . '</a>';
													}
												} else {
													// display from booking
													if (!empty($block->booking_contact_name)) {
														$contact_bits[] = 'Name: ' . $block->booking_contact_name;
													}
													if (!empty($block->booking_contact_tel)) {
														$contact_bits[] = 'Phone: <a href="tel:' . $block->booking_contact_tel . '">' . $block->booking_contact_tel . '</a>';
													}
													if (!empty($block->booking_contact_mobile)) {
														$contact_bits[] = 'Mobile: <a href="tel:' . $block->booking_contact_mobile . '">' . $block->booking_contact_mobile . '</a>';
													}
													if (!empty($block->booking_contact_email)) {
														$contact_bits[] = 'Email: <a href="mailto:' . $block->booking_contact_email . '">' . $block->booking_contact_email . '</a>';
													}
												}
												if (count($contact_bits) > 0) {
													echo implode('<br>', $contact_bits);
												}
												?></td>
											</tr><?php
										}
										?>
									</tbody>
								</table>
							</div>
						</td>
					</tr><?php
				}
			}
			if ($items_shown > 0) {
						?></tbody>
					</table>
				</div><?php
			}
		}
		if ($items_shown === 0) {
			?><div class="card-body">
				<div class="alert alert-info">
					No data
				</div>
			</div><?php
		}
	?></div><?php
}
