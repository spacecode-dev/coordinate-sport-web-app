<?php display_messages();
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
					'value' => $search_fields['date_from'],
					'data-mindate' => $block->startDate,
					'data-maxdate' => $block->endDate
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
					'value' => $search_fields['date_to'],
					'data-mindate' => $block->startDate,
					'data-maxdate' => $block->endDate
				);
				echo form_input($data);
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
					<strong><label for="field_type_id">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($types->num_rows() > 0) {
					foreach ($types->result() as $row) {
						$options[$row->typeID] = $row->name;
					}
				}
				echo form_dropdown('type_id', $options, $search_fields['type_id'], 'id="field_type_id" class="select2 form-control"');
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
					'sunday' => 'Sunday',
				);
				echo form_dropdown('day', $options, $search_fields['day'], 'id="field_day" class="select2 form-control"');
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
if ($sessions->num_rows() > 0) {
	?><div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered table-pages'>
				<thead>
					<tr>
						<th scope="col">Day</th>
						<th scope="col"></th>
						<th scope="col">Start Time</th>
						<th scope="col">End Time</th>
						<th scope="col">Group/Class</th>
						<th scope="col">Class Size</th>
						<th scope="col">Location</th>
						<th scope="col">Activity</th>
						<th scope="col">Type</th>
						<?php
						if ($block->project == 1) {
							?><th scope="col">Participants</th><?php
						}
						?>
						<th scope="col">Staff</th>
						<th scope="col">Required Member of Staff</th>
						<th scope="col">Missing Staff</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($sessions->result() as $row) {
						$missing_head = $row->staff_required_head - $row->head_count;
						if ($missing_head < 0) {
							$missing_head = 0;
						}
						$missing_lead = $row->staff_required_lead - $row->lead_count;
						if ($missing_lead < 0) {
							$missing_lead = 0;
						}
						$missing_assistant = $row->staff_required_assistant - $row->assistant_count;
						if ($missing_assistant < 0) {
							$missing_assistant = 0;
						}
						?><tr class="<?php if (($missing_head + $missing_lead + $missing_assistant) > 0) { echo ' missing_staff'; } ?>">
							<td><?php
							echo anchor('bookings/sessions/edit/' . $row->lessonID, ucwords($row->day));
							if (!empty($row->startDate)) {
								echo '<br />(' . mysql_to_uk_date($row->startDate);
								if (!empty($row->endDate) && strtotime($row->endDate) > strtotime($row->startDate)) {
									echo '-' . mysql_to_uk_date($row->endDate);
								}
								echo ')';
							}
							?></td>
							<td><a href="<?php echo site_url('bookings/sessions/edit/' . $row->lessonID); ?>" class="btn btn-light btn-sm"><i class="far fa-eye"></i></a></td>
							<td><?php echo anchor('bookings/sessions/edit/' . $row->lessonID, substr($row->startTime, 0 ,5)); ?></td>
							<td><?php echo substr($row->endTime, 0 ,5); ?></td>
							<td><?php
							if ($row->group == "other") {
								echo $row->group_other;
							} else {
								echo $this->crm_library->format_lesson_group($row->group);
							}
							?></td>
							<td><?php echo $row->class_size; ?></td>
							<td><?php echo $row->location; ?></td>
							<td><?php
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
							?></td>
							<td><?php
							if (!empty($row->type)) {
								echo $row->type;
							} else if (!empty($row->type_other)) {
								echo $row->type_other;
							}
							?></td>
							<?php
							if ($block->project == 1) {
								?><td><?php echo anchor('bookings/participants/' . $row->blockID, $participants[$row->lessonID]); ?></td><?php
							}
							?>
							<td><a href="<?php echo site_url('sessions/staff/' . $row->lessonID); ?>"><?php
							$row->staff = explode(',', $row->staff);
							if (is_array($row->staff)) {
								$row->staff = array_filter($row->staff); // remove empty vals
								if (count($row->staff) > 0) {
									sort($row->staff);
									echo implode(', ', $row->staff);
								} else {
									echo 'None';
								}
							} else {
								echo 'None';
							}
							?></a></td>
							<td><?php
							echo $this->settings_library->get_staffing_type_label('head') . ': ' . $row->staff_required_head;
							echo '<br>' . $this->settings_library->get_staffing_type_label('lead') . ': ' . $row->staff_required_lead;
							echo '<br>' . $this->settings_library->get_staffing_type_label('assistant') . ': '. $row->staff_required_assistant;
							?></td>
							<td><?php
							echo $this->settings_library->get_staffing_type_label('head') . ': ' . $missing_head;
							echo '<br>' . $this->settings_library->get_staffing_type_label('lead') . ': ' . $missing_lead;
							echo '<br>' . $this->settings_library->get_staffing_type_label('assistant') . ': '. $missing_assistant;
							?></td>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div><?php
} else {
	?><div class="alert alert-info">
		<i class="far fa-info-circle"></i>
		No sessions found
	</div><?php
}
