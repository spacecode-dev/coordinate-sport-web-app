<?php
display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'method' => 'get']); ?>
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
			<?php if (in_array($this->auth->user->department, array('directors', 'management', 'headcoach')) && $show_all || !in_array($this->auth->user->department, array('directors', 'management', 'headcoach'))){ ?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_staff_id">Submitted by</label></strong>
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
			<?php } ?>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_status">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'unsubmitted' => 'Unsubmitted',
					'submitted' => 'Submitted',
					'approved' => 'Approved',
					'rejected' => 'Rejected',
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
					<strong><label for="field_org">Organisation / School</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($orgs_list->num_rows() > 0) {
					foreach ($orgs_list->result() as $row) {
						$options[$row->orgID] = $row->name;
					}
				}
				echo form_dropdown('search_org', $options, $search_fields['org'], 'id="field_org" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="brandID"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
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
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="brandID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="sessionType">Session Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($session_types->num_rows() > 0) {
					foreach ($session_types->result() as $row) {
						$options[$row->typeID] = $row->name;
					}
				}
				echo form_dropdown('search_session_type', $options, $search_fields['session_type'], 'id="sessionType" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="activity">Activity</label></strong>
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
				echo form_dropdown('search_activity', $options, $search_fields['activity'], 'id="activity" class="select2 form-control"');
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
if ($evaluations->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No evaluations found.
	</div>
	<?php
} else {
	echo $this->pagination_library->display_get($page_base);
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered evaluations'>
				<thead>
					<tr>
						<th>
							Session
						</th>
						<th>
							Organisation
						</th>
						<th>
							Staff
						</th>
						<th>
							Status
						</th>
						<th>
							Submitted On
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($evaluations->result() as $row) {
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
						$tooltips[] = '<div class="tooltip-' . $row->noteID . '">' . implode('<br />', $tooltip) . '</div>';
						?><tr>
							<td class="name" data-tooltip="tooltip-<?php echo $row->noteID; ?>">
								<?php
								$lesson_start = $row->lesson_start;
								if (empty($lesson_start)) {
									$lesson_start = $row->block_start;
								}
								$lesson_end = $row->lesson_end;
								if (empty($lesson_end)) {
									$lesson_end = $row->block_end;
								}
								echo '<strong>' . ucwords($row->day) . ' - ' . substr($row->startTime, 0, 5) . '-' .substr($row->endTime, 0, 5) . '</strong><br />';
								echo mysql_to_uk_date($row->date);
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
							<td>
								<?php echo $row->first . ' ' .$row->surname; ?>
							</td>
							<td>
								<?php
								switch ($row->status) {
									default:
										$label_colour = 'warning';
										break;
									case 'submitted':
										$label_colour = 'info';
										break;
									case 'rejected':
										$label_colour = 'danger';
										break;
									case 'approved':
										$label_colour = 'success';
										break;
								}
								?>
								<span class="label label-inline label-<?php echo $label_colour; ?>"><?php
								echo ucwords($row->status);
								if ($row->status == 'rejected' && !empty($row->rejection_reason)) {
									echo ' (' . $row->reason . ')';
								}
								?></span>
							</td>
							<td>
								<?php
								if ($row->status != 'unsubmitted') {
									echo mysql_to_uk_date($row->added);
								}
								?>
							</td>
							<td class='text-right'>
								<?php
								if ($show_all === TRUE && $row->byID != $this->auth->user->staffID) {
									?><a class='btn btn-success btn-sm' href='<?php echo site_url('sessions/notes/edit/' . $row->noteID); ?>' title="View">
										<i class='far fa-globe'></i>
									</a><?php
								} else {
									?><a class='btn btn-success btn-sm' href='<?php echo site_url('coach/session/' . $row->lessonID . '/' . $row->date); ?>#evaluate' title="View">
										<i class='far fa-globe'></i>
									</a><?php
								}
							  ?>
							</td>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<?php
	echo $this->pagination_library->display_get($page_base);
}
?>
<div class="tooltips">
	<?php echo implode("\n", $tooltips); ?>
</div>
