<?php
display_messages();
$all_staff = [];
foreach ($staff_listing_by_dep as $dep => $value) {
	if ($dep != 'directors') {
		foreach ($value as $item) {
			$all_staff[] = $item;
		}
	}
}
echo form_open_multipart($submit_to);
	echo form_hidden('group', $group);
	if(!empty($recipientID)) {
		echo form_hidden('recipientID', $recipientID);
	}
	?><div class="row">
		<div class='col-md-6'>
			<?php echo form_fieldset('', ['class' => 'card card-custom']);
			?>
			<div class='card-header'>
				<div class="card-title pt-5">
					<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
					<h3 class="card-label">Recipient
						<?php if ($this->auth->user->department == 'directors' && $this->auth->account->admin == 1 && $group == 'staff') { ?>
						<small class="pt-2">
							<a href="#" onclick="setRecipients('<?php echo implode(',', $all_staff) ?>');return false;">All Staff Users</a>
							| <a href="#" onclick="setRecipients('<?php echo implode(',', $staff_listing_by_dep["directors"]) ?>');return false;">All Super Users</a>
						</small>
						<?php } ?>
					</h3>
				</div>
			</div>
			<div class="card-body">
			<?php if ($this->auth->user->department == 'directors' && $this->auth->account->admin == 1 && $group == 'staff') {
				$to = [];
				if (!$this->input->post() && isset($message_info->byID)) {
					$to[] = $message_info->byID;
				}else{
					$to[] = $this->input->post('to');
				}

				echo form_multiselect('to[]',
				 $staff_listing,
				 $to,
				'id="staff_recipient" class="form-control select2"');
				echo form_fieldset_close();
				echo form_fieldset('', ['class' => 'card card-custom']);
					?><div class='card-header'>
						<div class="card-title">
							<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
							<h3 class="card-label">Template</h3>
						</div>
					</div>
					<div class="card-body">
						<?php
							$options = ['None'];
							if (count($templates) > 0) {
								foreach ($templates as $key => $item) {
									$options[$key] = $item;
								}
							}
						?>
						<div class="form-group">
							<?php echo form_dropdown('template', $options, 0, 'id="field_template" class="select2 form-control" onchange="setTemplate();"'); ?>
						</div>
					</div>
				<?php
				echo form_fieldset_close();
			} else if($group === "staff"){ ?>
				<div class="form-group">
					<?php echo form_label('Permission Level', 'permission_levels', 'class="font-weight-bolder"'); ?>
					<?php
					if (!in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
						$departments = array(
							'all' => 'All',
							'none' => 'None',
							'directors' => $this->settings_library->get_permission_level_label('directors'),
							'management' => $this->settings_library->get_permission_level_label('management'),
							'office' => $this->settings_library->get_permission_level_label('office'),
							'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
							'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
							'coaching' => $this->settings_library->get_permission_level_label('coaching')
						);
						// if team leader, add team option
						if ($this->auth->user->department == 'headcoach') {
							$departments['team'] = 'Team';
						}
						$options = array();
						foreach ($departments as $key => $value) {
							$options[] = '<a href="#" class="select_recipient" data-department="' . $key . '">'  . $value . '</a>';
						}
						echo "<p>Select: " . implode(", ", $options)."</p>";
					}else{
						echo '<p>No permission levels available</p>';
					}
					?>
				</div>
				<div class="form-group">
					<?php echo form_label('Groups', 'groups', 'class="font-weight-bolder"'); ?>
					<?php
					if($groups->num_rows() > 0){
						$options = array();
						foreach ($groups->result() as $row){
							$options[] = '<a href="#" class="select_recipient" data-group="'.$row->groupID.'">'  . ucfirst($row->name) . '</a>';
						}
						echo "<p>Select: " . implode(", ", $options)."</p>";
					}else{
						echo form_label('No groups found.');
					}?>
				</div>
				<?php echo form_fieldset_close(); ?>
			<?php } else if($group === "schools"){?>
					<div class="form-group">
						<?php echo form_label('School Types', 'school_types', 'class="font-weight-bolder"'); ?>
						<?php
						$types = array(
							'all' => 'All',
							'none' => 'None',
							'infant' => 'Infant',
							'junior' => 'Junior',
							'primary' => 'Primary',
							'secondary' => 'Secondary',
							'college' => 'College',
							'special' => 'Special',
							'other' => 'Other'
						);

						$options = array();
						foreach ($types as $key => $value) {
							$options[] = '<a href="#" class="select_recipient" data-school-type="' . $key . '">'  . $value . '</a>';
						}
						echo "<p>Select: " . implode(", ", $options)."</p>";

						?>
					</div>
					<div class="form-group">
						<?php echo form_label('Sector', 'sector', 'class="font-weight-bolder"'); ?>
						<?php
							$options = array();
							$authorities = array(
								'0' => 'Local Authority',
								'1' => 'Private'
							);
							$options = array();
							foreach ($authorities as $key => $value) {
								$options[] = '<a href="#" class="select_recipient" data-sector="' . $key . '">'  . $value . '</a>';
							}
							echo "<p>Select: " . implode(", ", $options)."</p>";
						?>
					</div>
					<?php echo form_fieldset_close(); ?>
				<?php }else if($group === "organisations"){ ?>
					<div class="form-group">
						<?php echo form_label('Organisation Type', 'org_types', 'class="font-weight-bolder"'); ?>
						<?php
						$types = array(
							'all' => 'All',
							'none' => 'None',
							'0' => 'Organisations',
							'1' => 'Prospective Organisations',
						);

						$options = array();
						foreach ($types as $key => $value) {
							$options[] = '<a href="#" class="select_recipient" data-org-type="' . $key . '">'  . $value . '</a>';
						}
						echo "<p>Select: " . implode(", ", $options)."</p>";

						?>
					</div>
					<?php echo form_fieldset_close(); ?>
				<?php }else{?>
						<div class="multi-columns">
							<div class="form-group">
								<?php echo form_label('Date From', 'date_from'); ?>
								<?php
								$data = array(
									'name' => 'date_from',
									'id' => 'date_from',
									'class' => 'form-control datepicker',
									'value' => set_value('date_from')
								);
								echo form_input($data);
								?>
							</div>
							<div class="form-group">
								<?php echo form_label('Activity Types', 'activity_types');
								$options = array();
								if ($activities > 0) {
									foreach ($activities as $key => $value) {
										$options[$key] = $value;
									}
								}
								echo form_dropdown('activities[]', $options, set_value('activities', NULL, FALSE), 'multiple="multiple" id="activities" class="form-control select2 select2-tags"'); ?>
							</div>
							<div class="form-group">
								<?php echo form_label('Session Types', 'session_types');
								$options = array();
								if ($lesson_types > 0) {
									foreach ($lesson_types as $key => $value) {
										$options[$key] = $value;
									}
								}
								echo form_dropdown('typeIDs[]', $options, set_value('typeIDs', NULL, FALSE), 'multiple="multiple" id="typeID" class="form-control select2 select2-tags"'); ?>
							</div>
							<div class="form-group">
								<?php echo form_label('Date To', 'date_to'); ?>
								<?php
								$data = array(
									'name' => 'date_to',
									'id' => 'date_to',
									'class' => 'form-control datepicker',
									'value' => set_value('date_to')
								);
								echo form_input($data);
								?>
							</div>
							<div class="form-group">
								<?php echo form_label('Departments', 'departments');
								$options = array();

								if(count($departments) > 0) {
									foreach ($departments as $department) {
										$options[$department->brandID] = $department->name;
									}
								}

								echo form_dropdown('departments[]', $options, set_value('departments', NULL, FALSE), 'multiple="multiple" id="departments" class="select2 form-control select2-tags"'); ?>
							</div>
						</div>
				<?php
				echo form_fieldset_close();
			}
			 ?>
		</div>
		<div class="col-md-6">
			<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
				<div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
						<h3 class="card-label">Details</h3>
					</div>
				</div>
				<div class="card-body">
					<?php if ($this->auth->user->department != 'directors' || $this->auth->account->admin != 1 || $group != 'staff') { ?>
						<div class='form-group'><?php
							$mandate = '<em>*</em>';
							if($group == "participants"){
								$mandate = '';
							}
							echo form_label('To '.$mandate, 'to');
							if ($group == "staff" && !is_array($staff) && $staff->num_rows() > 0) {
								$to_options = array();$options = array();
								echo '<select multiple="multiple" class="control select2-tags" id="to" name="to[]">';
								foreach ($staff->result() as $row) {
									$selected = '';$attr ='data-department="'.$row->department.'" data-group="'.$row->groupID.'"';
									$array = isset($staff_array[$row->staffID])?$staff_array[$row->staffID]:array();
									if (in_array($this->auth->user->staffID, $array)) {
										$data['data-team'] = 'true';
									}
									$to = $this->input->post('to');
									if (!is_array($to)) {
										$to = array();
										if (!$this->input->post() && isset($message_info->byID)) {
											$to[] = $message_info->byID;
										}else if(!empty($recipientID)){
											$to[] = $recipientID;
										}
									}
									if (in_array($row->staffID, $to)) {
										$selected = 'selected="selected"';
									}
									if (in_array($row->staffID, $to)) {
										$to_options[] = $message_info->byID;
									}
									echo "<option value='".$row->staffID."' ".$selected." ".$attr.">".$row->first . ' ' . $row->surname."</option>";
								}
								echo "</select>";
							} else if (($group == "schools" || $group == "organisations") && !is_array($orgs) && $orgs->num_rows() > 0) {
								$to_options = array();$options = array();
								echo '<select multiple="multiple" class="control select2-tags" id="to" name="to[]">';
								foreach ($orgs->result() as $row) {
									$selected = '';$attr ='data-school-type="'.$row->schoolType.'" data-sector="'.$row->isPrivate.'"'.' data-org-type="'.$row->prospect.'"';
									$to = $this->input->post('to');
									if (!is_array($to)) {
										$to = array();
										if (!$this->input->post() && isset($message_info->byID)) {
											$to[] = $message_info->byID;
										}
									}
									if (in_array($row->orgID, $to) || !empty($recipientID)) {
										$selected = 'selected="selected"';
									}
									if (in_array($row->orgID, $to)) {
										$to_options[] = $message_info->byID;
									}
									echo "<option value='".$row->orgID."' ".$selected." ".$attr.">".$row->name."</option>";
								}
								echo "</select>";
							} else if ($group == "participants" && !is_array($participants) && $participants->num_rows() > 0){
								$to_options = array();$options = array();
								echo '<select multiple="multiple" class="control select2-tags" id="to" name="to[]">';
								foreach ($participants->result() as $row) {
									$selected = '';
									$to = $this->input->post('to');
									if (!is_array($to)) {
										$to = array();
										if (!$this->input->post() && isset($message_info->byID)) {
											$to[] = $message_info->byID;
										}
									}
									if (in_array($row->contactID, $to) || (!empty($recipientID) && $recipientID == $row->contactID)) {
										$selected = 'selected="selected"';
									}
									if (in_array($row->contactID, $to)) {
										$to_options[] = $message_info->byID;
									}
									echo "<option value='".$row->contactID."' ".$selected.">".$row->name."</option>";
								}
								echo "</select>";
							}
							?>
						</div>
					<?php }?>
					<div class='form-group'><?php
						echo form_label('Subject <em>*</em>', 'subject');
						$subject = NULL;
						if (isset($message_info->subject)) {
							$subject = 'Re: ' . $message_info->subject;
						}
						$data = array(
							'name' => 'subject',
							'id' => 'subject',
							'class' => 'form-control',
							'value' => set_value('subject', $this->crm_library->htmlspecialchars_decode($subject), FALSE),
							'maxlength' => 100
						);
						echo form_input($data);
						?></div>
					<div class='form-group'><?php
						echo form_label('Message <em>*</em>', 'message');
						$data = array(
							'name' => 'message',
							'id' => 'message',
							'class' => 'form-control wysiwyg',
							'value' => set_value('message', NULL, FALSE),
						);
						echo form_textarea($data);
						?>
					</div>
					<div class='form-group'><?php
						echo form_label('Attachments', 'file');
						$data = array(
							'name' => 'files[]',
							'id' => 'file',
							'class' => 'custom-file-input',
							'multiple' => 'multiple'
						);
						?>
						<div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="file">Choose file</label>
						</div>
						<small class='form-text text-muted'>Hold Ctrl/Command to select multiple files</small>
					</div>
					<div class="attachments_to_send">
						<ul></ul>
					</div>
				</div>
			<?php echo form_fieldset_close(); ?>
		</div>
	</div>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-envelope'></i> Send
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
