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
		No evaluations to approve found.
	</div>
	<?php
} else {
	echo form_open($page_base .'/approvals');
	$hidden_fields = array(
		'bulk' => 1
	);
	echo form_hidden($hidden_fields);
	echo $this->pagination_library->display($page_base);
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered evaluations bulk-checkboxes'>
				<thead>
					<tr>
						<th class="bulk-checkbox">
							<input type="checkbox" />
						</th>
                        <th>
                            Session
                        </th>
						<th>
							Organisation/Evaluation
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
							<td class="center">
								<input name="selected_evaluations[<?php echo $row->noteID; ?>]" value="<?php echo $row->noteID; ?>"<?php if (array_key_exists($row->noteID, $selected_evaluations)) { echo " checked=\"checked\""; } ;?> id="offer_<?php echo $row->noteID; ?>" type="checkbox" />
							</td>
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
								<strong><?php
								if (!empty($row->block_org)) {
									echo $row->block_org;
								} else if (!empty($row->booking_org)) {
									echo $row->booking_org;
								}
								?></strong><br>
								<?php echo nl2br($row->content); ?>
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
								<a class='btn btn-success btn-sm confirm' href='<?php echo site_url('evaluations/approve/' . $row->noteID); ?>' title="Approve">
								   <i class='far fa-check'></i>
								</a>
								<a class='btn btn-danger btn-sm confirm' href='<?php echo site_url('evaluations/reject/' . $row->noteID); ?>' title="Reject">
									<i class='far fa-times'></i>
								</a>
								<a class='btn btn-info btn-sm' href='<?php echo site_url('sessions/notes/edit/' . $row->noteID); ?>' title="View">
									<i class='far fa-globe'></i>
								</a>
						</tr><?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<br />
	<div class="row">
		<div class="col-sm-2">
			<?php
			$options = array(
				'' => 'Select Action',
				'approve' => 'Approve',
				'reject' => 'Reject'
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
