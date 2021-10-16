<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
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
					if (isset($group_info->name)) {
						$name = $group_info->name;
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
			</div>
		</div>
	<?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Add Staff</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
				<div class='form-group h-100'><?php
					echo form_label('Staff', 'field_name');
					$options = array(
						'' => 'Select'
					);
					foreach ($staff_list as $staff) {
						$options[$staff->staffID] = $staff->first . ' ' . $staff->surname;
					}
					echo form_dropdown(null, $options, null, 'id="staff_id_group" class="form-control select2" onchange="addStaff(this)"');
					?>
				</div>
				<div class="alert alert-info group-staff-alert" <?php echo (count($group_staff_list) < 1) ? 'style="display:block;"' : 'style="display:none;"' ?>>
					No users found.
				</div>
				<div class="added_staff" style="display: none;">
					<?php foreach ($group_staff_list as $staff) { ?>
						<input id="added-staff-<?php echo $staff->staffID ?>" type="hidden" name="staffId[]" value="<?php echo $staff->staffID ?>">
					<?php } ?>
				</div>
				<div class='table-responsive'>
					<table class='table table-striped table-bordered' id="group_users_table" style='margin-bottom:0; <?php echo (count($group_staff_list) < 1) ? "display:none;" : '' ?>' >
							<thead>
							<tr>
								<th >
								</th>
								<th>
									Name
								</th>
								<th>
								</th>
							</tr>
						</thead>
						<tbody>
							<?php
								$i = 0;
								foreach ($group_staff_list as $staff) {
								$i++; ?>
								<tr id="tr-staff-<?php echo $staff->staffID ?>">
									<td class="width-1p" class="staff-count"><?php echo $i; ?></td>
									<td><?php echo $staff->first . ' ' . $staff->surname ?></td>
									<td class="text-right width-1p">
										<a class="btn btn-danger btn-sm" title="Remove" onclick="removeStaff(<?php echo $staff->staffID; ?>);"><i class="far fa-trash"></i></a>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Offer &amp; Accept - Status</h3>
			</div>
		</div>
		<div class="card-body">
            <div class='multi-columns'>
                <div class='form-group'><?php
                    echo form_label('Status', 'field_status');

                    $offer_type = null;
                    if (isset($group_info->offer_type)) {
                    	$offer_type = $group_info->offer_type;
					}

                    $options = array(
                        'order' => 'Send in Order',
                        'all' => 'Send to All',
                        'auto' => 'Auto Send',
                    );
                    echo form_dropdown('offer_type', $options, $offer_type, 'id="offer_type" class="form-control select2"');
                    ?>
                </div>
            </div>
        </div>
	<?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
