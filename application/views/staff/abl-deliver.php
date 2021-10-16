<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);

	$data['tab'] = "abl-deliver";
	$this->load->view('staff/qualifications-tabs.php', $data);
}

echo form_open_multipart($submit_to);
if (count($activities) > 0) {
	echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Able to Deliver</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='table-responsive'>
				<table class='table table-striped table-bordered' id="activities">
					<thead>
						<tr>
							<th>
								Activity
							</th>
							<th class="center">
								<?php echo $this->settings_library->get_staffing_type_label('head'); ?>
							</th>
							<th class="center">
								<?php echo $this->settings_library->get_staffing_type_label('lead'); ?>
							</th>
							<th class="center">
								<?php echo $this->settings_library->get_staffing_type_label('assistant'); ?>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php
						if ($this->input->post()) {
							$activities_array = $this->input->post('activities');
						}
						if (!is_array($activities_array)) {
							$activities_array = array();
						}
						foreach ($activities as $activityID => $label) {
							?><tr>
								<td><?php echo $label; ?></td>
								<?php
								$roles = array(
									'head',
									'lead',
									'assistant'
								);
								foreach ($roles as $role) {
									?><td class="center">
										<?php
										$data = array(
											'name' => 'activities[' . $activityID . '][' . $role . ']',
											'value' => 1
										);
										if (isset($activities_array[$activityID][$role]) && $activities_array[$activityID][$role] == 1) {
											$data['checked'] = TRUE;
										}
										echo form_checkbox($data);
										?>
									</td><?php
								}
								?>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<?php echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
	<?php
echo form_close();
