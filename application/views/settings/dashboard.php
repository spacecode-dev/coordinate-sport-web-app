<?php
display_messages();

echo form_open_multipart($submit_to);
	if ($settings->num_rows() > 0) {
		$prev_section = NULL;
		$i = 0;
		foreach ($settings->result() as $setting) {
			if ($setting->section != $prev_section) {
				if ($i > 0) {
					?></tbody></table></div></div><?php
				}
				$prev_section = $setting->section;
				?><h3><?php echo ucwords($setting->section); ?></h3>
				<div class='card card-custom'>
					<div class='table-responsive'>
						<table class='table table-striped table-bordered'>
							<thead>
								<tr>
									<th>
										Alert
									</th>
									<th>
										Amber Trigger
									</th>
									<th>
										Red Trigger
									</th>
								</tr>
							</thead>
							<tbody>
                            <?php
			}
			$values = $this->settings_library->get_dashboard_trigger($setting->key);
			?><tr>
				<td width="30%"><?php echo $setting->title; ?></td>
				<td><?php
					if (!empty($setting->value_amber)) {
						$value = $values['amber'];
						if ($type == 'defaults') {
							$value = $setting->value_amber;
						}
						$value_num = substr($value, 0, strpos($value, ' '));
						$value_interval = trim(substr($value, strpos($value, ' ')));
						$data = array(
							'name' => $setting->key . '[amber][num]',
							'class' => 'form-control',
							'value' => $value_num,
							'step' => 1,
							'min' => 0
						);
						if ($values['positive_only'] != TRUE) {
							unset($data['min']);
						}
						echo form_number($data);
						$options = array(
							'day' => 'Day(s)',
							'week' => 'Week(s)',
							'month' => 'Month(s)',
							'year' => 'Year(s)',
						);
						echo form_dropdown($setting->key . '[amber][interval]', $options, $value_interval, 'class="form-control select2"');
					}
				?></td>
				<td><?php
					if (!empty($setting->value_red)) {
						$value = $values['red'];
						if ($type == 'defaults') {
							$value = $setting->value_red;
						}
						$value_num = substr($value, 0, strpos($value, ' '));
						$value_interval = trim(substr($value, strpos($value, ' ')));
						$data = array(
							'name' => $setting->key . '[red][num]',
							'class' => 'form-control',
							'value' => $value_num,
							'step' => 1,
							'min' => 0
						);
						if ($values['positive_only'] != TRUE) {
							unset($data['min']);
						}
						echo form_number($data);
						$options = array(
							'day' => 'Day(s)',
							'week' => 'Week(s)',
							'month' => 'Month(s)',
							'year' => 'Year(s)',
						);
						echo form_dropdown($setting->key . '[red][interval]', $options, $value_interval, 'class="form-control select2"');
					}
				?></td>
			</tr><?php
			$i++;
		}
		?></tbody></table></div></div><?php
	}
	?><div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
<?php echo form_close();
