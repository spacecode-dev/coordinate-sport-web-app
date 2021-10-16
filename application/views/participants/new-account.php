<?php
display_messages();
?>
<div class="row">
	<div class='col-sm-12'>
		<input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
		<div class="fuelux">
			<div class="wizard" data-initialize="wizard" id="form-wizard">
				<div class="steps-container">
					<ul class="steps">
						<li data-step="1" data-role="ah" data-name="campaign" class="active">
							<div class="title">
								<i class="badge">&nbsp;</i>
								Main Account Holder
							</div>
						</li>
						<li class="new-content text-muted" data-action="p">
							<div class="title">
								<i class="badge far fa-plus"></i>
								Add Participant
							</div>
						</li>
						<li class="new-content add-ah text-muted" data-action="ah">
							<div class="title">
								<i class="badge far fa-plus"></i>
								Add Account Holder
							</div>
						</li>
					</ul>
				</div>
				<div class="step-content">
					<div id="overlay" style="display:none;">
						<div class="spinner"></div>
						<br/>
						Loading...
					</div>
					<div class="step-pane active sample-pane account-holder-content" data-step="1" data-primary="1">
						<?php echo form_open_multipart($submit_to, 'class="new_family family" data-role="ah"'); ?>
						<div class='row'>
							<div class='col-sm-12'>
								<div class='box bordered-box'>
									<div class="alert alert-danger" style="display: none;">
										<p><i class="far fa-exclamation-circle"></i> Please correct the following errors:</p>
										<ul></ul>
									</div>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
											<h3 class="card-label">Individual</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<?php
											if (show_field('title', $account_holder_fields)) { ?>
												<div class='form-group'><?php
													echo field_label('title', $account_holder_fields);
													$options = array(
														'' => 'Select',
														'mr' => 'Mr',
														'mrs' => 'Mrs',
														'miss' => 'Miss',
														'ms' => 'Ms',
														'dr' => 'Dr'
													);
													echo form_dropdown('title', $options, set_value('title', NULL, FALSE), 'class="form-control select2"');
													?></div>
											 <?php } ?>
											<div id="contactcheck_result" style="display:none;">
												<div class="alert alert-danger">
													<h4><i class="far fa-exclamation-circle"></i> Error</h4>
													<div class="result"></div>
												</div>
											</div>
											<?php if (show_field('first_name', $account_holder_fields)) { ?>
												<div class='form-group'><?php
													echo field_label('first_name', $account_holder_fields);
													$data = array(
														'name' => 'first_name',
														'id' => 'first_name',
														'class' => 'form-control',
														'value' => set_value('first_name', NULL, FALSE),
														'maxlength' => 100,
														'required'=>''
													);
													echo form_input($data);
													?></div>
												<?php
											}
											if (show_field('last_name', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('last_name', $account_holder_fields);
												$data = array(
													'name' => 'last_name',
													'id' => 'last_name',
													'class' => 'form-control',
													'value' => set_value('last_name', NULL, FALSE),
													'maxlength' => 100
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('gender', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('gender', $account_holder_fields);
												$options = array(
													'' => 'Select',
													'male' => 'Male',
													'female' => 'Female',
													'please_specify' => 'Other (please specify)',
													'other' => 'Prefer not to say'
												);
												?>
												<div class="d-flex">
													<?php
													echo form_dropdown('gender', $options, set_value('gender', NULL, FALSE), 'class="form-control select2"');
													$data = array(
														'name' => 'gender_specify',
														'id' => 'gender_specify',
														'placeholder' => 'Please specify',
														'class' => 'ml-2 form-control'.(set_value('gender', NULL, FALSE)!="please_specify" ? " d-none" : ""),
														'value' => set_value('gender_specify', FALSE),
														'maxlength' => 100
													);
													echo form_input($data);
													?>
												</div>
											</div>
												<?php
											}
											if (show_field('dob', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('dob', $account_holder_fields);
												$data = array(
													'name' => 'dob',
													'class' => 'form-control datepicker datepicker-dob',
													'value' => set_value('dob', NULL, FALSE),
													'maxlength' => 10
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('medical', $account_holder_fields)) {
											?>
											<div class="form-group"><?php
												echo field_label('profile_pic', $account_holder_fields);
												$data = array(
												'name' => 'profile_pic',
												'class' => 'custom-file-input'
												); ?>
												<div class="custom-file">
													<?php echo form_upload($data); ?>
													<label class="custom-file-label" for="logo">Choose file</label>
												</div>
												<small class="text-muted">Recommended size is 200px by 200px. Thumbnails will be cropped into a square.</small>
											</div>
												<?php
											}
											if (show_field('medical', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('medical', $account_holder_fields);
												$data = array(
													'name' => 'medical',
													'id' => 'medical',
													'class' => 'form-control',
													'value' => set_value('medical', NULL, FALSE)
												);
												echo form_textarea($data);
												?></div>
												<?php
											}
											if (show_field('behavioural_information', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('behavioural_information', $account_holder_fields);
												$data = array(
													'name' => 'behavioural_information',
													'id' => 'behavioural_information',
													'class' => 'form-control',
													'value' => set_value('behavioural_information', NULL, FALSE)
												);
												echo form_textarea($data);
												?></div>
												<?php
											}
											if (show_field('disability', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('disability', $account_holder_fields);
												foreach ($this->settings_library->disabilities as $p => $checkbox) {
													$data = array(
														'name' => 'disability['.$p.']',
														'id' => 'disability_'.$p,
														'value' => '1',
														'checked' => (bool)set_value('disability['.$p.']', false)
													);
													?>
													<div class="checkbox-single">
														<label class="checkbox">
															<?php
															echo form_checkbox($data);
															echo $checkbox;
															?>
															<span></span>
														</label>
													</div>
													<?php
												}
												?></div>
												<?php
											}
											if (show_field('disability_info', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('disability_info', $account_holder_fields);
												$data = array(
													'name' => 'disability_info',
													'id' => 'disability_info',
													'class' => 'form-control',
													'value' => set_value('disability_info', NULL, FALSE)
												);
												echo form_textarea($data);
												?></div>
											<?php } ?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>

									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
											<h3 class="card-label">Diversity</h3>
										</div>
										<div class="card-toolbar">
											<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
												<i class="ki ki-arrow-down icon-nm"></i>
											</a>
										</div>
									</div>
									<div class="card-body">
										<div class="multi-columns">
											<?php if (show_field('ethnic_origin', $account_holder_fields)) { ?>
											<div class='form-group'><?php
												echo field_label('ethnic_origin', $account_holder_fields);
												$options = $this->settings_library->ethnic_origins;
												echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', NULL, FALSE), 'class="form-control select2"');
												?>
											</div>
											<?php }
											if (show_field('religion', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('religion', $account_holder_fields);
												$options = $this->settings_library->religions;
												?>
												<div class="d-flex"><?php
													echo form_dropdown('religion', $options, set_value('religion', 'prefer_not_to_say', FALSE), 'class="form-control select2"');
													$data = array(
														'name' => 'religion_specify',
														'id' => 'religion_specify',
														'placeholder' => 'Please specify',
														'class' => 'ml-2 form-control'.(set_value('religion', 'prefer_not_to_say', FALSE)!="please_specify" ? " d-none" : ""),
														'value' => set_value('religion_specify', NULL, FALSE),
														'maxlength' => 100
													);
													echo form_input($data);
													?>
												</div>
											</div>
											<?php } ?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
											<h3 class="card-label">Address</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<?php
											if (show_field('address1', $account_holder_fields) || show_field('address2', $account_holder_fields) || show_field('address3', $account_holder_fields)) {
											if(show_field('address1', $account_holder_fields)){?>
											<div class='form-group'><?php
												echo field_label('address1', $account_holder_fields);
												$data = array(
													'name' => 'address1',
													'id' => 'address1',
													'class' => 'form-control',
													'value' => set_value('address1', NULL, FALSE),
													'maxlength' => 255
												);
												echo form_input($data);
												}
												if(show_field('address2', $account_holder_fields)){
												?><br /><?php
												$data = array(
													'name' => 'address2',
													'id' => 'address2',
													'class' => 'form-control',
													'value' => set_value('address2', NULL, FALSE),
													'maxlength' => 255
												);
												echo form_input($data);
												}
												if(show_field('address3', $account_holder_fields)){
												?><br /><?php
												$data = array(
													'name' => 'address3',
													'id' => 'address3',
													'class' => 'form-control',
													'value' => set_value('address3', NULL, FALSE),
													'maxlength' => 255
												);
												echo form_input($data);
												}
												?></div>
												<?php
											}
											if (show_field('town', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('town', $account_holder_fields);
												$data = array(
													'name' => 'town',
													'id' => 'town',
													'class' => 'form-control',
													'value' => set_value('town', NULL, FALSE),
													'maxlength' => 50
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('county', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('county', $account_holder_fields);
												$data = array(
													'name' => 'county',
													'id' => 'county',
													'class' => 'form-control',
													'value' => set_value('county', NULL, FALSE),
													'maxlength' => 50
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('postcode', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('postcode', $account_holder_fields);
												$data = array(
													'name' => 'postcode',
													'id' => 'postcode',
													'class' => 'form-control',
													'value' => set_value('postcode', NULL, FALSE),
													'maxlength' => 10
												);
												echo form_input($data);
												?></div>
											<?php } ?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-phone text-contrast'></i></span>
											<h3 class="card-label">Contact Details</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<?php
											if (show_field('mobile', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												$label = 'Mobile';
												if ($this->settings_library->get('require_mobile') == 1) {
													$label .= ' <em>*</em>';
												}
												echo field_label('mobile', $account_holder_fields);
												$data = array(
													'name' => 'mobile',
													'id' => 'mobile',
													'class' => 'form-control',
													'value' => set_value('mobile', NULL, FALSE),
													'maxlength' => 20
												);
												echo form_tel($data);
												?></div>
												<?php
											}
											if (show_field('phone', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('phone', $account_holder_fields);
												$data = array(
													'name' => 'phone',
													'id' => 'phone',
													'class' => 'form-control',
													'value' => set_value('phone', NULL, FALSE),
													'maxlength' => 20
												);
												echo form_tel($data);
												?></div>
												<?php
											}
											if (show_field('workPhone', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('workPhone', $account_holder_fields);
												$data = array(
													'name' => 'workPhone',
													'id' => 'workPhone',
													'class' => 'form-control',
													'value' => set_value('workPhone', NULL, FALSE),
													'maxlength' => 20
												);
												echo form_tel($data);
												?></div>
												<?php
											}
											if (show_field('email', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('email', $account_holder_fields);
												$data = array(
													'name' => 'email',
													'id' => 'email',
													'class' => 'form-control',
													'value' => set_value('email', NULL, FALSE),
													'maxlength' => 150,
													'autocomplete' => 'off'
												);
												echo form_email($data);
												?></div>
												<?php
											}
											?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-user-friends text-contrast'></i></span>
											<h3 class="card-label">Emergency Contacts</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<?php
											if (show_field('emergency_contact_1_name', $account_holder_fields)) {
											?>
											<h4 class="lead small">Emergency Contact 1</h4>
											<div class='form-group'><?php
												echo field_label('emergency_contact_1_name', $account_holder_fields);
												$data = array(
													'name' => 'emergency_contact_1_name',
													'id' => 'emergency_contact_1_name',
													'class' => 'form-control',
													'value' => set_value('emergency_contact_1_name', NULL, FALSE),
													'maxlength' => 100
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('emergency_contact_1_phone', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('emergency_contact_1_phone', $account_holder_fields);
												$data = array(
													'name' => 'emergency_contact_1_phone',
													'id' => 'emergency_contact_1_phone',
													'class' => 'form-control',
													'value' => set_value('emergency_contact_1_phone', NULL, FALSE),
													'maxlength' => 20
												);
												echo form_tel($data);
												?></div>
												<?php
											}
											if (show_field('emergency_contact_2_name', $account_holder_fields)) {
											?>
											<h4 class="lead small">Emergency Contact 2</h4>
											<div class='form-group'><?php
												echo field_label('emergency_contact_2_name', $account_holder_fields);
												$data = array(
													'name' => 'emergency_contact_2_name',
													'id' => 'emergency_contact_2_name',
													'class' => 'form-control',
													'value' => set_value('emergency_contact_2_name', NULL, FALSE),
													'maxlength' => 100
												);
												echo form_input($data);
												?></div>
												<?php
											}
											if (show_field('emergency_contact_2_phone', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('emergency_contact_2_phone', $account_holder_fields);
												$data = array(
													'name' => 'emergency_contact_2_phone',
													'id' => 'emergency_contact_2_phone',
													'class' => 'form-control',
													'value' => set_value('emergency_contact_2_phone', NULL, FALSE),
													'maxlength' => 20
												);
												echo form_tel($data);
												?></div>
												<?php
											}
											?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
											<h3 class="card-label">Web Site Login</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<div class='form-group'><?php
												echo form_label('Password (<a href="#" class="dynamic-generatepassword">Generate?)</a>', 'password');
												$password = NULL;
												$data = array(
													'name' => 'password',
													'id' => 'password',
													'class' => 'form-control pwstrength',
													'value' => set_value('password', $this->crm_library->htmlspecialchars_decode($password), FALSE),
													'maxlength' => 100,
													'autocomplete' => 'off'
												);
												echo form_password($data);
												?></div>
											<div class='form-group'><?php
												echo form_label('Confirm Password', 'password_confirm');
												$password_confirm = NULL;
												$data = array(
													'name' => 'password_confirm',
													'id' => 'password_confirm',
													'class' => 'form-control pwstrength',
													'value' => set_value('password_confirm', $this->crm_library->htmlspecialchars_decode($password_confirm), FALSE),
													'maxlength' => 100,
													'autocomplete' => 'off'
												);
												echo form_password($data);
												?></div><?php
											if ($this->settings_library->get('send_new_participant') == 1) {
												?><div class='form-group'><?php
												$data = array(
													'name' => 'notify',
													'id' => 'notify',
													'value' => 1
												);
												if (set_value('notify') == 1) {
													$data['checked'] = TRUE;
												}
												?><div class="checkbox-single">
												<label class="checkbox">
													<?php echo form_checkbox($data); ?>
													Send login details by email
													<span></span>
												</label>
												</div>
												</div><?php
											}
											?>
										</div>
									</div>
									<?php echo form_fieldset_close(); ?>
									<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
									<div class="card-header">
										<div class="card-title">
											<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
											<h3 class="card-label">Advanced</h3>
										</div>
									</div>
									<div class="card-body">
										<div class='multi-columns'>
											<?php
											if (show_field('tags', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												echo field_label('tags', $account_holder_fields);
												$options = array();
												if (count($tag_list) > 0) {
													foreach ($tag_list as $tag) {
														$options[$tag] = $tag;
													}
												}
												echo form_dropdown('tags[]', $options, set_value('tags'), 'multiple="multiple" class="form-control select2-tags"');
												?>
												<p class="help-block">
													<small class="text-muted">Start typing to select a tag or create a new one.</small>
												</p>
											</div>
												<?php
											}
											if (show_field('blacklisted', $account_holder_fields)) {
											?>
											<div class='form-group'><?php
												$blacklisted = NULL;
												$data = array(
													'name' => 'blacklisted',
													'id' => 'blacklisted',
													'value' => 1
												);
												?><div class="checkbox-single">
													<label class="checkbox">
														<?php echo form_checkbox($data); ?>
														Block contact from making bookings
														<span></span>
													</label>
												</div>
											</div>
												<?php
											}
											?>
										</div>
									</div>
									<?php echo form_fieldset_close();
										if (time() >= strtotime('2018-05-25')) {
											?><?php
											$data = array(
												'brands' => $brands
											);
											$this->load->view('participants/privacy-inlay.php', $data);
										}?>
								</div>
							</div>
						</div>
						<?php echo form_close(); ?>
					</div>
					<div class="step-pane"></div>
					<div class="step-pane"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="participant-data" data-dummy="1" style="display: none">
	<?php echo form_open_multipart($submit_to, 'class="new_family family" data-role="p"'); ?>
	<div class="alert alert-danger" style="display: none;">
		<p><i class="far fa-exclamation-circle"></i> Please correct the following errors:</p>
		<ul></ul>
	</div>
	<div class='row'>
		<div class='col-sm-12'>
			<div class='box bordered-box'>
				<div class='box-content box-double-padding'>
					<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
					<div class="card-header">
						<div class="card-title">
							<span class="card-icon"><i class='far fa-child text-contrast'></i></span>
							<h3 class="card-label">Participant</h3>
						</div>
					</div>
					<div class="card-body">
						<div class='multi-columns'>
						<div id="childcheck_result" style="display:none;">
							<div class="alert alert-danger">
								<h4><i class="far fa-exclamation-circle"></i> Error</h4>
								<div class="result"></div>
							</div>
						</div>
							<?php
							if (show_field('first_name', $participant_fields)) { ?>
								<div class='form-group'><?php
									echo field_label('first_name', $participant_fields);
									$data = array(
										'name' => 'first_name',
										'class' => 'form-control',
										'value' => set_value('first_name', NULL, FALSE),
										'maxlength' => 100
									);
									echo form_input($data);
									?></div>
								<?php
							}
							if (show_field('last_name', $participant_fields)) {
							?>
								<div class='form-group'><?php
									echo field_label('last_name', $participant_fields);
									$data = array(
										'name' => 'last_name',
										'class' => 'form-control',
										'value' => set_value('last_name', NULL, FALSE),
										'maxlength' => 100
									);
									echo form_input($data);
									?></div>
								<?php
							}
							if (show_field('gender', $participant_fields)) {
							?>
								<div class='form-group'><?php
									echo field_label('gender', $participant_fields);
									$options = array(
										'' => 'Select',
										'male' => 'Male',
										'female' => 'Female',
										'please_specify' => 'Other (please specify)',
										'other' => 'Prefer not to say'
									);
									?>
									<div class="d-flex">
										<?php
										echo form_dropdown('gender', $options, set_value('gender', NULL, FALSE), 'class="form-control select2"');
										$data = array(
											'name' => 'gender_specify',
											'id' => 'gender_specify',
											'placeholder' => 'Please specify',
											'class' => 'ml-2 form-control'.(set_value('gender', NULL, FALSE)!="please_specify" ? " d-none" : ""),
											'value' => set_value('gender_specify', FALSE),
											'maxlength' => 100
										);
										echo form_input($data);
										?>
									</div>
								</div>
								<?php
							}
							if (show_field('dob', $participant_fields)) {
							?>
								<div class='form-group'><?php
									echo field_label('dob', $participant_fields);
									$data = array(
										'name' => 'dob',
										'class' => 'form-control datepicker datepicker-dob',
										'value' => set_value('dob', NULL, FALSE),
										'maxlength' => 10
									);
									echo form_input($data);
									?></div>
							<?php
							}
							if (show_field('orgID', $participant_fields)) {
							?>
							<?php
							if ($add_school != 1) {
								?><div class='form-group'><?php
								echo field_label('orgID', $participant_fields);
								$options = array(
									'' => 'Select'
								);
								if ($schools->num_rows() > 0) {
									foreach ($schools->result() as $row) {
										$options[$row->orgID] = $row->name;
									}
								}
								echo form_dropdown('orgID', $options, set_value('orgID', NULL, FALSE), 'class="form-control select2"');
								?><p class="help-block">
									<small class="text-muted"><a href="#" class="add_school">Add School</a></small>
								</p>
								</div><?php
							}
							echo form_hidden(array('add_school' => $add_school));
							?>
							<div class="add_school_fields"<?php if ($add_school != 1) { echo ' style="display:none;"'; } ?>>
								<div class='form-group'><?php
									echo form_label('School <em>*</em>', 'new_school');
									$data = array(
										'name' => 'new_school',
										'class' => 'form-control',
										'value' => set_value('new_school', NULL, FALSE),
										'maxlength' => 100
									);
									echo form_input($data);
									?></div>
							</div>
							<?php
						}
						if (show_field('pin', $participant_fields)) {
							?>
							<div class='form-group'><?php
								echo field_label('pin', $participant_fields);
								$data = array(
									'name' => 'pin',
									'id' => 'pin',
									'class' => 'form-control',
									'pattern' => '\d{4}',
									'maxlength' => "4",
									'value' => set_value('pin', NULL, FALSE)
								);
								echo form_input($data);
								?>
								<small class="text-muted">If entered, this four digit PIN will be asked to the person who picks up the participant at the end of their session. This is used to protect children or vunerable participants so that their coach/instructor knows who is authorised to pick them up</small>
							</div>
							<?php
						}
						if (show_field('medical', $participant_fields)) {
						?>
						<div class='form-group'><?php
							echo field_label('medical', $participant_fields);
							$data = array(
								'name' => 'medical',
								'class' => 'form-control',
								'value' => set_value('medical', NULL, FALSE)
							);
							echo form_textarea($data);
							?></div>
						<?php ?>
							<?php
						}
						if (show_field('behavioural_information', $participant_fields)) {
						?>
						<div class='form-group'><?php
							echo field_label('behavioural_information', $participant_fields);
							$data = array(
								'name' => 'behavioural_information',
								'id' => 'behavioural_information',
								'class' => 'form-control',
								'value' => set_value('behavioural_information', NULL, FALSE)
							);
							echo form_textarea($data);
							?></div>
							<?php
						}
						if (show_field('disability', $participant_fields)) {
						?>
							<div class='form-group'><?php
								echo field_label('disability', $participant_fields);
								foreach ($this->settings_library->disabilities as $p => $checkbox) {
									$data = array(
										'name' => 'disability['.$p.']',
										'id' => 'disability_'.$p,
										'value' => '1',
										'checked' => (bool)set_value('disability['.$p.']', false)
									);
									?>
									<div class="checkbox-single">
										<label class="checkbox">
											<?php
											echo form_checkbox($data);
											echo $checkbox;
											?>
											<span></span>
										</label>
									</div>
									<?php
								}
								?></div>
							<?php
						}
						if (show_field('disability_info', $participant_fields)) {
						?>
						<div class='form-group'><?php
							echo field_label('disability_info', $participant_fields);
							$data = array(
								'name' => 'disability_info',
								'class' => 'form-control',
								'value' => set_value('disability_info', NULL, FALSE)
							);
							echo form_textarea($data);
							?></div>
							<?php
						}
						if (show_field('photoConsent', $participant_fields)) {
						?>
						<div class='form-group'><?php
							echo field_label('photoConsent', $participant_fields);
							$data = array(
								'name' => 'photoConsent',
								'value' => 1
							);
							if (set_value('photoConsent', NULL, FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="col-md-12">
								<div class="checkbox-single">
									<label class="checkbox">
										<?php echo form_checkbox($data); ?>
										Yes
										<span></span>
									</label>
								</div>
							</div>
						</div>
							<?php
						}
						if (show_field('tags', $participant_fields)) {
						?>
						<div class='form-group'><?php
							echo field_label('tags', $participant_fields);
							$options = array();
							if (count($tag_list) > 0) {
								foreach ($tag_list as $tag) {
									$options[$tag] = $tag;
								}
							}
							echo form_dropdown('tags[]', $options, set_value('tags'), ' multiple="multiple" class="form-control select2-tags"');
							?>
							<p class="help-block">
								<small class="text-muted">Start typing to select a tag or create a new one.</small>
							</p>
						</div>
					<?php
					}
					if (show_field('profile_pic', $participant_fields)) {
					?>
						<div class="form-group"><?php
							echo field_label('profile_pic', $participant_fields);
							$data = array(
								'name' => 'profile_pic',
								'class' => 'custom-file-input'
							);?>
							<div class="custom-file">
								<?php echo form_upload($data); ?>
								<label class="custom-file-label" for="logo">Choose file</label>
							</div>
							<small class="text-muted">Recommended size is 200px by 200px. Thumbnails will be cropped into a square.</small>
						</div>
					<?php
					}
					?>
					</div>
					</div>
					<?php echo form_fieldset_close(); ?>

					<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
					<div class="card-header">
						<div class="card-title">
							<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
							<h3 class="card-label">Diversity</h3>
						</div>
						<div class="card-toolbar">
							<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
								<i class="ki ki-arrow-down icon-nm"></i>
							</a>
						</div>
					</div>
					<div class="card-body">
						<div class="multi-columns">
							<?php if (show_field('ethnic_origin', $participant_fields)) { ?>
							<div class='form-group'><?php
								echo field_label('ethnic_origin', $participant_fields);
								$options = $this->settings_library->ethnic_origins;
								echo form_dropdown('ethnic_origin', $options, set_value('ethnic_origin', NULL, FALSE), 'class="form-control select2"');
								?>
							</div>
							<?php } ?>
							<?php if (show_field('religion', $participant_fields)) { ?>
							<div class='form-group'><?php
								echo field_label('religion', $participant_fields);
								$options = $this->settings_library->religions;
								?>
								<div class="d-flex"><?php
									echo form_dropdown('religion', $options, set_value('religion', 'prefer_not_to_say', FALSE), 'class="form-control select2"');
									$data = array(
										'name' => 'religion_specify',
										'id' => 'religion_specify',
										'placeholder' => 'Please specify',
										'class' => 'ml-2 form-control'.(set_value('religion', 'prefer_not_to_say', FALSE)!="please_specify" ? " d-none" : ""),
										'value' => set_value('religion_specify', NULL, FALSE),
										'maxlength' => 100
									);
									echo form_input($data);
									?>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php echo form_fieldset_close(); ?>

					<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
					<div class="card-header">
						<div class="card-title">
							<span class="card-icon"><i class='far fa-user-friends text-contrast'></i></span>
							<h3 class="card-label">Participant Emergency Contacts</h3>
						</div>
					</div>
					<div class="card-body">
						<div class='multi-columns'>
							<?php
							if (show_field('emergency_contact_1_name', $participant_fields)) {
							?>
						<h4 class="lead small">Emergency Contact 1</h4>
						<div class='form-group'><?php
							echo field_label('emergency_contact_1_name', $participant_fields);
							$data = array(
								'name' => 'emergency_contact_1_name',
								'class' => 'form-control',
								'value' => set_value('emergency_contact_1_name', NULL, FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
							?></div>
								<?php
							}
							if (show_field('emergency_contact_1_phone', $participant_fields)) {
							?>
						<div class='form-group'><?php
							echo field_label('emergency_contact_1_phone', $participant_fields);
							$data = array(
								'name' => 'emergency_contact_1_phone',
								'class' => 'form-control',
								'value' => set_value('emergency_contact_1_phone', NULL, FALSE),
								'maxlength' => 20
							);
							echo form_tel($data);
							?></div>
								<?php
							}
							if (show_field('emergency_contact_2_name', $participant_fields)) {
							?>
						<h4 class="lead small">Emergency Contact 2</h4>
						<div class='form-group'><?php
							echo field_label('emergency_contact_2_name', $participant_fields);
							$data = array(
								'name' => 'emergency_contact_2_name',
								'class' => 'form-control',
								'value' => set_value('emergency_contact_2_name', NULL, FALSE),
								'maxlength' => 100
							);
							echo form_input($data);
							?></div>
								<?php
							}
							if (show_field('emergency_contact_2_phone', $participant_fields)) {
							?>
						<div class='form-group'><?php
							echo field_label('emergency_contact_2_phone', $participant_fields);
							$data = array(
								'name' => 'emergency_contact_2_phone',
								'class' => 'form-control',
								'value' => set_value('emergency_contact_2_phone', NULL, FALSE),
								'maxlength' => 20
							);
							echo form_tel($data);
							?></div>
								<?php
							}
							?>
					</div>
					</div>
					<?php echo form_fieldset_close(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php echo form_close(); ?>
</div>
