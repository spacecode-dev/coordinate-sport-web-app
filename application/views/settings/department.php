<?php
display_messages();
echo form_open_multipart($submit_to);
 	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($brand_info->name)) {
						$name = $brand_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Colour <em>*</em>', 'field_colour');
					$colour = NULL;
					if (isset($brand_info->colour)) {
						$colour = $brand_info->colour;
						if (substr($colour, 0, 1) !== '#') {
							$colour = colour_to_hex($colour);
						}
					}
					$data = array(
						'name' => 'colour',
						'id' => 'field_colour',
						'class' => 'form-control colorpicker',
						'value' => set_value('colour', $this->crm_library->htmlspecialchars_decode($colour), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Logo', 'logo');
					$logo_path = NULL;
					if (isset($brand_info->logo_path)) {
						$logo_path = $brand_info->logo_path;
					}
					if (!empty($logo_path)) {
						$args = array(
							'alt' => 'Image',
							'src' => 'attachment/brand/' . $logo_path,
							'style' => "max-width:200px"
						);
						echo '<p>' . img($args) . '</p>';
						echo form_label('Replace Logo', 'logo');
					}
					$data = array(
						'name' => 'logo',
						'id' => 'logo',
						'class' => 'custom-file-input'
					);
					?>
					<div class="custom-file">
						<?php echo form_upload($data); ?>
						<label class="custom-file-label" for="file">Choose file</label>
					</div>
					<small class="text-muted form-text">Shown on outgoing emails instead of company logo if brand selected in booking</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Web Site', 'field_website');
					$website = NULL;
					if (isset($brand_info->website)) {
						$website = $brand_info->website;
					}
					$data = array(
						'name' => 'website',
						'id' => 'field_website',
						'class' => 'form-control',
						'value' => set_value('website', $this->crm_library->htmlspecialchars_decode($website), FALSE),
						'maxlength' => 200
					);
					echo form_url($data);
					?><small class="text-muted form-text">Shown on outgoing emails if smart tag used in relevant emails</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Newsletter Audience ID', 'field_mailchimp_id');
					$mailchimp_id = NULL;
					if (isset($brand_info->mailchimp_id)) {
						$mailchimp_id = $brand_info->mailchimp_id;
					}
					$data = array(
						'name' => 'mailchimp_id',
						'id' => 'field_mailchimp_id',
						'class' => 'form-control',
						'value' => set_value('mailchimp_id', $this->crm_library->htmlspecialchars_decode($mailchimp_id), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
					?><small class="text-muted form-text">Enter the newsletter specific Audience ID to provide the application access to the list of all customers that have subscribed to the newsletter. <a href="https://mailchimp.com/help/find-audience-id/" target="_blank">Find Audience ID</a></small>
				</div>
				<div class='form-group'><?php
					echo form_label('Staff Performance');
					$data = array(
						'name' => 'staff_performance_exclude_session_evaluations',
						'id' => 'staff_performance_exclude_session_evaluations',
						'value' => 1
					);
					$staff_performance_exclude_session_evaluations = NULL;
					if (isset($brand_info->staff_performance_exclude_session_evaluations)) {
						$staff_performance_exclude_session_evaluations = $brand_info->staff_performance_exclude_session_evaluations;
					}
					if (set_value('staff_performance_exclude_session_evaluations', $this->crm_library->htmlspecialchars_decode($staff_performance_exclude_session_evaluations), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Exclude Session Evaluations
							<span></span>
						</label>
					</div><?php
					$data = array(
						'name' => 'staff_performance_exclude_pupil_assessments',
						'id' => 'staff_performance_exclude_pupil_assessments',
						'value' => 1
					);
					$staff_performance_exclude_pupil_assessments = NULL;
					if (isset($brand_info->staff_performance_exclude_pupil_assessments)) {
						$staff_performance_exclude_pupil_assessments = $brand_info->staff_performance_exclude_pupil_assessments;
					}
					if (set_value('staff_performance_exclude_pupil_assessments', $this->crm_library->htmlspecialchars_decode($staff_performance_exclude_pupil_assessments), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Exclude Pupil Assessments
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">If selected, these items will not be taken into account if a staff member is assigned to this department.</small>
				</div>
				<?php
				if ($this->auth->has_features('online_booking')) {
					?><div class='form-group'><?php
						$data = array(
							'name' => 'hide_online',
							'id' => 'hide_online',
							'value' => 1
						);
						$hide_online = 0;
						if (isset($brand_info->hide_online)) {
							$hide_online = $brand_info->hide_online;
						}
						if (set_value('hide_online', $this->crm_library->htmlspecialchars_decode($hide_online), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Hide from Search Dropdown on Bookings Site
								<span></span>
							</label>
						</div>
					</div><?php
				}
				?><div class='form-group'><?php
					echo form_label('Mandatory Qualifications');
					?>
					<table class='table table-striped table-bordered' id="quals-brand">
						<thead>
						<tr>
							<th>
								Name
							</th>
							<th class="center"></th>
						</tr>
						</thead>
						<tbody>
						<?php
						if (!is_array($mandatory_quals)) {
							$mandatory_quals = [];
						}

						if (!is_array($brand_quals)) {
							$brand_quals = [];
						}
						foreach ($mandatory_quals as $qual) {
							?><tr>
							<td><label for="<?php echo 'quals_' . $qual->qualID; ?>"><?php echo $qual->name; ?></label></td>
							<td class="center">
							<?php
							$data = array(
								'name' => 'quals[' . $qual->qualID . ']',
								'id' => 'quals_' . $qual->qualID,
								'value' => 1
							);
							$data['checked'] = FALSE;
							if (isset($brand_quals[$qual->qualID])) {
								$data['checked'] = TRUE;
							}
							echo form_checkbox($data);
							?>
							</td>
							</tr><?php
						}
						?>
						</tbody>
					</table>
				</div>
				<div class='form-group'><?php
					echo form_label('Able to Deliver');
					?>
					<table class='table table-striped table-bordered' id="activities-brand">
						<thead>
						<tr>
							<th>
								Activity
							</th>
							<th class="center"></th>
						</tr>
						</thead>
						<tbody>
						<?php
						if (!is_array($activities)) {
							$activities = [];
						}

						if (!is_array($brand_activities)) {
							$brand_activities = [];
						}
						foreach ($activities as $id => $name) {
							?><tr>
							<td><label for="<?php echo 'activities_' . $id; ?>"><?php echo $name; ?></label></td>
							<td class="center">
								<?php
								$data = array(
									'name' => 'activities[' . $id . ']',
									'id' => 'activities_' . $id,
									'value' => 1
								);
								$data['checked'] = FALSE;
								if (isset($brand_activities[$id])) {
									$data['checked'] = TRUE;
								}
								echo form_checkbox($data);
								?>
							</td>
							</tr><?php
						}
						?>
						</tbody>
					</table>
				</div>
			</div><?php
		echo form_fieldset_close();
		?>
		<div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div>
<?php echo form_close();
