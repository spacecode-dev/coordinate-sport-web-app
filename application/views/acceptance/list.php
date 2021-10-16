<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
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
	<div class="card-body">
		<div class='row'>
			<?php
			if ($show_all == TRUE) {
				?><div class='col-sm-2'>
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
				</div><?php
			}
			?>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_status">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'offered' => 'Offered',
					'accepted' => 'Accepted',
					'declined' => 'Declined',
				);
				echo form_dropdown('search_status', $options, $search_fields['status'], 'id="field_status" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_date_from',
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
					'name' => 'search_date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Organisation / School</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($orgs as $org) {
					$options[$org->orgID] = $org->name;
				}

				echo form_dropdown('search_orgs', $options, $search_fields['org'], 'id="search_orgs" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Department</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($departments as $department) {
					$options[$department->brandID] = $department->name;
				}

				echo form_dropdown('search_department', $options, $search_fields['brand_id'], 'id="search_department" class="select2 form-control"');
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
				foreach ($session_types as $type) {
					$options[$type->typeID] = $type->name;
				}

				echo form_dropdown('search_type_id', $options, $search_fields['type_id'], 'id="search_session_types" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="search_by">Activity</label></strong>
				</p>
				<?php
				$options = ['' => 'Select'];

				foreach ($activities as $activity) {
					$options[$activity->activityID] = $activity->name;
				}

				echo form_dropdown('search_activity', $options, $search_fields['activity_id'], 'id="search_activity" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_staffing_type">Staffing Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($this->settings_library->staffing_types_defaults as $key => $label) {
					$options[$key] = $this->settings_library->get_staffing_type_label($key);
				}
				echo form_dropdown('search_staffing_type', $options, $search_fields['staffing_type'], 'id="field_staffing_type" class="select2 form-control"');
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
$tooltips = array();
if (count($offers_bookings) == 0) {
	?>
	<div class="alert alert-info">
		No offers found.
	</div>
	<?php
} else {
	$form_action = 'acceptance';
	if ($show_all === TRUE) {
		$form_action .= '/all';
	}
	echo form_open($form_action, 'id="acceptance"');
	$hidden_fields = array(
		'bulk' => 1
	);

	if ($manual) {
		$hidden_fields['manual'] = 1;
	}

	echo form_hidden($hidden_fields);
	echo $this->pagination_library->display($page_base);
	?>
	<?php foreach ($offers_bookings as $bookingId => $offers) { ?>
		<div class='card card-custom'>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label"><?php if ($booking_names[$bookingId]['project']) { ?>
						Project Name: <?php echo $booking_names[$bookingId]['name'] ?>
					<?php } else { ?>
						Contract
					<?php } ?></h3>
				</div>
			</div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered bulk-checkboxes'>
					<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
						<th>
							Session
						</th>
						<th>
							Organisation
						</th>
						<?php
						if ($show_all == TRUE) {
							?><th>
								Staff
							</th><?php
						}
						?>
						<th>
							Role
						</th>
						<th>
							Status
						</th>
						<th>
							Offered On
						</th>
						<th></th>
					</tr>
					</thead>
					<tbody>
					<?php
					foreach ($offers as $offer_array) { ?>
						<tr>
						<? foreach ($offer_array as $index => $row) {
							// build tooltip
							$tooltip = array();
							$address_bits = array();
							if (!empty($row->event_name)) {
								$tooltip[] = '<strong>Event:</strong> ' . $row->event_name;
							}
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
							if (!empty($row->lesson_type)) {
								$tooltip[] = '<strong>Type:</strong> ' . $row->lesson_type;
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
							$tooltips[] = '<div class="tooltip-' . $row->offerID . '">' . implode('<br />', $tooltip) . '</div>';
							?>
							<tr>
							<?php if ($index == 0) { ?>
								<td class="center" rowspan="<?= count($offer_array) ?>">
									<?php
									if ($row->status == 'offered') {
										?><input
										name="selected_offers[<?php echo $row->offerID; ?>]" value="<?php echo $row->offerID; ?>"<?php if (array_key_exists($row->offerID, $selected_offers)) {
											echo " checked=\"checked\"";
										}
									; ?> id="offer_<?php echo $row->offerID; ?>"
										type="checkbox" /><?php
									}
									?>
								</td>
							<?php } ?>
							<td class="name"
								data-tooltip="tooltip-<?php echo $row->offerID; ?>">
								<?php
								if ($row->status == 'offered') {
								?><label for="offer_<?php echo $row->offerID; ?>"><?php
									}
									$lesson_start = $row->lesson_start;
									if (empty($lesson_start)) {
										$lesson_start = $row->block_start;
									}
									$lesson_end = $row->lesson_end;
									if (empty($lesson_end)) {
										$lesson_end = $row->block_end;
									}
									echo '<strong>' . ucwords($row->day) . 's - ' . substr($row->startTime, 0, 5) . '-' . substr($row->endTime, 0, 5) . '</strong><br />';
									echo mysql_to_uk_date($lesson_start);
									if ($lesson_start != $lesson_end) {
										echo '-' . mysql_to_uk_date($lesson_end);
									}
									if ($row->status == 'offered') {
									?></label><?php
							}
							?>
							</td>
							<td>
								<?php
								if (!empty($row->block_org)) {
									echo $row->block_org;
								} else if (!empty($row->booking_org)) {
									echo $row->booking_org;
								}
								?>
							</td>
							<?php
							if ($show_all == TRUE) {
								?>
								<td>
								<?php echo $row->first . ' ' . $row->surname; ?>
								</td><?php
							}
							?>
							<td>
								<?php echo $this->settings_library->get_staffing_type_label($row->type); ?>
							</td>
							<td>
								<?php
								switch ($row->status) {
									case 'offered':
									default:
										$label_colour = 'info';
										break;
									case 'declined':
									case 'expired':
										$label_colour = 'danger';
										break;
									case 'accepted':
										$label_colour = 'success';
										break;
								}
								?>
								<span class="label label-inline label-<?php echo $label_colour; ?>"><?php
									echo ucwords($row->status);
									if (!empty($row->reason)) {
										echo ' (' . $row->reason . ')';
									}
									?></span>
							</td>
							<td>
								<?php echo mysql_to_uk_date($row->added); ?>
							</td>
							<?php if ($index == 0) { ?>
								<td class='text-right' rowspan="<?= count($offer_array) ?>">
									<?php
									if ($row->status == 'offered') {
										?><a class='btn btn-success btn-sm confirm'
											 href='<?php echo site_url('acceptance/accept/' . $row->offerID . '?from=' . $page_base); ?>'
											 title="Approve">
											<i class='far fa-check'></i>
										</a>
									<a class='btn btn-danger btn-sm confirm'
									   href='<?php echo site_url('acceptance/decline/' . $row->offerID . '?from=' . $page_base); ?>'
									   title="Decline">
											<i class='far fa-times'></i>
										</a><?php
									}
									if ($show_all === TRUE && count($offer_array) < 2) {
										?> <a class='btn btn-info btn-sm'
											  href='<?php echo site_url('sessions/staff/' . $row->lessonID); ?>'
											  title="View Session Staff">
											<i class='far fa-users'></i>
										</a><?php
									}
									?>
								</td>
							<?php } ?>
							</tr><?php
						} ?>
					</tr>
					<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
	<? } ?>

	<br />
	<div class="row">
		<div class="col-sm-2">
			<?php
			$options = array(
				'' => 'Select Action',
				'accept' => 'Accept',
				'decline' => 'Decline'
			);

			if (!array_key_exists($action, $options)) {
				$action = NULL;
			}
			echo form_dropdown('action', $options, $action, 'id="action" class="form-control select2"');
			?>
		</div>
		<div class="col-sm-2">
			<button class='btn btn-primary btn-submit' type="submit">
				Go
			</button>
		</div>
	</div>
	<?php
	echo form_close();
	echo $this->pagination_library->display($page_base);
}
?>
	<div class="tooltips">
<?php echo implode("\n", $tooltips); ?>
</div>
