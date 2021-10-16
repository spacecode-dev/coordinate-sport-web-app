<?php
display_messages();
$data = array(
	'tab' => $tab
);
$this->load->view('equipment/tabs.php', $data);
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
					<strong><label for="field_name">Name</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => $search_fields['name']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_type">Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'staff' => 'Staff',
					'org' => $this->settings_library->get_label('customer'),
					'contact' => 'Parent/Contact',
					'child' => 'Child'
				);
				echo form_dropdown('search_type', $options, $search_fields['type'], 'id="field_type" class="select2 form-control"');
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
						$options[$row->staffID] = $row->first . ' ' . $row->surname;
					}
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
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
				echo form_dropdown('search_org_id', $options, $search_fields['org_id'], 'id="field_org_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_contact_id">Parent/Contact</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($contacts->num_rows() > 0) {
					foreach ($contacts->result() as $row) {
						$options[$row->contactID] = $row->first_name . ' ' . $row->last_name;
					}
				}
				echo form_dropdown('search_contact_id', $options, $search_fields['contact_id'], 'id="field_contact_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_child_id">Child</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($children->num_rows() > 0) {
					foreach ($children->result() as $row) {
						$options[$row->childID] = $row->first_name . ' ' . $row->last_name;
					}
				}
				echo form_dropdown('search_child_id', $options, $search_fields['child_id'], 'id="field_child_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_checked_in">Checked In</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
					'yes' => 'Yes',
					'no' => 'No'
				);
				echo form_dropdown('search_checked_in', $options, $search_fields['checked_in'], 'id="field_checked_in" class="select2 form-control"');
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
if ($equipment->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No bookings found. <?php if ($this->auth->user->department != 'coaching') { ?>Do you want to <?php	echo anchor('equipment/bookings/new', 'create one'); ?>?<?php } ?>
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
							Type
						</th>
						<th>
							Booked Out By
						</th>
						<th class="min">
							Quantity
						</th>
						<th>
							Date Out
						</th>
						<th>
							Expected Back
						</th>
						<th class="min">
							Status
						</th>
						<?php
						if ($this->auth->user->department != 'coaching') {
							?><th></th><?php
						}
						?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($equipment->result() as $row) {
						?>
						<tr>
							<td class="name">
								<?php
								if ($this->auth->user->department == 'coaching') {
									echo $row->name;
								} else {
									echo anchor('equipment/bookings/edit/' . $row->bookingID, $row->name);
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->type) {
									case 'staff':
									default;
										echo ucwords($row->type);
										break;
									case 'org':
										echo $this->settings_library->get_label('customer');
										break;
									case 'contact':
										echo 'Parent/Contact';
										break;
									case 'child':
										echo 'Child';
										break;
								}
								?>
							</td>
							<td>
								<?php
								switch ($row->type) {
									case 'staff':
										echo $row->staff_label;
										break;
									case 'org':
										echo $row->org_label;
										break;
									case 'contact':
										echo $row->contact_label;
										break;
									case 'child':
										echo $row->child_label;
										break;
								}
								?>
							</td>
							<td class="min">
								<?php echo $row->quantity; ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->dateOut); ?>
							</td>
							<td>
								<?php echo mysql_to_uk_datetime($row->dateIn); ?>
							</td>
							<td class="has_icon">
								<?php
								if($row->status == 1) {
									if ($this->auth->user->department == 'coaching') {
										?><span class='btn btn-danger btn-sm no-action' title="Checked Out">
											<i class='far fa-times'></i>
										</span><?php
									} else {
										?><a href="<?php echo site_url('equipment/bookings/checkin/' . $row->bookingID); ?>" class='btn btn-danger btn-sm' title="Check In">
											<i class='far fa-times'></i>
										</a><?php
									}
								} else {
									?><span class='btn btn-success btn-sm no-action' title="Checked In">
										<i class='far fa-check'></i>
									</span><?php
								}
								?>
							</td>
							<?php
							if ($this->auth->user->department != 'coaching') {
								?><td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('equipment/bookings/edit/' . $row->bookingID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('equipment/bookings/remove/' . $row->bookingID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a>
									</div>
								</td><?php
							}
							?>
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
