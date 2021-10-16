<?php
display_messages();
if (count($tabs) > 0) {
	?><div class="card card-custom">
		<div class="card-header card-header-tabs-line">
			<ul class="nav nav-tabs nav-bold nav-tabs-line settings-tabs nav-responsive" role="tablist" id="settings-tabs"><?php
				$i = 1;
				foreach ($tabs as $key => $label) {
					?><li role="presentation" class="nav-item"><a href="#<?php echo $key; ?>" class="nav-link<?php if ($i === 1) { echo ' active'; } ?>" aria-controls="<?php echo $key; ?>" role="tab" data-toggle="tab"><?php echo $label; ?></a></li><?php
					$i++;
				}
			?></ul>
		</div>
	</div><?php
}
if (!empty($sections)) {
	echo form_open_multipart('settings/listing/' . $current_page, array('class' => 'settings'));
	// if has tabs
		if (count($tabs) > 0) {
			$tab_data = [];
			foreach ($sections as $item) {
				$tab_data[$item->tab][] = $item;
			}
			?>
			<div class="tab-content">
				<?php
				$i = 1;
				foreach ($tabs as $key => $label) {
					?><div role="tabpanel" class="tab-pane fade in<?php if ($i === 1) { echo ' active show'; } ?>" id="<?php echo $key; ?>">
						<div class='card card-custom'>
							<div class='table-responsive'>
								<table class='table table-striped table-bordered checkbox-enable-td'>
									<thead>
										<th>Name</th>
										<th class="hidden-xs">Description</th>
										<?php if ($type != 'defaults') { ?>
											<th>Active</th>
										<?php } ?>
										<th></th>
									</thead>
									<tbody>
										<tr>
											<?php
											if (array_key_exists($key, $tab_data)) {
												foreach($tab_data[$key] as $item){
													if($item->key === "account_renewal_email_alert_emailsms" && !$this->auth->has_features('accounts')){
														continue;
													}
													?>
													<tr>
														<td><?php
															if ($type != 'defaults') {
																echo anchor('settings/listing/' . $current_page . '/' . $item->key, $item->title);
															} else {
																echo anchor('accounts/defaults/listing/' . str_replace('defaults_', '', $current_page) . '/' . $item->key, $item->title);
															} ?>
														</td>
														<td class="hidden-xs"><?php echo $item->description; ?></td>
														<?php if ($type != 'defaults') { ?>
															<td class="text-center">
																<?php
																if(empty($item->toggle_fields)){
																	$data = [
																		'checked' => true,
																		'disabled' => 'disabled'
																	];
																	echo form_checkbox($data);
																// multiple toggle fields
																} elseif (count(explode(',', $item->toggle_fields)) > 1) {
																	$toggle_fields = array_filter(explode(',', $item->toggle_fields));
																	$active_toggles = 0;
																	// count active toggles
																	foreach ($toggle_fields as $field) {
																		if ($this->settings_library->get($field)) {
																			$active_toggles++;
																		}
																	}
																	$data = array(
																		'name' => $item->toggle_fields,
																		'id' => $item->key,
																		'value' => 1,
																		'class' => 'auto'
																	);
																	if ($active_toggles > 0) {
																		$data['checked'] = TRUE;
																	}
																	// if some toggles on, but not all, show message on hover
																	if ($active_toggles > 0 && $active_toggles < count($toggle_fields)) {
																		$data['title'] = 'Not all toggles activated within this section';
																	}
																	echo form_checkbox($data);
																// single toggle field
																} elseif (count(explode(',', $item->toggle_fields)) == 1) {
																	$data = array(
																		'name' => $item->toggle_fields,
																		'id' => $item->key,
																		'value' => set_value($item->toggle_fields, $this->settings_library->get($item->toggle_fields), FALSE),
																		'class' => 'auto'
																	);
																	if ($data['value'] == 1) {
																		$data['checked'] = TRUE;
																	}

																	$data['value'] = 1;

																	echo form_checkbox($data);
																} ?>
															</td>
														<?php } ?>
														<td class="text-center">
															<a class='btn btn-warning btn-xs' href='<?php
															if ($type != 'defaults') {
																echo site_url('settings/listing/' . $current_page . '/' . $item->key);
															} else {
																echo site_url('accounts/defaults/listing/' . str_replace('defaults_', '', $current_page) . '/' . $item->key)  ;
															} ?>' title="Edit">
																<i class='far fa-pencil'></i>
															</a>
														</td>
													</tr>
												<?php }
											}
											?>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div><?php
				$i++;
				}
				?>
			</div>
	<?php } else { ?>
		<div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered checkbox-enable-td'>
					<thead>
						<th class="col-sm-10">Name</th>
						<?php if ($type != 'defaults') { ?>
						<th class="col-sm-1">Active</th>
						<?php } ?>
						<th class="col-sm-1"></th>
					</thead>
					<tbody>
						<?php foreach($sections as $item) {
							?>
							<tr>
								<td><?php
									if ($type != 'defaults') {
										echo anchor('settings/listing/' . $current_page . '/' . $item->key, $item->title);
									} else {
										echo anchor('accounts/defaults/listing/' . str_replace('defaults_', '', $current_page) . '/' . $item->key, $item->title);
									} ?>
								</td>
								<?php if ($type != 'defaults') { ?>
								<td class="text-center">
									<?php if(empty($item->toggle_fields)){
										$data = [
											'checked' => true,
											'disabled' => 'disabled'
										];
										echo form_checkbox($data);
									} elseif (count(explode(',', $item->toggle_fields)) > 1) {
										$data = [
											'checked' => false,
											'disabled' => 'disabled'
										];
										echo form_checkbox($data);
									} elseif (count(explode(',', $item->toggle_fields)) == 1) {
										$data = array(
											'name' => $item->toggle_fields,
											'id' => $item->key,
											'value' => set_value($item->toggle_fields, $this->settings_library->get($item->toggle_fields), FALSE),
											'class' => 'auto'
										);
										if ($data['value'] == 1) {
											$data['checked'] = TRUE;
										}

										$data['value'] = 1;

										echo form_checkbox($data);
									} ?>
								</td>
								<?php } ?>
								<td class="text-center">
									<a class='btn btn-warning btn-xs' href='<?php
									if ($type != 'defaults') {
										echo site_url('settings/listing/' . $current_page . '/' . $item->key);
									} else {
										echo site_url('accounts/defaults/listing/' . str_replace('defaults_', '', $current_page) . '/' . $item->key)  ;
									} ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
	<?php }
	if ($type != 'defaults') { ?>
		<div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-save'></i> Save
			</button>
		</div>
	<?php }
	echo form_close();
}
