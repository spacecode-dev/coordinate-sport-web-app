<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);

	$data['tab'] = "man-qualifications";
	$this->load->view('staff/qualifications-tabs.php', $data);
}

echo form_open_multipart($submit_to);
echo form_fieldset('', ['class' => 'card card-custom']);
	?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Mandatory Qualifications</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th class="center">
							Valid
						</th>
						<th>
							Issue Date
						</th>
						<th>
							Expiry Date
						</th>
						<th>
							Reference
						</th>
						<th>
							<abbr title="Not Required">NR</abbr>
						</th>
                        <th>
                            Attachment
                        </th>
                        <th>
                        </th>
					</tr>
				</thead>
				<tbody>
					<?php
					$quals = array(
						'first' => 'First Aid',
						'child' => 'Child Protection',
						'fsscrb' => 'Company DBS',
						'othercrb' => 'Other DBS'
					);
					foreach ($quals as $key => $label) {
						?><tr>
							<td><?php echo $label; ?></td>
							<td class="center">
								<?php
								$field = 'qual_' . $key;
								$data = array(
									'name' => $field,
									'id' => $field,
									'value' => 1
								);
								$val = NULL;
								if (isset($staff_info->$field)) {
									$val = $staff_info->$field;
								}
								if (set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE) == 1) {
									$data['checked'] = TRUE;
								}
								echo form_checkbox($data);
								?>
							</td>
							<td>
								<?php
								$field = 'qual_' . $key . '_issue_date';
								$val = NULL;
								if (isset($staff_info->$field) && !empty($staff_info->$field)) {
									$val = date("d/m/Y", strtotime($staff_info->$field));
								}
								$data = array(
									'name' => $field,
									'id' => $field,
									'class' => 'datepicker form-control',
									'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
									'maxlength' => 10
								);
								echo form_input($data);
								?>
							</td>
							<td>
								<?php
								$field = 'qual_' . $key . '_expiry_date';
								$val = NULL;
								if (isset($staff_info->$field) && !empty($staff_info->$field)) {
									$val = date("d/m/Y", strtotime($staff_info->$field));
								}
								$data = array(
									'name' => $field,
									'id' => $field,
									'class' => 'datepicker form-control',
									'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
									'maxlength' => 10
								);
								echo form_input($data);
								?>
							</td>
							<td>
								<?php
								if (!in_array($key, array('first', 'child'))) {
									$field = 'qual_' . $key . '_ref';
									$val = NULL;
									if (isset($staff_info->$field)) {
										$val = $staff_info->$field;
									}
									$data = array(
										'name' => $field,
										'id' => $field,
										'class' => 'form-control',
										'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
										'maxlength' => 30
									);
									echo form_input($data);
								}
								?>
							</td>
							<td class="center">
								<?php
								$field = 'qual_' . $key . '_not_required';
								$data = array(
									'name' => $field,
									'id' => $field,
									'value' => 1
								);
								$val = NULL;
								if (isset($staff_info->$field)) {
									$val = $staff_info->$field;
								}
								if (set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE) == 1) {
									$data['checked'] = TRUE;
								}
								echo form_checkbox($data);
								?>
							</td>
                            <td>
                                <?php if(isset($attachments[$key])) { ?>
                                    <?php echo anchor('attachment/staff/' . $attachments[$key]->path, $attachments[$key]->name, 'target="_blank"'); ?>
                                <?php } else {?>
                                    <div class='form-group'><?php
                                    $data = array(
                                        'name' => "files[" . $key . "]",
                                        'id' => 'file',
                                        'class' => ''
                                    );
                                    echo form_upload($data);
                                    ?></div>
                                <?php } ?>
                            </td>
                        <td>
                            <?php if(isset($attachments[$key])) { ?>
                                <a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/attachments/remove/' . $attachments[$key]->attachmentID . '/staff_quals'); ?>' title="Remove Attachment">
                                    <i class='far fa-trash'></i>
                                </a>
                            <?php } else {?>
                                <a class='btn btn-danger btn-sm disabled confirm-delete' href='#' title="Remove">
                                    <i class='far fa-trash'></i>
                                </a>
                            <?php } ?>
                        </td>
						</tr><?php
					}
					if (count($mandatory_quals) > 0) {
						foreach ($mandatory_quals as $qualID => $row) {
							$data = array(
								'name' => 'mandatory_quals[]',
								'id' => 'mandatory_qual_' . $qualID,
								'value' => $qualID
							);
							if (isset($mandatory_quals_array[$qualID]->valid) && $mandatory_quals_array[$qualID]->valid == 1) {
								$data['checked'] = TRUE;
							}
							?><tr>
								<td><?php echo $row->name; ?></td>
								<td class="center">
									<?php echo form_checkbox($data); ?>
								</td>
								<td>
									<?php
										if($row->require_issue_expiry_date == 1) {
										$field = 'mandatory_qual_'.$qualID.'_issue_date';
										$val = NULL;
										if (isset($mandatory_quals_array[$qualID]->issue_date) && !empty($mandatory_quals_array[$qualID]->issue_date)) {
											$val = date("d/m/Y", strtotime($mandatory_quals_array[$qualID]->issue_date));
										}
										$data = array(
											'name' => $field,
											'id' => $field,
											'class' => 'datepicker form-control',
											'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
											'maxlength' => 10
										);
										echo form_input($data);
									}
									?>
								</td>
								<td>
									<?php
									if($row->require_issue_expiry_date == 1) {
										$field = 'mandatory_qual_'.$qualID.'_expiry_date';
										$val = NULL;
										if (isset($mandatory_quals_array[$qualID]->expiry_date) && !empty($mandatory_quals_array[$qualID]->expiry_date)) {
											$val = date("d/m/Y", strtotime($mandatory_quals_array[$qualID]->expiry_date));
										}
										$data = array(
											'name' => $field,
											'id' => $field,
											'class' => 'datepicker form-control',
											'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
											'maxlength' => 10
										);
										echo form_input($data);
									}
									?>
								</td>
								<td>
									<?php 
									if($row->require_reference == 1){
										$field = 'mandatory_qual_' . $qualID . '_ref';
										$val = NULL;
										if (isset($mandatory_quals_array[$qualID]->reference)) {
											$val = $mandatory_quals_array[$qualID]->reference;
										}
										$data = array(
											'name' => $field,
											'id' => $field,
											'class' => 'form-control',
											'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($val), FALSE),
											'maxlength' => 30
										);
										echo form_input($data);
									}
									?>
								</td>
								<td class="center">
									<?php
									$data = array(
										'name' => 'mandatory_quals_not_required[]',
										'id' => 'mandatory_qual_' . $qualID . '_not_required',
										'value' => $qualID
									);
									if (isset($mandatory_quals_array[$qualID]->not_required) && $mandatory_quals_array[$qualID]->not_required == 1) {
										$data['checked'] = TRUE;
									}
									echo form_checkbox($data);
									?>
								</td>
                            <td>
                                <?php if(isset($attachments[$qualID])) { ?>
                                    <?php echo anchor('attachment/staff/' . $attachments[$qualID]->path, $attachments[$qualID]->name, 'target="_blank"'); ?>
                                <?php } else {?>
                                    <div class='form-group'><?php
                                        $data = array(
                                            'name' => "files[" . $qualID . "]",
                                            'id' => 'file',
                                            'class' => ''
                                        );
                                        echo form_upload($data);
                                        ?></div>
                                <?php } ?>
                            </td>
                            <td><?php if(isset($attachments[$qualID])) { ?>
                                    <a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('staff/attachments/remove/' . $attachments[$qualID]->attachmentID . '/staff_quals'); ?>' title="Remove">
                                        <i class='far fa-trash'></i>
                                    </a>
                                <?php } else {?>
                                    <a class='btn btn-danger btn-sm disabled confirm-delete' href='#' title="Remove">
                                        <i class='far fa-trash'></i>
                                    </a>
                                <?php } ?>
                            </td>
							</tr><?php

						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
<?php
echo form_fieldset_close();
if (count($mandatory_quals) > 0 && $payroll_enabled) {
	echo form_fieldset('', ['class' => 'card card-custom']); ?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
			<h3 class="card-label">Pay Rate Level</h3>
		</div>
	</div>
	<div class="card-body">
		<div class="form-group">
			<?php echo form_dropdown('qual_preferred_for_pay_rate', $mandatory_quals_rates, $mandatory_qual_preferred_for_pay_rate, 'id="preferred_for_pay_rate" class="select2 form-control"'); ?>
		</div>
	</div>
	<?php
	echo form_fieldset_close();
}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
	<?php
echo form_close();
