<?php
display_messages();
echo form_open_multipart('settings/fields/' . $type);
	echo form_hidden(array('save' => 1));
	?>
	<div class='card card-custom'>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered' id="fields">
				<thead>
					<tr>
						<th>
							Field
						</th>
						<th>
							Show
						</th>
						<th>
							Required
						</th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ($fields->num_rows() > 0) {
						$array = array('mileage_default_start_location', 'mileage_activate_fuel_cards', 'mileage_default_mode_of_transport');
						foreach ($fields->result() as $field) {
							if($mileage_section == 1 || ($mileage_section == 0 && !in_array($field->field, $array))){
								if (in_array($field->field, ['county', 'eCounty'])) {
									$field->label = localise('county');
								}
								?><tr>
									<td><?php echo $field->label; ?></td>
									<td class="min">
										<?php
										$data = array(
											'name' => 'show[' . $field->field . ']',
											'value' => 1
										);
										$default_val = $field->show;
										if ($field->account_show !== NULL) {
											$default_val = $field->account_show;
										}
										$val = set_value('show[' . $field->field . ']', $default_val, FALSE);
										if ($val == 1) {
											$data['checked'] = 'checked';
										}
										if ($field->locked == 1) {
											$data['readonly'] = 'readonly';
											$data['disabled'] = 'disabled';
										}
										echo form_checkbox($data);
										?>
									</td>
									<td class="min">
										<?php
										$data = array(
											'name' => 'required[' . $field->field . ']',
											'value' => 1
										);
										$default_val = $field->required;
										if ($field->account_required !== NULL) {
											$default_val = $field->account_required;
										}
										$val = set_value('required[' . $field->field . ']', $default_val, FALSE);
										if ($val == 1) {
											$data['checked'] = 'checked';
										}
										if ($field->locked == 1 || $field->required == 2) {
											$data['readonly'] = 'readonly';
											$data['disabled'] = 'disabled';
										}
										echo form_checkbox($data);
										?>

									</td>
								</tr><?php
							}
						}
					}
					?>
				</tbody>
			</table>
		</div>
	</div>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
<?php echo form_close();
