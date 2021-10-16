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
if ($type == 'defaults') {
	echo form_open_multipart('accounts/' . $type . (!empty($default_section) ? '/' . $default_section : ''), array('class' => 'settings'));
} else {
	echo form_open_multipart('settings/' . $type, array('class' => 'settings'));
}
	if ($settings->num_rows() > 0) {
		if (count($tabs) > 0) {
			$tab_data = [];
			foreach ($settings->result() as $setting) {
				$tab_data[$setting->tab][] = $setting;
			}
			?>
			<div class="tab-content">
				<?php
				$i = 1;
				foreach ($tabs as $key => $label) {
					?><div role="tabpanel" class="tab-pane fade in<?php if ($i === 1) { echo ' active show'; } ?>" id="<?php echo $key; ?>"><?php
						if (array_key_exists($key, $tab_data)) {
							$prev_section = NULL;
							$i = 0;
							$close_tag = false;
							foreach ($tab_data[$key] as $setting) {

								if (isset($subsections[$setting->key]) && $subsections[$setting->key] != $prev_section) {
									if ($close_tag) {
										echo '</div></div>';
										echo form_fieldset_close();
										$close_tag = false;
									}

									$prev_section = (isset($subsections[$setting->key]) ? $subsections[$setting->key] : null);
									?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<?php if ($setting->section != 'styling') { ?>
										<div class='card-header'>
											<div class="card-title">
												<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
												<h3 class="card-label"><?php echo $subsections[$setting->key]; ?></h3>
											</div>
										</div>
									<?php } ?>
									<div class="card-body">
									<div class='
									<?php if ($setting->key == 'online_booking_header_image' || $setting->key == 'logo') {
										echo '';
									} else {
										echo 'multi-columns';
									} ?>
									'>
								<?php
								$close_tag = true;
								}else if ($setting->section != $prev_section && !isset($subsections[$setting->key])) {
									if ($close_tag) {
										echo '</div></div>';
										echo form_fieldset_close();
										$close_tag = false;
									}
									$prev_section = (isset($subsections[$setting->key]) ? $subsections[$setting->key] : $setting->section);

									echo form_fieldset('', ['class' => 'card card-custom']);
									if ($setting->section != 'styling') { ?>
										<div class='card-header'>
											<div class="card-title">
												<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
												<h3 class="card-label"><?php
												if (isset($subsections[$setting->key])) {
													echo $subsections[$setting->key];
												} else if (array_key_exists($setting->section, $titles)) {
													echo $titles[$setting->section];
												} else {
													echo ucwords($setting->section);
												};
												?></h3>
											</div>
										</div>
									<?php } ?>
									<div class="card-body">
									<div class='multi-columns'>
									<?php
									$close_tag = true;
								}

								//Only show Stripe Webhook Signing Secret Key if feature is enabled.
								if ($setting->key!="stripe_whs" || ($setting->key=="stripe_whs" && $this->auth->has_features("online_booking_subscription_module"))) {
								?>
									<div class='form-group'><?php
									echo form_label($setting->title, $setting->key);
									$data = array(
										'name' => $setting->key,
										'id' => $setting->key,
										'class' => 'form-control',
										'value' => set_value($setting->key, $this->settings_library->get($setting->key), FALSE)
									);
									if ($type == 'defaults') {
										$data['value'] = set_value($setting->key, $this->settings_library->get($setting->key, 'default'), FALSE);
									}
									if (isset($setting->readonly)) {
										if ($setting->readonly == 1) {
											$data['readonly'] = 1;
										}
									}

									if ($setting->key == 'email_from_override' && empty($data['value'])) {
										$data['value'] = $this->settings_library->get('email_from_default', 'default');
									}

									switch ($setting->type) {
										case 'text':
										default:
											if (substr($setting->type, 0, 4) == 'date') {
												$data['class'] .= ' datepicker';
												$data['value'] = mysql_to_uk_date($data['value']);
												if ($setting->type == 'date-monday') {
													$data['data-onlyday'] = 'monday';
												}
											}
											echo form_input($data);
											break;
										case 'email':
										case 'email-multiple':
											if ($setting->type == 'email-multiple') {
												$data['multiple'] = 'multiple';
											}
											echo form_email($data);
											break;
										case 'url':
											echo form_url($data);
											break;
										case 'tel':
											echo form_telephone($data);
											break;
										case 'number':
											echo form_number($data);
											break;
										case 'textarea':
										case 'html':
										case 'css':
											if (in_array($setting->type, array('css', 'html'))) {
												$data['data-editor'] = $setting->type;
											}
											echo form_textarea($data);
											break;
										case 'checkbox':
										case 'function':
											if ($data['value'] == 1) {
												$data['checked'] = TRUE;
											}
											$data['value'] = 1;
											$data['class'] = 'auto';
											if ($setting->type == 'function') {
												$data['checked'] = FALSE;
												$data['class'] .= ' confirm';
											}
											if (!empty($setting->toggle_fields)) {
												$data['data-toggle_fields'] = $setting->toggle_fields;
											}
											?><div class="checkbox-single">
												<label class="checkbox">
													<?php echo form_checkbox($data); ?>
													Yes
													<span></span>
												</label>
											</div><?php
											break;
										case 'wysiwyg':
											$data['class'] .= ' wysiwyg';
											echo form_textarea($data);
											break;
										case 'image':
											$image_data = @unserialize($data['value']);
											if ($image_data !== FALSE) {
												$args = array(
													'alt' => 'Image',
													'src' => 'attachment/setting/'. $setting->key,
													'class' => 'responsive-img',
													'style' => 'max-width:500px; max-height:100px'
												);
												if ($type == 'defaults') {
													$args['src'] .= '/default';
												}
												echo "<a href='".$this->crm_library->asset_url("attachment/setting/". $setting->key)."' data-fancybox='gallery' class='profileimage' >";
												echo '<p>' . img($args) . '</p>';
												echo "</a>";
												if ($setting->key == 'online_booking_header_image') {
													$checkbox_data = array(
														'name' => 'remove_' . $setting->key,
														'class' => 'auto',
														'value' => 1
													);
													?><div class="checkbox-single">
														<label class="checkbox">
															<?php echo form_checkbox($checkbox_data); ?>
															Remove <?php echo $setting->title; ?>
															<span></span>
														</label>
													</div><?php
												}
												echo form_label('Replace ' . $setting->title, $setting->key);
											}
											unset($data['value'], $data['class']);
											$data['class'] = ' custom-file-input';
											?><div class="custom-file">
												<?php echo form_upload($data); ?>
												<label class="custom-file-label" for="<?php echo $setting->key; ?>">Choose file</label>
											</div><?php
											break;
										case 'staff':
											$options = array(
												'' => 'Select'
											);
											if ($staff_list->num_rows() > 0) {
												foreach ($staff_list->result() as $row) {
													$options[$row->staffID] = $row->first . ' ' . $row->surname;
												}
											}
											echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
											break;
										case 'brand':
											$options = array(
												'' => 'Select'
											);
											if ($brand_list->num_rows() > 0) {
												foreach ($brand_list->result() as $row) {
													$options[$row->brandID] = $row->name;
												}
											}
											echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
											break;
										case 'select':
											$options = array(
												'' => 'Select'
											);
											$options_list = explode("\n", $setting->options);
											if (count($options_list) > 0) {
												foreach ($options_list as $option) {
													$option_parts = explode(" : ", $option);
													if (count($option_parts) == 2) {
														$options[$option_parts[0]] = $option_parts[1];
													}
												}
											}
											echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
											break;
										case 'permission-levels':
											$options = array(
												'directors' => $this->settings_library->get_permission_level_label('directors'),
												'management' => $this->settings_library->get_permission_level_label('management'),
												'office' => $this->settings_library->get_permission_level_label('office'),
												'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
												'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
												'coaching' => $this->settings_library->get_permission_level_label('coaching')
											);
											echo form_dropdown($setting->key . '[]', $options, $data['value'], 'id="' . $setting->key . '"  multiple="multiple" class="form-control select2"');
											break;
									}
									if (!empty($setting->instruction)) {
										?><small class="text-muted form-text"><?php echo str_replace(array('{site_url}', '{account_id}'), array(site_url(), $this->auth->user->accountID), $setting->instruction); ?></small><?php
									}
								?></div><?php
								}
								$i++;
							}
							if ($close_tag) {
								echo '</div></div>';
							}
							echo form_fieldset_close();
						}
					?></div><?php
				}
			?></div><?php
		} else {
			$prev_section = NULL;
			$i = 0;
			$close_tag = false;
			foreach ($settings->result() as $setting) {

				if (isset($subsections[$setting->key]) && $subsections[$setting->key] != $prev_section) {
					if ($close_tag) {
						echo '</div></div>';
						echo form_fieldset_close();
						$close_tag = false;
					}

					$prev_section = (isset($subsections[$setting->key]) ? $subsections[$setting->key] : null);
					?>
					<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
					<?php if ($setting->section != 'styling') { ?>
						<div class='card-header'>
							<div class="card-title">
								<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
								<h3 class="card-label"><?php echo $subsections[$setting->key]; ?></h3>
							</div>
						</div>
					<?php } ?>
					<div class="card-body">
					<div class='
					<?php if ($setting->key == 'online_booking_header_image' || $setting->key == 'logo') {
						echo '';
					} else {
						echo 'multi-columns';
					} ?>
					'>
				<?php
				$close_tag = true;
				}else if ($setting->section != $prev_section && !isset($subsections[$setting->key])) {
					if ($close_tag) {
						echo '</div></div>';
						echo form_fieldset_close();
						$close_tag = false;
					}
					$prev_section = (isset($subsections[$setting->key]) ? $subsections[$setting->key] : $setting->section);

					echo form_fieldset('', ['class' => 'card card-custom']);
					if ($setting->section != 'styling') { ?>
						<div class='card-header'>
							<div class="card-title">
								<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
								<h3 class="card-label"><?php
								if (isset($subsections[$setting->key])) {
									echo $subsections[$setting->key];
								} else if (array_key_exists($setting->section, $titles)) {
									echo $titles[$setting->section];
								} else {
									echo ucwords($setting->section);
								};
								?></h3>
							</div>
						</div>
					<?php } ?>
					<div class="card-body">
					<div class='multi-columns'>
					<?php
					$close_tag = true;
				} ?>

					<div class='form-group'><?php
					echo form_label($setting->title, $setting->key);
					$data = array(
						'name' => $setting->key,
						'id' => $setting->key,
						'class' => 'form-control',
						'value' => set_value($setting->key, $this->settings_library->get($setting->key), FALSE)
					);
					if ($type == 'defaults') {
						$data['value'] = set_value($setting->key, $this->settings_library->get($setting->key, 'default'), FALSE);
					}
					if (isset($setting->readonly)) {
						if ($setting->readonly == 1) {
							$data['readonly'] = 1;
						}
					}

					if ($setting->key == 'email_from_override' && empty($data['value'])) {
						$data['value'] = $this->settings_library->get('email_from_default', 'default');
					}

					switch ($setting->type) {
						case 'text':
						default:
							if (substr($setting->type, 0, 4) == 'date') {
								$data['class'] .= ' datepicker';
								$data['value'] = mysql_to_uk_date($data['value']);
								if ($setting->type == 'date-monday') {
									$data['data-onlyday'] = 'monday';
								}
							}
							echo form_input($data);
							break;
						case 'email':
						case 'email-multiple':
							if ($setting->type == 'email-multiple') {
								$data['multiple'] = 'multiple';
							}
							echo form_email($data);
							break;
						case 'url':
							echo form_url($data);
							break;
						case 'tel':
							echo form_telephone($data);
							break;
						case 'number':
							echo form_number($data);
							break;
						case 'textarea':
						case 'html':
						case 'css':
							if (in_array($setting->type, array('css', 'html'))) {
								$data['data-editor'] = $setting->type;
							}
							echo form_textarea($data);
							break;
						case 'checkbox':
						case 'function':
							if ($data['value'] == 1) {
								$data['checked'] = TRUE;
							}
							$data['value'] = 1;
							$data['class'] = 'auto';
							if ($setting->type == 'function') {
								$data['checked'] = FALSE;
								$data['class'] .= ' confirm';
							}
							if (!empty($setting->toggle_fields)) {
								$data['data-toggle_fields'] = $setting->toggle_fields;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div><?php
							break;
						case 'wysiwyg':
							$data['class'] .= ' wysiwyg';
							echo form_textarea($data);
							break;
						case 'image':
							$image_data = @unserialize($data['value']);
							if ($image_data !== FALSE) {
								$args = array(
									'alt' => 'Image',
									'src' => 'attachment/setting/'. $setting->key,
									'class' => 'responsive-img',
									'style' => 'max-width:500px; max-height:100px'
								);
								if ($type == 'defaults') {
									$args['src'] .= '/default';
								}
								echo "<a href='".$this->crm_library->asset_url("attachment/setting/". $setting->key)."' data-fancybox='gallery' class='profileimage' >";
								echo '<p>' . img($args) . '</p>';
								echo "</a>";
								if ($setting->key == 'online_booking_header_image') {
									$checkbox_data = array(
										'name' => 'remove_' . $setting->key,
										'class' => 'auto',
										'value' => 1
									);
									?><div class="checkbox-single">
										<label class="checkbox">
											<?php echo form_checkbox($checkbox_data); ?>
											Remove <?php echo $setting->title; ?>
											<span></span>
										</label>
									</div><?php
								}
								echo form_label('Replace ' . $setting->title, $setting->key);
							}
							unset($data['value'], $data['class']);
							$data['class'] = ' custom-file-input';
							?><div class="custom-file">
								<?php echo form_upload($data); ?>
								<label class="custom-file-label" for="<?php echo $setting->key; ?>">Choose file</label>
							</div><?php
							break;
						case 'staff':
							$options = array(
								'' => 'Select'
							);
							if ($staff_list->num_rows() > 0) {
								foreach ($staff_list->result() as $row) {
									$options[$row->staffID] = $row->first . ' ' . $row->surname;
								}
							}
							echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
							break;
						case 'brand':
							$options = array(
								'' => 'Select'
							);
							if ($brand_list->num_rows() > 0) {
								foreach ($brand_list->result() as $row) {
									$options[$row->brandID] = $row->name;
								}
							}
							echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
							break;
						case 'select':
							$options = array(
								'' => 'Select'
							);
							$options_list = explode("\n", $setting->options);
							if (count($options_list) > 0) {
								foreach ($options_list as $option) {
									$option_parts = explode(" : ", $option);
									if (count($option_parts) == 2) {
										$options[$option_parts[0]] = $option_parts[1];
									}
								}
							}
							echo form_dropdown($setting->key, $options, $data['value'], 'id="' . $setting->key . '" class="form-control select2"');
							break;
						case 'permission-levels':
							$options = array(
								'directors' => $this->settings_library->get_permission_level_label('directors'),
								'management' => $this->settings_library->get_permission_level_label('management'),
								'office' => $this->settings_library->get_permission_level_label('office'),
								'headcoach' => $this->settings_library->get_permission_level_label('headcoach'),
								'fulltimecoach' => $this->settings_library->get_permission_level_label('fulltimecoach'),
								'coaching' => $this->settings_library->get_permission_level_label('coaching')
							);
							echo form_dropdown($setting->key . '[]', $options, $data['value'], 'id="' . $setting->key . '"  multiple="multiple" class="form-control select2"');
							break;
					}
					if (!empty($setting->instruction)) {
						?><small class="text-muted form-text"><?php echo str_replace(array('{site_url}', '{account_id}'), array(site_url(), $this->auth->user->accountID), $setting->instruction); ?></small><?php
					}
				?></div><?php
				$i++;
			}
			if ($close_tag) {
				echo '</div></div>';
			}
			echo form_fieldset_close();
		}
	} else {
		?><p>No settings</p><?php
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
	</div>
<?php echo form_close();
