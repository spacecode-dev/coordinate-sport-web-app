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
					<strong><label for="field_first_name">First Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_first_name',
					'id' => 'field_first_name',
					'class' => 'form-control',
					'value' => $search_fields['first_name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_last_name">Last Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_last_name',
					'id' => 'field_last_name',
					'class' => 'form-control',
					'value' => $search_fields['last_name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_job_title">Job Title</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_job_title',
					'id' => 'field_job_title',
					'class' => 'form-control',
					'value' => $search_fields['job_title']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_department">Permission Level</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'directors' => $this->settings_library->get_permission_level_label('directors'),
					'management' => $this->settings_library->get_permission_level_label('management'),
					'office' => $this->settings_library->get_permission_level_label('office'),
					'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
					'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
					'coaching' => $this->settings_library->get_permission_level_label('coaching')
				);
				echo form_dropdown('search_department', $options, $search_fields['department'], 'id="field_department" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_brand_id">Primary <?php echo $this->settings_library->get_label('brand'); ?></label></strong>
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
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="field_brand_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_is_active">Active</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_is_active', $options, $search_fields['is_active'], 'id="field_is_active" class="select2 form-control"');
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
				echo form_dropdown('search_activity_id', $options, $search_fields['activity_id'], 'id="field_activity_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_min_age">Min Age</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				for ($age = 5; $age <= 70; $age++) {
					$options[$age] = $age;
				}
				echo form_dropdown('search_min_age', $options, $search_fields['min_age'], 'id="field_min_age" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_max_age">Max Age</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				for ($age = 5; $age <= 70; $age++) {
					$options[$age] = $age;
				}
				echo form_dropdown('search_max_age', $options, $search_fields['max_age'], 'id="field_max_age" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_gender">Gender</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'male' => 'Male',
					'female' => 'Female'
				);
				echo form_dropdown('search_gender', $options, $search_fields['gender'], 'id="field_gender" class="select2 form-control"');
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
		No staff found.<?php if ($this->auth->user->department !== 'headcoach') { ?> Do you want to <?php echo anchor('staff/new', 'create one'); ?>?<?php } ?>
	</div>
	<?php
} else {
	?>
	<?php echo $this->pagination_library->display($page_base); ?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Job Title
						</th>
						<th>
							Permission Level
						</th>
						<th>
							Phone
						</th>
						<th>
							Message
						</th>
						<th>
							Active
						</th>
						<?php if ($this->auth->user->department !== 'headcoach') {
							?><th></th><?php
						} ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($staff->result() as $row) {
						?>
						<tr>
							<td width="10% !important">
								<div class="name participant-club">
									<?php
									$profile_pic = @unserialize($row->profile_pic);
									if($profile_pic !== FALSE){
										$args = array(
											'alt' => 'Image',
											'src' => 'attachment/staff_profile_pic/profile_pic/thumb/'.$row->staffID,
											'class' => 'responsive-img'
										);
										echo '<div class="profile_pic">' . img($args) . '</div>';
									}
									elseif (!empty($row->id_photo_path)) {
										$args = array(
											'alt' => 'Image',
											'src' => 'attachment/staff-id/' . $row->id_photo_path,
											'class' => 'responsive-img'
										);
										echo '<div class="profile_pic">' . img($args) . '</div>';
									}
									else{
										echo "<div class='img-container bg-random-".substr($row->staffID, -1)."'>".substr(trim($row->first), 0, 1)."</div>";
									}
									echo '<div>';
									if ($this->auth->user->department === 'headcoach') {
										echo $row->first . ' ' . $row->surname;
									} else {
										echo anchor('staff/edit/' . $row->staffID, trim($row->first . ' ' . $row->surname));
									}
									echo "</div>";
									?>
								</div>
							</td>
							<td>
								<?php echo $row->jobTitle ?>
							</td>
							<td>
								<?php
								echo $this->settings_library->get_permission_level_label($row->department);
								?>
							</td>
							<td>
								<?php
								$numbers = array();
								if (!empty($row->mobile_work)) {
									$numbers[] = $row->mobile_work . ' (Work)';
								}
								if (!empty($row->mobile)) {
									$numbers[] = $row->mobile;
								}
								if (!empty($row->phone)) {
									$numbers[] = $row->phone;
								}
								echo htmlspecialchars(implode(", ", $numbers));
								?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->email)) {
									?><a href="<?php echo site_url('messages/sent/staff/new/' . $row->staffID); ?>" class="btn btn-default btn-sm"><i class="far fa-envelope"></i></a><?php
								}
								?>
							</td>
							<td class="has_icon ajax_toggle">
								<?php
								if ($this->auth->user->department === 'headcoach') {
									if($row->active == 1) {
										?><span class='btn btn-success btn-sm no-action' title="Yes">
											<i class='far fa-check'></i>
										</span><?php
									} else {
										?><span class='btn btn-danger btn-sm no-action' title="No">
											<i class='far fa-times'></i>
										</span><?php
									}
								} else {
									if($row->active == 1) {
										?><a class='btn btn-success btn-sm' href="<?php echo site_url('staff/active/' . $row->staffID); ?>/no" title="Yes">
											<i class='far fa-check'></i>
										</a><?php
									} else {
										if ($limit_reached === TRUE) {
											?><span title="Staff limit reached. Contact us to upgrade or deactivate another user.">
												<a class='btn btn-danger btn-sm disabled' href="<?php echo site_url('staff/active/' . $row->staffID); ?>/yes" title="No">
													<i class='far fa-lock'></i>
												</a>
											</span><?php
										} else {
											?><a class='btn btn-danger btn-sm' href="<?php echo site_url('staff/active/' . $row->staffID); ?>/yes" title="No">
												<i class='far fa-times'></i>
											</a><?php
										}
									}
								}
								?>
							</td>
							<?php if ($this->auth->user->department !== 'headcoach') {
								?><td>
									<div class='text-right fixed-3-icons'>
										<?php
										if ($row->active == 1 && $this->auth->user->department !== 'office' && $row->staffID != $this->auth->user->staffID && ($row->department != 'directors' || $this->auth->user->department == 'directors')) {
											?><a href="<?php echo site_url('staff/access/' . $row->staffID); ?>" class='btn btn-info btn-sm' title="Log in as <?php echo $row->first . ' ' . $row->surname; ?>">
												<i class='far fa-desktop'></i>
											</a><?php
										}
										?>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('staff/edit/' . $row->staffID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<?php
										if ($this->auth->user->staffID == $row->staffID) {
											?><span class='btn btn-danger btn-sm no-action'>
												<i class='far fa-lock'></i>
											</span><?php
										} else {
											?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/remove/' . $row->staffID); ?>' title="Remove">
												<i class='far fa-trash'></i>
											</a><?php
										}
										?>
									</div>
								</td><?php
							} ?>
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
