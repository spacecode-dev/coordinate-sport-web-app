<div class="card card-custom">
	<div class="card-header card-header-tabs-line">
		<ul class="nav nav-tabs nav-bold nav-tabs-line settings-tabs nav-responsive" role="tablist" id="settings-tabs">
			<li role="presentation" class="nav-item">
				<a href="#staff_tab" id="staff-head" class="nav-link<?php if ($tab === 'staff') { echo ' active'; } ?>" aria-controls="staff_tab" role="tab" data-toggle="tab" aria-selected="true">
					Staff
				</a>
			</li>
			<li role="presentation" class="nav-item">
				<a href="#participants_tab" id="participants-head" class="nav-link<?php if ($tab === 'participants') { echo ' active'; } ?>" aria-controls="participants_tab" role="tab" data-toggle="tab" aria-selected="false">
					Participants
				</a>
			</li>
		</ul>
	</div>
</div>

<div class="card card-custom">
	<div class='table-responsive'>
		<div class="tab-content">
			<div role="tabpanel" id="staff_tab" class="tab-pane fade <?php if ($tab === 'staff') { echo ' active show'; } ?>" aria-labelledby="staff-head">
				<table class='table table-striped table-bordered'>
					<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Description
						</th>
						<th class="text-center block-cell">
							Active
						</th>
						<th class="text-center block-cell"></th>
					</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<?php echo anchor('settings/fields/staff', 'Staff Personal Fields'); ?>
							</td>
							<td>
								Select which fields appear in the Personal tab of Staff accounts.
							</td>
							<td class="text-center block-cell selected">
								<?php
								$data = array(
									'name' => 'staff_personal_fields',
									'id' => 'staff_personal_fields',
									'value' => 1,
									'class' => 'auto',
									'checked' => TRUE,
									'disabled' => 'disabled'
								);
								echo form_checkbox($data);
								?>
							</td>
							<td class="text-center block-cell">
								<a class='btn btn-warning btn-xs' href='<?php
								echo site_url('settings/fields/staff');?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo anchor('settings/fields/staff_recruitment', 'Staff Recruitment Fields'); ?>
							</td>
							<td>
								Select which fields appear in the Recruitment tab of Staff accounts.
							</td>
							<td class="text-center block-cell selected">
								<?php
								$data = array(
									'name' => 'staff_recruitment_fields',
									'id' => 'staff_recruitment_fields',
									'value' => 1,
									'class' => 'auto',
									'checked' => TRUE,
									'disabled' => 'disabled'
								);
								echo form_checkbox($data);
								?>
							</td>
							<td class="text-center block-cell">
								<a class='btn btn-warning btn-xs' href='<?php
								echo site_url('settings/fields/staff_recruitment');?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div role="tabpanel" id="participants_tab" class="tab-pane fade <?php if ($tab === 'participants') { echo ' active show'; } ?>" aria-labelledby="participants-head">
				<table class='table table-striped table-bordered'>
					<thead>
						<tr>
							<th>
								Name
							</th>
							<th>
								Description
							</th>
							<th class="text-center block-cell">
								Active
							</th>
							<th class="text-center block-cell"></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>
								<?php echo anchor('settings/fields/account_holder', 'Account Holder Profile Fields'); ?>
							</td>
							<td>
								Select which fields appear in the account holder profile.
							</td>
							<td class="text-center block-cell selected">
								<?php
								$data = array(
									'name' => 'account_holder_fields',
									'id' => 'account_holder_fields',
									'value' => 1,
									'class' => 'auto',
									'checked' => TRUE,
									'disabled' => 'disabled'
								);
								echo form_checkbox($data);
								?>
							</td>
							<td class="text-center block-cell">
								<a class='btn btn-warning btn-xs' href='<?php
								echo site_url('settings/fields/account_holder');?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
						</tr>
						<tr>
							<td>
								<?php echo anchor('settings/fields/participant', 'Participant Profile Fields'); ?>
							</td>
							<td>
								Select which fields appear in the participant profile.
							</td>
							<td class="text-center block-cell selected">
								<?php
								$data = array(
									'name' => 'participants_profile_fields',
									'id' => 'participants_profile_fields',
									'value' => 1,
									'class' => 'auto',
									'checked' => TRUE,
									'disabled' => 'disabled'
								);
								echo form_checkbox($data);
								?>
							</td>
							<td class="text-center block-cell">
								<a class='btn btn-warning btn-xs' href='<?php
								echo site_url('settings/fields/participant');?>' title="Edit">
									<i class='far fa-pencil'></i>
								</a>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
