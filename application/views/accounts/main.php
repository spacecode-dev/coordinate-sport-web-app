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
					<strong><label for="field_first_name">Company</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_company',
					'id' => 'field_company',
					'class' => 'form-control',
					'value' => $search_fields['company']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_plan_id">Plan</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if ($plans->num_rows () > 0) {
					foreach ($plans->result() as $plan) {
						$options[$plan->planID] = $plan->name;
					}
				}
				echo form_dropdown('search_plan_id', $options, $search_fields['plan_id'], 'id="field_plan_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_status">Status</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'All',
					'trial' => 'Trial',
					'paid' => 'Paid',
					'demo' => 'Demo',
					'support' => 'Support Team',
					'internal' => 'Internal',
					'admin' => 'Admin Only',
				);
				echo form_dropdown('search_status', $options, $search_fields['status'], 'id="field_status" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_contact">Contact</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_contact',
					'id' => 'field_contact',
					'class' => 'form-control',
					'value' => $search_fields['contact']
				);
				echo form_input($data);
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
if ($accounts->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No accounts found. Do you want to <?php echo anchor('accounts/new', 'create one'); ?>?
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
							Company
						</th>
						<th>
							Plan
						</th>
						<th>
							Status
						</th>
						<th>
							Users
						</th>
						<th class="text-center text-nowrap" style="width: 1px;">
							Active
						</th>
						<th class="text-center text-nowrap" style="width: 1px;">
							Proxy
						</th>
						<th class="text-center text-nowrap" style="width: 1px;">
							Booking Site
						</th>
						<th class="text-center text-nowrap" style="width: 1px;">
							Edit
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($accounts->result() as $row) {
						$is_active = FALSE;
						$is_expired = FALSE;
						// check if account active/expired
						if (in_array($row->status, array('paid', 'trial'))) {
							switch ($row->status) {
								case 'paid':
									if (empty($row->paid_until)) {
										$is_active = TRUE;
									} else if (strtotime($row->paid_until) <= strtotime(date('Y-m-d'))) {
										$is_expired = TRUE;
									} else {
										$is_active = TRUE;
									}
									break;
								case 'trial':
									if (empty($row->trial_until)) {
										$is_active = TRUE;
									} else if (strtotime($row->trial_until) <= strtotime(date('Y-m-d'))) {
										$is_expired = TRUE;
									} else {
										$is_active = TRUE;
									}
									break;
							}
						}
						?>
						<tr>
							<td class="name">
								<?php echo anchor('accounts/edit/' . $row->accountID, $row->company); ?>
							</td>
							<td>
								<?php echo $row->plan; ?>
							</td>
							<td>
								<?php
								$label_type = 'default';
								switch ($row->status) {
									case 'paid':
										$label_type = 'success';
										break;
									case 'demo':
										$label_type = 'primary';
										break;
									case 'trial':
										$label_type = 'warning';
										break;
									case 'support':
										$label_type = 'info';
										$row->status = 'Support Team';
										break;
									case 'admin':
										$label_type = 'inverse';
										$row->status = 'Admin Only';
										break;
								}
								if ($is_expired) {
									if ($row->status != 'trial') {
										$label_type = 'danger';
									}
									$row->status .= ' (Expired)';
								}
								?><span class='label label-inline label-<?php echo htmlspecialchars($label_type); ?>'><?php echo htmlspecialchars(ucwords($row->status)); ?></span>
							</td>
							<td class="text-center">
								<?php
								echo '<span title="' . intval($row->users_management) . ' Management / ' . intval($row->users_coaches) . ' Coach(es)">';
								echo intval($row->users_management) + intval($row->users_coaches);
								echo '/';
								if ($row->organisation_size == 0) {
									echo '&infin;';
								} else {
									echo $row->organisation_size;
								}
								echo '</span>';
								?>
							</td>
							<td class="has_icon ajax_toggle text-center text-nowrap">
								<?php
								if ($row->admin == 1) {
									?><span class='btn <?php if ($row->active == 1) { echo 'btn-success'; } else { echo 'btn-danger'; } ?> btn-sm no-action'>
										<i class='far fa-lock'></i>
									</span><?php
								} else if($row->active == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('accounts/active/' . $row->accountID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('accounts/active/' . $row->accountID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td class="text-center text-nowrap">
								<?php
								if ($row->accountID != $this->auth->user->accountID) {
									?>
									<a href="<?php echo site_url('accounts/access/' . $row->accountID); ?>" class='btn btn-info btn-sm' title="Access Account">
										<i class='far fa-desktop'></i>
									</a><?php
								}
								?>
							</td>
							<td class="text-center text-nowrap">
							<?php
							if ($is_active && ($row->addons_all == 1 || $row->addon_online_booking == 1)) {
								$booking_link = null;
								if (!empty($row->booking_customdomain)) {
									$booking_link = PROTOCOL . '://' . $row->booking_customdomain;
								} else if (!empty($row->booking_subdomain)) {
									$booking_link = PROTOCOL . '://' . $row->booking_subdomain . '.' . ROOT_DOMAIN;
								}
								if (!empty($booking_link)) {
									?><a href="<?php echo $booking_link; ?>" class='btn btn-primary btn-sm' title="Online Booking" target="_blank">
										<i class='far fa-book'></i>
									</a><?php
								}
							}
							?>
							</td>
							<td class="text-right text-nowrap">
								<a class='btn btn-warning btn-sm' href='<?php echo site_url('accounts/edit/' . $row->accountID); ?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
								<?php
								if ($row->admin == 1 || $this->auth->user->accountID == $row->accountID) {
									?><span class='btn btn-danger btn-sm no-action'>
											<i class='far fa-lock'></i>
										</span><?php
								} else if ($this->auth->user->department == 'directors' && ($row->active != 1 || $row->status != 'paid')) {
									?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('accounts/remove/' . $row->accountID); ?>' title="Remove">
										<i class='far fa-trash'></i>
									</a><?php
								}
								?>
							</td>
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
