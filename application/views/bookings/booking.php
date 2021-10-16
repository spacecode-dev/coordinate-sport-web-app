<?php
display_messages();
if($this->crm_library->last_segment() == "booking-site" && $booking_info->project != 1){
	show_404();
}
if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}

echo form_open_multipart($submit_to, 'class="booking"');
echo form_fieldset('', ['class' => 'card card-custom'.($this->crm_library->last_segment() == "booking-site" ? " d-none" : "") ]);
?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
			<h3 class="card-label">Overview</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='row'>
			<?php
			if ($type == 'event' || $is_project === TRUE) {
				?><div class='col-md-6 form-group'><?php
				echo form_label('Project Name <em>*</em>', 'name');
				$name = NULL;
				if (isset($booking_info->name) && !empty($booking_info->name)) {
					$name = $booking_info->name;
				}
				$data = array(
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control',
					'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
					'maxlength' => 100
				);
				echo form_input($data);
				?>
				<small class="text-muted">
				If applicable, this name will also appear on the bookings site.
				</small>
				</div><?php
			}
			if ($is_project === TRUE) {
				?><div class='col-md-6  form-group'><?php
				echo form_label('Project Type <em>*</em>', 'project_typeID');
				$project_typeID = NULL;
				if (isset($booking_info->project_typeID)) {
					$project_typeID = $booking_info->project_typeID;
				}
				$options = array(
					'' => 'Select'
				);
				if ($project_types->num_rows() > 0) {
					foreach ($project_types->result() as $row) {
						$options[$row->typeID] = $row->name;
					}
				}
				echo form_dropdown('project_typeID', $options, set_value('project_typeID', $this->crm_library->htmlspecialchars_decode($project_typeID), FALSE), 'id="project_typeID" class="form-control select2"');
				?></div><?php

				if ($this->auth->has_features('projectcode') && $project_codes->num_rows() > 0) {
					?><div class='col-md-6 form-group'><?php
					echo form_label('Project Code', 'project_codeID');
					$project_codeID = NULL;
					if (isset($booking_info->project_codeID)) {
						$project_codeID = $booking_info->project_codeID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($project_codes->num_rows() > 0) {
						foreach ($project_codes->result() as $row) {
							$options[$row->codeID] = $row->code;
						}
					}
					echo form_dropdown('project_codeID', $options, set_value('project_codeID', $this->crm_library->htmlspecialchars_decode($project_codeID), FALSE), 'id="project_codeID" class="form-control select2"');
					?></div><?php
				}
			}
			?>
			<div class='col-md-6 form-group'><?php
				if ($is_project === TRUE) {
					switch ($type) {
						case 'booking':
							$label = 'Customer or Venue';
							break;
						case 'event':
							$label = 'Venue';
							break;
					}
				}else{
					$label = 'Customer';
				}
				echo form_label($label . ' <em>*</em>', 'orgID');
				// directors/managers can change venue
				if ($bookingID != NULL && !in_array($this->auth->user->department, array('directors', 'management'))) {
					if ($organisations->num_rows() > 0) {
						foreach ($organisations->result() as $row) {
							$data = array(
								'name' => 'orgID',
								'id' => 'orgID',
								'class' => 'form-control',
								'value' => $this->crm_library->htmlspecialchars_decode($row->name),
								'readonly' => 'readonly',
								'disabled' => 'disabled'
							);
							echo form_input($data);
						}
					}
				} else {
					$orgID = NULL;
					if (isset($booking_info->orgID)) {
						$orgID = $booking_info->orgID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($organisations->num_rows() > 0) {
						foreach ($organisations->result() as $row) {
							$options[$row->orgID] = array(
								'name' => $row->name,
								'extras' => 'data-type="' . $row->type . '"'
							);
						}
					}
					echo form_dropdown_advanced('orgID', $options, set_value('orgID', $this->crm_library->htmlspecialchars_decode($orgID), FALSE), 'id="orgID" class="form-control select2"');
				}
				?></div>
			<?php
			if ($is_project !== TRUE) {
				if ($this->auth->has_features('projectcode') && $project_codes->num_rows() > 0) {
					?><div class='col-md-6 form-group'><?php
					echo form_label('Project Code', 'project_codeID');
					$project_codeID = NULL;
					if (isset($booking_info->project_codeID)) {
						$project_codeID = $booking_info->project_codeID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($project_codes->num_rows() > 0) {
						foreach ($project_codes->result() as $row) {
							$options[$row->codeID] = $row->code;
						}
					}
					echo form_dropdown('project_codeID', $options, set_value('project_codeID', $this->crm_library->htmlspecialchars_decode($project_codeID), FALSE), 'id="project_codeID" class="form-control select2"');
					?></div><?php
				}
			}
			?>
			<?php
			if ($bookingID != NULL && in_array($this->auth->user->department, array('directors', 'management'))) {
				?><div class="alert alert-info venue-change-warning" style="display:none;">
				<p><strong>Warning:</strong> After changing the <?php echo strtolower($label); ?>, be sure to reassign the addresses within blocks and sessions to the new <?php echo strtolower($label); ?>.</p>
				</div><?php
			}
			switch ($type) {
				case 'booking':
					if ($add_contact != 1) {
						?><div class='col-md-6 form-group'><?php
						echo form_label('Contact <em>*</em>', 'contactID');
						$contactID = NULL;
						if (isset($booking_info->contactID)) {
							$contactID = $booking_info->contactID;
						}
						$options = array(
							'' => 'Select'
						);
						if ($contacts->num_rows() > 0) {
							foreach ($contacts->result() as $row) {
								if ($bookingID == NULL || in_array($this->auth->user->department, array('directors', 'management'))) {
									$options[$row->contactID] = array(
										'name' => $row->name,
										'extras' => 'data-org="' . $row->orgID . '"'
									);
								} else {
									$options[$row->contactID] = $row->name;
								}
							}
						}
						echo form_dropdown_advanced('contactID', $options, set_value('contactID', $this->crm_library->htmlspecialchars_decode($contactID), FALSE), 'id="contactID" class="form-control select2"');
						?><small class="text-muted form-text"><a href="#" class="add_contact">Add Contact</a></small>
						</div><?php
					}
					echo form_hidden(array('add_contact' => $add_contact));
					?>
					<div class="col-md-6 form-group add_contact_fields"<?php if ($add_contact != 1) { echo ' style="display:none;"'; } ?>>
						<div class="row">
							<div class='col-md-6 form-group'><?php
								echo form_label('Name <em>*</em>', 'contact_name');
								$data = array(
									'name' => 'contact_name',
									'id' => 'contact_name',
									'class' => 'form-control',
									'value' => set_value('contact_name', NULL, FALSE),
									'maxlength' => 100
								);
								echo form_input($data);
								?></div>
							<div class='col-md-6 form-group'><?php
								echo form_label('Position <em>*</em>', 'contact_position');
								$data = array(
									'name' => 'contact_position',
									'id' => 'contact_position',
									'class' => 'form-control',
									'value' => set_value('contact_position', NULL, FALSE),
									'maxlength' => 50
								);
								echo form_input($data);
								?></div>
							<div class='col-md-6 form-group'><?php
								echo form_label('Phone', 'contact_tel');
								$data = array(
									'name' => 'contact_tel',
									'id' => 'contact_tel',
									'class' => 'form-control',
									'value' => set_value('contact_tel', NULL, FALSE),
									'maxlength' => 20
								);
								echo form_input($data);
								?></div>
							<div class='col-md-6 form-group'><?php
								echo form_label('Mobile', 'contact_mobile');
								$data = array(
									'name' => 'contact_mobile',
									'id' => 'contact_mobile',
									'class' => 'form-control',
									'value' => set_value('contact_mobile', NULL, FALSE),
									'maxlength' => 20
								);
								echo form_input($data);
								?></div>
							<div class='col-md-6 form-group'><?php
								echo form_label('Email <em>*</em>', 'contact_email');
								$data = array(
									'name' => 'contact_email',
									'id' => 'contact_email',
									'class' => 'form-control',
									'value' => set_value('contact_email', NULL, FALSE),
									'maxlength' => 150
								);
								echo form_email($data);
							?></div>
						</div>
					</div><?php
					break;
				case 'event':
					?><div class='col-md-6 form-group'><?php
					echo form_label('Address <em>*</em>', 'addressID');
					$addressID = NULL;
					if (isset($booking_info->addressID)) {
						$addressID = $booking_info->addressID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($addresses->num_rows() > 0) {
						foreach ($addresses->result() as $row) {
							$address_parts = array();
							$address = NULL;
							if (!empty($row->address1)) {
								$address_parts[] = $row->address1;
							}
							if (!empty($row->address2)) {
								$address_parts[] = $row->address2;
							}
							if (!empty($row->address3)) {
								$address_parts[] = $row->address3;
							}
							if (!empty($row->town)) {
								$address_parts[] = $row->town;
							}
							if (!empty($row->county)) {
								$address_parts[] = $row->county;
							}
							if (!empty($row->postcode)) {
								$address_parts[] = $row->postcode;
							}
							if (count($address_parts) > 0) {
								$address = implode(', ', $address_parts);

								if (!empty($row->type)) {
									$address .= ' (' . ucwords($row->type) . ')';
								}

								if ($bookingID == NULL || in_array($this->auth->user->department, array('directors', 'management'))) {
									$options[$row->addressID] = array(
										'name' => $address,
										'extras' => 'data-org="' . $row->orgID . '"'
									);
								} else {
									$options[$row->addressID] = $address;
								}
							}
						}
					}
					echo form_dropdown_advanced('addressID', $options, set_value('addressID', $this->crm_library->htmlspecialchars_decode($addressID), FALSE), 'id="addressID" class="form-control select2"');
					?></div><?php
					break;
			}
			?>
			<div class="col-md-6 form-group">
				<div class="row">
					<div class='col-md-6'><?php
						$label = 'Contract Start';
						if($is_project == TRUE){
							$label = 'Start Date';
						}
						echo form_label($label.' <em>*</em>', 'startDate');
						$startDate = NULL;
						if (isset($booking_info->startDate)) {
							$startDate = mysql_to_uk_date($booking_info->startDate);
						}
						$data = array(
							'name' => 'startDate',
							'id' => 'startDate',
							'class' => 'form-control datepicker',
							'value' => set_value('startDate', $this->crm_library->htmlspecialchars_decode($startDate), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
						?>
					</div>
					<div class='col-md-6'><?php
						$label = 'Contract End';
						if($is_project == TRUE){
							$label = 'End Date';
						}
						echo form_label($label.' <em>*</em>', 'endDate');
						$endDate = NULL;
						if (isset($booking_info->endDate)) {
							$endDate = mysql_to_uk_date($booking_info->endDate);
						}
						$data = array(
							'name' => 'endDate',
							'id' => 'endDate',
							'class' => 'form-control datepicker',
							'value' => set_value('endDate', $this->crm_library->htmlspecialchars_decode($endDate), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
						?>
					</div>
				</div>
				<?php
				if ($is_project === TRUE) {
					echo '
					<small class="text-muted">
					If you have a contract with a customer, this will relate to the Start and End dates specified there.
					</small>';
				}
				?>
			</div>
			<?php if ($this->auth->has_features('online_booking_subscription_module') && ($type == 'event' || $is_project === TRUE)) {?>
			<div class='col-md-6 form-group'><?php
				if(!empty($gc_error) && !empty($stripe_error)) {?>
					<p class="help-block">
						<small class="text-muted">
							<?php echo $gc_error; ?>
						</small>
					</p>
					<p class="help-block">
						<small class="text-muted">
							<?php echo $stripe_error; ?>
						</small>
					</p>
				<?php } else {

				$data = array(
					'name' => 'subscriptions_only',
					'id' => 'subscriptions_only',
					'value' => 1
				);
				$subscriptions_only = NULL;
				if (isset($booking_info->subscriptions_only)) {
					$subscriptions_only = $booking_info->subscriptions_only;
				}
				if (set_value('subscriptions_only', $this->crm_library->htmlspecialchars_decode($subscriptions_only), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				?>
				<div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Subscriptions Only
						<span></span>
					</label>
				</div>
				<small class="text-muted">You can set these up in the subscriptions tab once the project has been created</small>
				<?php } ?>
			</div>
			<?php }?>
			<?php
			if ($type == 'booking') {
				?><div class='col-md-6 form-group'><?php
				echo form_label('Contract Renewal', 'contract_renewal');
				$data = array(
					'name' => 'contract_renewal',
					'id' => 'contract_renewal',
					'data-togglecheckbox' => 'renewalDate renewalMeetingDate contract_renewed',
					'value' => 1
				);
				$contract_renewal = NULL;
				if (isset($booking_info->contract_renewal)) {
					$contract_renewal = $booking_info->contract_renewal;
				}
				if (set_value('contract_renewal', $this->crm_library->htmlspecialchars_decode($contract_renewal), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox-single">
				<label class="checkbox">
					<?php echo form_checkbox($data); ?>
					Yes
					<span></span>
				</label>
				</div>
				<small class="text-muted">
					<?php if ($is_project === TRUE) { ?>
					Contract renewal should only be used when there is a contract linked to a specific customer or venue.
					<?php }else{ ?>
					If the contract renewal is set, then you will receive an email reminder towards the end of the contract.
					<?php } ?>
				</small>
				</div>
				<div class='col-md-6 form-group'><?php
					echo form_label('Contract Renewal Date', 'renewalDate');
					$renewalDate = NULL;
					if (isset($booking_info->renewalDate) && !empty($booking_info->renewalDate)) {
						$renewalDate = mysql_to_uk_date($booking_info->renewalDate);
					}
					$data = array(
						'name' => 'renewalDate',
						'id' => 'renewalDate',
						'class' => 'form-control datepicker',
						'value' => set_value('renewalDate', $this->crm_library->htmlspecialchars_decode($renewalDate), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
					?><small class="text-muted form-text">When a date is entered. The Contract Renewal Reminder email will be sent out</small>
				</div>
				<div class='col-md-6 form-group'><?php
					echo form_label('Contract Renewal Meeting Date', 'renewalMeetingDate');
					$renewalMeetingDate = NULL;
					if (isset($booking_info->renewalMeetingDate) && !empty($booking_info->renewalMeetingDate)) {
						$renewalMeetingDate = mysql_to_uk_date($booking_info->renewalMeetingDate);
					}
					$data = array(
						'name' => 'renewalMeetingDate',
						'id' => 'renewalMeetingDate',
						'class' => 'form-control datepicker',
						'value' => set_value('renewalMeetingDate', $this->crm_library->htmlspecialchars_decode($renewalMeetingDate), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
					?></div>
				<div class='col-md-6 form-group'><?php
				echo form_label('Contract Renewed Status', 'contract_renewed');
				$contract_renewed = NULL;
				if (isset($booking_info->contract_renewed)) {
					$contract_renewed = $booking_info->contract_renewed;
				}
				$options = array(
					'' => 'Select',
					'pending' => 'Pending',
					'renewed' => 'Renewed',
					'cancelled' => 'Cancelled'
				);
				echo form_dropdown('contract_renewed', $options, set_value('contract_renewed', $this->crm_library->htmlspecialchars_decode($contract_renewed), FALSE), 'id="contract_renewed" class="form-control select2"');
				?><small class="text-muted form-text">Once a status has been set, contract reminder emails will stop being sent out to the customer</small>
				</div><?php
			}
			?>
			<div class='col-md-6 form-group'><?php
				echo form_label($this->settings_library->get_label('brand') . ' <em>*</em>', 'brandID');
				$brandID = NULL;
				if (isset($booking_info->brandID)) {
					$brandID = $booking_info->brandID;
				}
				$options = array(
					'' => 'Select'
				);
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('brandID', $options, set_value('brandID', $this->crm_library->htmlspecialchars_decode($brandID), FALSE), 'id="brandID" class="form-control select2"');
				?></div>
			<?php
			if ($is_project === TRUE) {
				?><div class='col-md-6 form-group'><?php
				echo form_label('Register Type <em>*</em>', 'register_type');
				$register_type = NULL;
				if (isset($booking_info->register_type)) {
					$register_type = $booking_info->register_type;
				}
				$options = array(
					'' => 'Select',
					'children' => 'Children',
					'individuals' => 'Adults',
					'adults_children' => 'Adults & Children',
					'names' => 'Names Only',
					'numbers' => 'Numbers Only'
				);

				if ($this->auth->has_features('bikeability')) {
					$options['children_bikeability'] = 'Bikeability - Children';
					$options['individuals_bikeability'] = 'Bikeability - Adults';
					$options['bikeability'] = 'Bikeability - Names Only';
				}

				if ($this->auth->has_features('shapeup')) {
					$options['individuals_shapeup'] = 'Shape Up - Individuals';
				}

				echo form_dropdown('register_type', $options, set_value('register_type', $this->crm_library->htmlspecialchars_decode($register_type), FALSE), 'id="register_type" class="form-control select2"');
				?></div>
				<div class='col-md-6 form-group'><?php
					echo form_label('Tags', 'tags');
					$tags = array();
					if (isset($booking_info->tags) && is_array($booking_info->tags)) {
						$tags = $booking_info->tags;
					}
					$options = array();
					if (count($tag_list) > 0) {
						foreach ($tag_list as $tag) {
							$options[$tag] = $tag;
						}
					}
					echo form_dropdown('tags[]', $options, set_value('tags', $tags), 'id="tags" multiple="multiple" class="form-control select2-tags"');
					?>
					<small class="text-muted form-text">Start typing to select a tag or create a new one.</small>
				</div>
				<div class='col-md-6 form-group hide-for-numbers-and-names'><?php
					echo form_label('Restrict Bookings to Postcodes', 'booking_postcodes');
					$booking_postcodes = NULL;
					if (isset($booking_info->booking_postcodes) && !empty($booking_info->booking_postcodes)) {
						$booking_postcodes = $booking_info->booking_postcodes;
					}
					$data = array(
						'name' => 'booking_postcodes',
						'id' => 'booking_postcodes',
						'class' => 'form-control',
						'value' => set_value('booking_postcodes', $this->crm_library->htmlspecialchars_decode($booking_postcodes), FALSE),
						'maxlength' => 250
					);
					echo form_input($data);
					?>
					<p class="help-block">
						<small class="text-muted">Separate with commas. Can be partial postcodes, e.g. HU7</small>
					</p>
				</div>
				<div class="col-md-6 form-group">
					<div class="row">
						<div class='col-md-6 form-group hide-for-numbers-and-names'><?php
							echo form_label('Minimum Age', 'min_age');
							$min_age = NULL;
							if (isset($booking_info->min_age) && !empty($booking_info->min_age)) {
								$min_age = $booking_info->min_age;
							}
							$data = array(
								'name' => 'min_age',
								'id' => 'min_age',
								'class' => 'form-control',
								'value' => set_value('min_age', $this->crm_library->htmlspecialchars_decode($min_age), FALSE),
								'maxlength' => 3,
								'min' => 0,
								'step' => 1
							);
							echo form_number($data);
							?>
							<small class="text-muted form-text">If not set, <?php
								if (empty($this->settings_library->get('min_age'))) {
									echo 'no limits';
								} else {
									echo 'a default of ' . $this->settings_library->get('min_age');
								}
								?> will apply. Can be overridden per block and session.
							</small>
						</div>
						<div class='col-md-6 form-group hide-for-numbers-and-names'><?php
						echo form_label('Maximum Age', 'max_age');
						$max_age = NULL;
						if (isset($booking_info->max_age) && !empty($booking_info->max_age)) {
							$max_age = $booking_info->max_age;
						}
						$data = array(
							'name' => 'max_age',
							'id' => 'max_age',
							'class' => 'form-control',
							'value' => set_value('max_age', $this->crm_library->htmlspecialchars_decode($max_age), FALSE),
							'maxlength' => 3,
							'min' => 0,
							'step' => 1
						);
						echo form_number($data);
						?>
						<small class="text-muted form-text">If not set, <?php
							if (empty($this->settings_library->get('max_age'))) {
								echo 'no limits';
							} else {
								echo 'a default of ' . $this->settings_library->get('max_age');
							}
							?> will apply. Can be overridden per block and session.</small>
						</div>
					</div>
				</div>
				<?php
				if ($booking_type == 'booking') {
					?><div class='col-md-6 form-group hide-for-numbers-and-names'><?php
					echo form_label('Booking Requirement <em>*</em>', 'booking_requirement');
					$booking_requirement = NULL;
					if (isset($booking_info->booking_requirement)) {
						$booking_requirement = $booking_info->booking_requirement;
					}
					$options = array(
						'' => 'Select',
						'all' => 'All Weeks',
						'remaining' => 'All Weeks (Remaining Sessions Only)',
						'select' => 'Select Weeks'
					);
					echo form_dropdown('booking_requirement', $options, set_value('booking_requirement', $this->crm_library->htmlspecialchars_decode($booking_requirement), FALSE), 'id="booking_requirement" class="form-control select2"');
					?><small class="text-muted form-text">This option can be changed at any time. All Weeks (Remaining Sessions Only) allows booking up to and including the day of the session here, however on online booking this respects cut off times you have set up.</small>
					</div><?php
				}
			}
			?>
		</div>
	</div>
<?php echo form_fieldset_close(); ?>
<?php if ($type == 'booking' && count($lesson_types) > 0) {
	echo form_fieldset('', ['class' => 'card card-custom card-collapsed'.($this->crm_library->last_segment() == 'booking-site' ? " d-none" : "")]);	?>
	<div class='card-header row'>
		<div class="card-title col-md-10 col-lg-10 col-sm-9 toggle-large">
			<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
			<h3 class="card-label">Default Session Prices
			</h3>
			<small class="text-muted form-text">This pricing relates to the invoice function for customers only. This does not relate to participant customer pricing.</small>
		</div>
		<div class="card-toolbar toggle-small">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-5 " data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
	<div class='multi-columns'>
		<?php
		foreach ($lesson_types as $typeID => $label) {
			?><div class='form-group'><?php
			echo form_label($label, 'price_' . $typeID);
			if ($this->input->post()) {
				$prices_array = $this->input->post('prices');
			}
			if (!is_array($prices_array)) {
				$prices_array = array();
			}
			$price = NULL;
			if (array_key_exists($typeID, $prices_array)) {
				$price = $prices_array[$typeID];
			}
			$data = array(
				'name' => 'prices[' . $typeID . ']',
				'id' => 'price_' . $typeID,
				'class' => 'form-control',
				'data-prices-amount' => $typeID,
				'value' => $price,
				'maxlength' => 10
			);
			echo form_input($data);
			$data = array(
				'name' => 'prices_contract[]',
				'data-prices-contract' => $typeID,
				'value' => $typeID
			);
			if ($this->input->post()) {
				$prices_contract_array = $this->input->post('prices_contract');
			}
			if (!is_array($prices_contract_array)) {
				$prices_contract_array = array();
			}
			if (in_array($typeID, $prices_contract_array)) {
				$data['checked'] = TRUE;
			}
			?>
			<div class="checkbox-single">
				<label class="checkbox">
					<?php echo form_checkbox($data); ?>
					Contract Pricing
					<span></span>
				</label>
			</div>
			</div><?php
		}
		?>
	</div>
	</div><?php
	echo form_fieldset_close();
	if ($bookingID != NULL && count($org_attachments) > 0) {
		echo form_fieldset('', ['class' => 'card card-custom card-collapsed'.($this->crm_library->last_segment() == 'booking-site' ? " d-none" : "")]);	?>
		<div class='card-header row'>
			<div class="card-title col-md-10 col-lg-10 col-sm-9 toggle-large">
				<span class="card-icon"><i class='far fa-paperclip text-contrast'></i></span>
				<h3 class="card-label">Attachments</h3>
				<small>Send customer attachments with confirmation</small>
			</div>
			<div class="card-toolbar toggle-small">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				foreach ($org_attachments as $attachmentID => $attachment) {
					$data = array(
						'name' => 'org_attachments[]',
						'value' => $attachmentID
					);

					if ($this->input->post()) {
						$org_attachments_array = $this->input->post('org_attachments');
					}
					if (!is_array($org_attachments_array)) {
						$org_attachments_array = array();
					}
					if (in_array($attachmentID, $org_attachments_array)) {
						$data['checked'] = TRUE;
					}
					?>
					<div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							<?php echo $attachment; ?>
							<span></span>
						</label>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php echo form_fieldset_close();
	}
}
if ($type == 'event' || $is_project === TRUE) {
	?><div class="<?php echo $this->crm_library->last_segment() == 'booking-site' ? "d-none" : "hide-for-numbers-and-names"; ?>">
	<?php echo form_fieldset('', ['class' => 'card card-custom card-collapsed']);	?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-wallet text-contrast'></i></span>
			<h3 class="card-label">Discounts</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>

            <div class='form-group'><?php
				echo form_label('Automatic Discount <em>*</em>', 'autodiscount');
				$autodiscount = NULL;
				if (isset($booking_info->autodiscount)) {
					$autodiscount = $booking_info->autodiscount;
				}
				$options = array(
					'off' => 'Off',
					'percentage' => 'Percentage',
					'amount' => 'Amount',
					'fixed' => 'Fixed Amount'
				);
				echo form_dropdown('autodiscount', $options, set_value('autodiscount', $this->crm_library->htmlspecialchars_decode($autodiscount), FALSE), 'id="autodiscount" class="form-control select2"');
				?></div>
			<div class='form-group'><?php
				echo form_label('Automatic Discount Amount <em>*</em>', 'autodiscount_amount');
				$autodiscount_amount = NULL;
				if (isset($booking_info->autodiscount_amount) && !empty($booking_info->autodiscount_amount)) {
					$autodiscount_amount = $booking_info->autodiscount_amount;
				}
				$data = array(
					'name' => 'autodiscount_amount',
					'id' => 'autodiscount_amount',
					'class' => 'form-control',
					'value' => set_value('autodiscount_amount', $this->crm_library->htmlspecialchars_decode($autodiscount_amount), FALSE),
					'min' => 0,
					'step' => 0.01
				);
				?><div class="input-group">
					<div class="input-group-append amount"><span class="input-group-text"><?php echo currency_symbol(); ?></span></div>
					<?php echo form_number($data); ?>
					<div class="input-group-append percentage"><span class="input-group-text">%</span></div>
				</div>
                <small class="text-muted form-text add-text"></small>
			</div>
			<div class='form-group'><?php
				echo form_label('Sibling Discount <em>*</em>', 'siblingdiscount');
				$siblingdiscount = NULL;
				if (isset($booking_info->siblingdiscount)) {
					$siblingdiscount = $booking_info->siblingdiscount;
				}
				$options = array(
					'off' => 'Off',
					'percentage' => 'Percentage',
					'amount' => 'Amount',
					'fixed' => 'Fixed Amount'
				);
				echo form_dropdown('siblingdiscount', $options, set_value('siblingdiscount', $this->crm_library->htmlspecialchars_decode($siblingdiscount), FALSE), 'id="siblingdiscount" class="form-control select2"');
				?></div>
			<div class='form-group'><?php
				echo form_label('Sibling Discount Amount<em>*</em>', 'siblingdiscount_amount');
				$siblingdiscount_amount = NULL;
				if (isset($booking_info->siblingdiscount_amount) && !empty($booking_info->siblingdiscount_amount)) {
					$siblingdiscount_amount = $booking_info->siblingdiscount_amount;
				}
				$data = array(
					'name' => 'siblingdiscount_amount',
					'id' => 'siblingdiscount_amount',
					'class' => 'form-control',
					'value' => set_value('siblingdiscount_amount', $this->crm_library->htmlspecialchars_decode($siblingdiscount_amount), FALSE),
					'min' => 0,
					'step' => 0.01
				);
				?><div class="input-group">
					<div class="input-group-append amount"><span class="input-group-text"><?php echo currency_symbol(); ?></span></div>
					<?php echo form_number($data); ?>
					<div class="input-group-append percentage"><span class="input-group-text">%</span></div>
				</div>
				<small class="text-muted form-text">Sibling discount will apply when one or more participants from the same account are booked on to the same session, either when booked simultaneously or separately.</small>
			</div>
		</div>
	</div>
	<?php echo form_fieldset_close(); ?>
	</div>
	<div class="hide-for-numbers">
		<?php echo form_fieldset('', ['class' => 'card card-custom card-collapsed'.($this->crm_library->last_segment() == 'booking-site' ? " d-none" : "")]); ?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Monitoring Fields</h3>
			</div>
			<div class="card-toolbar">
				<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="Toggle Card">
					<i class="ki ki-arrow-down icon-nm"></i>
				</a>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				$maxRows = 10;
				for ($col=0; $col<=1; $col++) {
					?>
					<div class="form-group">
						<?php
						for ($i=($col*$maxRows)+1; $i <= ($col*$maxRows)+$maxRows; $i++) {
							$field = 'monitoring' . $i;
							?>
							<div class='form-group'>
								<?php
								echo form_label('Monitoring ' . $i, $field);
								$$field = NULL;
								${$field.'_entry_type'} = 0;
								${$field.'_mandatory'} = 0;
								if (isset($booking_info->$field) && !empty($booking_info->$field)) {
									$$field = $booking_info->$field;
								}
								if (isset($booking_info->{$field.'_entry_type'})) {
									${$field.'_entry_type'} = $booking_info->{$field.'_entry_type'};
								}
								if (isset($booking_info->{$field.'_mandatory'})) {
									${$field.'_mandatory'} = $booking_info->{$field.'_mandatory'};
								}
								$data = array(
									'name' => $field,
									'id' => $field,
									'class' => 'form-control',
									'value' => set_value($field, $this->crm_library->htmlspecialchars_decode($$field), FALSE),
									'maxlength' => 100
								);
								?>
								<div class="d-block d-md-flex align-items-center">
									<div class="w-100 mr-2">
										<?php echo form_input($data); ?>
									</div>
									<div class="w-100 mr-4">
										<?php echo form_dropdown_advanced($field.'_entry_type', array(1 => "Register", 2=> "Booking Site"), set_value($field.'_entry_type',$this->crm_library->htmlspecialchars_decode(${$field.'_entry_type'})),"required class=\"form-control\""); ?>
									</div>
									<div class="w-auto w-md-100">
										<?php
										$data = array(
											'name' => $field."_mandatory",
											'id' => $field."_mandatory",
											'checked' => (bool)(set_value($field.'_mandatory',$this->crm_library->htmlspecialchars_decode(${$field.'_mandatory'}))==1),
											'value' => 1
										);
										echo form_label("Mandatory", $field."_mandatory");
										?>
										<div class="checkbox-single">
											<label class="checkbox">
												<?php echo form_checkbox($data); ?>
												Yes
												<span></span>
											</label>
										</div>
									</div>
								</div>
							</div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
		</div>
		<?php echo form_fieldset_close(); ?>
	</div>
	<div class="<?php echo $this->crm_library->last_segment() == 'booking-site' ? "hide-for-numbers-and-names" : "d-none"; ?>">
		<?php
		if ($this->auth->has_features('online_booking')) {
			echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Details</h3>
				</div>
			</div>
			<div class="card-body">
				<div class="multi-columns">
					<div class='form-group'>
						<?php
						echo form_label('Show on Bookings Site', 'public');
						$data = array(
							'name' => 'public',
							'id' => 'public',
							'data-togglecheckbox' => 'disable_online_booking location limit_participants online_booking_password image_1 image_2 image_3 image_4',
							'value' => 1
						);
						if ($has_org_bookable_blocks !== TRUE) {
							$data['data-togglecheckbox'] .= ' website_description';
						}
						$public = NULL;
						if (isset($booking_info->public)) {
							$public = $booking_info->public;
						}
						if (set_value('public', $this->crm_library->htmlspecialchars_decode($public), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
						<small class="text-muted form-text">Once enabled, individual blocks can be turned off by editing each block.</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Web Site Description', 'website_description');
						$website_description = NULL;
						if (isset($booking_info->website_description) && !empty($booking_info->website_description)) {
							$website_description = $booking_info->website_description;
						}
						$data = array(
							'name' => 'website_description',
							'id' => 'website_description',
							'class' => 'form-control',
							'value' => set_value('website_description', $this->crm_library->htmlspecialchars_decode($website_description), FALSE),
						);
						echo form_textarea($data);
						?><small class="text-muted form-text">For customer login and online booking</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Online Booking Password', 'online_booking_password');
						$online_booking_password = NULL;
						if (isset($booking_info->online_booking_password) && !empty($booking_info->online_booking_password)) {
							$online_booking_password = $booking_info->online_booking_password;
						}
						$data = array(
							'name' => 'online_booking_password',
							'id' => 'online_booking_password',
							'class' => 'form-control',
							'value' => set_value('online_booking_password', $this->crm_library->htmlspecialchars_decode($online_booking_password), FALSE),
							'maxlength' => 20
						);
						echo form_input($data);
						?>
					</div>
					<div class='form-group'><?php
						echo form_label('Limit Online Booking to Target Participant Count', 'limit_participants');
						$data = array(
							'name' => 'limit_participants',
							'id' => 'limit_participants',
							'value' => 1
						);
						$limit_participants = NULL;
						if (isset($booking_info->limit_participants)) {
							$limit_participants = $booking_info->limit_participants;
						}
						if (set_value('limit_participants', $this->crm_library->htmlspecialchars_decode($limit_participants), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
					</div>
					<div class='form-group'><?php
						echo form_label('Disable Online Booking', 'disable_online_booking');
						$data = array(
							'name' => 'disable_online_booking',
							'id' => 'disable_online_booking',
							'value' => 1
						);
						$disable_online_booking = NULL;
						if (isset($booking_info->disable_online_booking)) {
							$disable_online_booking = $booking_info->disable_online_booking;
						}
						if (set_value('disable_online_booking', $this->crm_library->htmlspecialchars_decode($disable_online_booking), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
					</div>
					<div class='form-group'><?php
						echo form_label('Location', 'location');
						$location = NULL;
						if (isset($booking_info->location) && !empty($booking_info->location)) {
							$location =$booking_info->location;
						}
						$data = array(
							'name' => 'location',
							'id' => 'location',
							'class' => 'form-control',
							'value' => set_value('location', $this->crm_library->htmlspecialchars_decode($location), FALSE),
							'maxlength' => 100
						);
						echo form_input($data);
						?><small class="text-muted form-text">This will be displayed to participant customers on the online bookings site so they know where the event will be held</small>
					</div>
				</div>
				<div class="multi-columns form-group">
					<?php
					for ($i = 1; $i <= 4; $i++) {
						?><div class='form-group'><?php
						echo form_label('Current Marketing Image ' . ($i));

						if (isset($booking_info->images) && is_array($booking_info->images) && array_key_exists($i, $booking_info->images)) {
							$data = array(
								'name' => 'delete_images[' . $i . ']',
								'value' => $booking_info->images[$i]->imageID
							);
							?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Delete Marketing Image <?php echo ($i); ?>
								<span></span>
							</label>
							</div><?php
						}
						elseif (($i==3 && isset($booking_info->images[1])) || ($i==1 && isset($booking_info->images[3])) || ($i==2 && isset($booking_info->images[4])) || ($i==4 && isset($booking_info->images[2]))) {
							//Create placeholder div to keep upload inputs inline
							?>
							<div style="width: 100%; height: 29px;"></div>
							<?php
						}
						$data = array(
							'name' => 'image_' . $i,
							'id' => 'image_' . $i,
							'class' => 'custom-file-input'
						);
						?>
						<div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="image_<?php echo $i; ?>"><?php echo (isset($booking_info->images[$i]) ? $booking_info->images[$i]->name : "Choose file"); ?></label>
						</div>
						<small class="text-muted form-text">Shows on online booking. Minimum size of 512px x 512px. Thumbnails will be cropped into a square.</small>
						</div><?php
					}
					?>
				</div>
				<div class="multi-columns">
					<div class='form-group'><?php
						echo form_label('Send Thanks Email', 'thanksemail');
						$data = array(
							'name' => 'thanksemail',
							'id' => 'thanksemail',
							'data-togglecheckbox' => 'thanksemail_text',
							'value' => 1
						);
						$thanksemail = NULL;
						if (isset($booking_info->thanksemail)) {
							$thanksemail = $booking_info->thanksemail;
						}
						if (set_value('thanksemail', $this->crm_library->htmlspecialchars_decode($thanksemail), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
					</div>
					<div class='form-group'><?php
						echo form_label('Thanks Email <em>*</em>', 'thanksemail_text');
						$thanksemail_text = $this->settings_library->get('email_event_thanks');
						if (isset($booking_info->thanksemail_text) && !empty($booking_info->thanksemail_text)) {
							$thanksemail_text = $booking_info->thanksemail_text;
						}
						$data = array(
							'name' => 'thanksemail_text',
							'id' => 'thanksemail_text',
							'class' => 'form-control wysiwyg',
							'value' => set_value('thanksemail_text', $this->crm_library->htmlspecialchars_decode($thanksemail_text), FALSE),
						);
						echo form_textarea($data);
						?><small class="text-muted form-text">Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {website}</small>
					</div>
					<?php
					if ($bookingID != NULL) {
						?><div class='form-group'><?php
						echo form_label('Cancel Event', 'cancelled');
						$data = array(
							'name' => 'cancelled',
							'id' => 'cancelled',
							'value' => 1
						);
						$cancelled = NULL;
						if (isset($booking_info->cancelled)) {
							$cancelled = $booking_info->cancelled;
						}
						if (set_value('cancelled', $this->crm_library->htmlspecialchars_decode($cancelled), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
						</div>
						<small class="text-muted form-text">Note: Cancelling an event will remove staff from it</small>
						</div><?php
					}
					?>
					<div class='form-group'><?php
						echo form_label('Participant Booking Instructions', 'booking_instructions');
						$booking_instructions = NULL;
						if (isset($booking_info->booking_instructions) && !empty($booking_info->booking_instructions)) {
							$booking_instructions = $booking_info->booking_instructions;
						}
						$data = array(
							'name' => 'booking_instructions',
							'id' => 'booking_instructions',
							'class' => 'form-control wysiwyg',
							'value' => set_value('booking_instructions', $this->crm_library->htmlspecialchars_decode($booking_instructions), FALSE),
						);
						echo form_textarea($data);
						?><small class="text-muted form-text">These instructions will be attached to the event confirmation and also shown in the participants's account</small>
					</div>
				</div>
			</div>
			<?php
			echo form_fieldset_close();
		}?>
		</div>
	<?php }
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
