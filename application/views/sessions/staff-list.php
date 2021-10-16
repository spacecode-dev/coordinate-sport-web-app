<?php
display_messages();
if ($bookingID != NULL && $lessonID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $lesson_info->blockID,
		'lessonID' => $lessonID,
		'tab' => $tab
	);
	$this->load->view('sessions/tabs.php', $data);
}
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
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_comment">Comment</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_comment',
					'id' => 'field_comment',
					'class' => 'form-control',
					'value' => $search_fields['comment']
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
<div id="results"></div>
<?php
if ($staff->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No staff found. Do you want to <?php echo anchor('sessions/staff/'.$lessonID.'/new/', 'create one'); ?>?
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<?php echo form_open(site_url($page_base)); ?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th>
								Start Date
							</th>
							<th>
								End Date
							</th>
							<th>
								Time
							</th>
							<th>
								Staff
							</th>
							<th>
								Comment
							</th>
							<th>
								Type
							</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($staff->result() as $row) {
							?>
							<tr>
								<td>
									<?php echo anchor('sessions/staff/edit/' . $row->recordID, mysql_to_uk_date($row->startDate)); ?>
								</td>
								<td>
									<?php echo mysql_to_uk_date($row->endDate); ?>
								</td>
								<td>
									<?php
									if (!empty($row->startTime)) {
										echo substr($row->startTime, 0, 5);
									} else {
										echo substr($lesson_info->startTime, 0, 5);
									}
									echo '-';
									if (!empty($row->endTime)) {
										echo substr($row->endTime, 0, 5);
									} else {
										echo substr($lesson_info->endTime, 0, 5);
									}
									?>
								</td>
								<td class="name">
									<?php echo $row->first . ' ' . $row->surname; ?>
								</td>
								<td>
									<?php echo $row->comment; ?>
								</td>
								<td>
									<?php echo $this->settings_library->get_staffing_type_label($row->type); ?>
								</td>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('sessions/staff/edit/' . $row->recordID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('sessions/staff/remove/' . $row->recordID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a>
									</div>
								</td>
							</tr>
							<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
	<?php echo form_close(); ?>
	<?php
	echo $this->pagination_library->display($page_base);
}
?>
<?php if (count($offers) > 0) { ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered bulk-checkboxes'>
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
				foreach ($offers as $row) {
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
					?><tr>
					<td class="name" data-tooltip="tooltip-<?php echo $row->offerID; ?>">
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
							echo '<strong>' . ucwords($row->day) . 's - ' . substr($row->startTime, 0, 5) . '-' .substr($row->endTime, 0, 5) . '</strong><br />';
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
					<td>
						<?php echo $row->first . ' ' .$row->surname; ?>
					</td>
					<td>
						<?php echo $this->settings_library->get_staffing_type_label($row->type); ?>
					</td>
					<td>
						<?php
						switch ($row->status) {
							case 'offered':
							default:
								$label_colour = 'blue';
								break;
							case 'declined':
							case 'expired':
								$label_colour = 'red';
								break;
							case 'accepted':
								$label_colour = 'green';
								break;
						}
						?>
						<span class="label label-<?php echo $label_colour; ?>"><?php
							echo ucwords($row->status);
							if (!empty($row->reason)) {
								echo ' (' . $row->reason . ')';
							}
							?></span>
					</td>
					<td>
						<?php echo mysql_to_uk_date($row->added); ?>
					</td>
					<td class='text-right'>
						<?php
						if ($row->status == 'offered') {
							?><a class='btn btn-success btn-sm confirm' href='<?php echo site_url('acceptance/accept/' . $row->offerID . '?from=/sessions/staff/' . $lessonID); ?>' title="Approve">
								<i class='far fa-check'></i>
							</a>
						<a class='btn btn-danger btn-sm confirm' href='<?php echo site_url('acceptance/decline/' . $row->offerID . '?from=/sessions/staff/' . $lessonID); ?>' title="Decline">
								<i class='far fa-times'></i>
							</a><?php
						} ?>
					</td>
					</tr><?php
				}
				?>
				</tbody>
			</table>
		</div>
	</div>
<?php }
