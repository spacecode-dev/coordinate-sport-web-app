<?php
display_messages();
if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to);
if (show_field('passport', $fields) || show_field('ni_card', $fields)
|| show_field('drivers_licence', $fields) || show_field('birth_certificate', $fields)
|| show_field('utility_bill', $fields) || show_field('other', $fields)) {
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Proof of ID</h3>
			</div>
            <div class="card-toolbar">
                <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                    <i class="ki ki-arrow-down icon-nm"></i>
                </a>
            </div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class="col-sm-6 pl-0" area="proofid">
					<?php
					if (show_field('passport', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('passport', $fields);
						$data = array(
							'name' => 'proofid_passport',
							'id' => 'proofid_passport',
							'value' => 1,
							'data-togglecheckbox' => 'proofid_passport_date proofid_passport_ref'
						);
						$proofid_passport = NULL;
						if (isset($staff_info->proofid_passport)) {
							$proofid_passport = $staff_info->proofid_passport;
						}
						if (set_value('proofid_passport', $this->crm_library->htmlspecialchars_decode($proofid_passport), FALSE) == 1) {
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
						echo form_label('Passport - Date', 'proofid_passport_date');
						$proofid_passport_date = NULL;
						if (isset($staff_info->proofid_passport_date) && !empty($staff_info->proofid_passport_date)) {
							$proofid_passport_date = date("d/m/Y", strtotime($staff_info->proofid_passport_date));
						}
						$data = array(
							'name' => 'proofid_passport_date',
							'id' => 'proofid_passport_date',
							'class' => 'form-control datepicker',
							'value' => set_value('proofid_passport_date', $this->crm_library->htmlspecialchars_decode($proofid_passport_date), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Passport - Reference', 'proofid_passport_ref');
						$proofid_passport_ref = NULL;
						if (isset($staff_info->proofid_passport_ref)) {
							$proofid_passport_ref = $staff_info->proofid_passport_ref;
						}
						$data = array(
							'name' => 'proofid_passport_ref',
							'id' => 'proofid_passport_ref',
							'class' => 'form-control',
							'value' => set_value('proofid_passport_ref', $this->crm_library->htmlspecialchars_decode($proofid_passport_ref), FALSE),
							'maxlength' => 30
						);
						echo form_input($data);
					?></div>
					<?php } if (show_field('ni_card', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('ni_card', $fields);
						$data = array(
							'name' => 'proofid_nicard',
							'id' => 'proofid_nicard',
							'data-togglecheckbox' => 'proofid_nicard_ref',
							'value' => 1
						);
						$proofid_nicard = NULL;
						if (isset($staff_info->proofid_nicard)) {
							$proofid_nicard = $staff_info->proofid_nicard;
						}
						if (set_value('proofid_nicard', $this->crm_library->htmlspecialchars_decode($proofid_nicard), FALSE) == 1) {
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
						echo form_label('NI Card - Reference', 'proofid_nicard_ref');
						$proofid_nicard_ref = NULL;
						if (isset($staff_info->proofid_nicard_ref)) {
							$proofid_nicard_ref = $staff_info->proofid_nicard_ref;
						}
						$data = array(
							'name' => 'proofid_nicard_ref',
							'id' => 'proofid_nicard_ref',
							'class' => 'form-control',
							'value' => set_value('proofid_nicard_ref', $this->crm_library->htmlspecialchars_decode($proofid_nicard_ref), FALSE),
							'maxlength' => 30
						);
						echo form_input($data);
					?></div>
					<?php } if (show_field('drivers_licence', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('drivers_licence', $fields);
						$data = array(
							'name' => 'proofid_driving',
							'id' => 'proofid_driving',
							'data-togglecheckbox' => 'proofid_driving_date proofid_driving_ref',
							'value' => 1
						);
						$proofid_driving = NULL;
						if (isset($staff_info->proofid_driving)) {
							$proofid_driving = $staff_info->proofid_driving;
						}
						if (set_value('proofid_driving', $this->crm_library->htmlspecialchars_decode($proofid_driving), FALSE) == 1) {
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
						echo form_label('Driver\'s Licence - Date', 'proofid_driving_date');
						$proofid_driving_date = NULL;
						if (isset($staff_info->proofid_driving_date) && !empty($staff_info->proofid_driving_date)) {
							$proofid_driving_date = date("d/m/Y", strtotime($staff_info->proofid_driving_date));
						}
						$data = array(
							'name' => 'proofid_driving_date',
							'id' => 'proofid_driving_date',
							'class' => 'form-control datepicker',
							'value' => set_value('proofid_driving_date', $this->crm_library->htmlspecialchars_decode($proofid_driving_date), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Driver\'s Licence - Reference', 'proofid_driving_ref');
						$proofid_driving_ref = NULL;
						if (isset($staff_info->proofid_driving_ref)) {
							$proofid_driving_ref = $staff_info->proofid_driving_ref;
						}
						$data = array(
							'name' => 'proofid_driving_ref',
							'id' => 'proofid_driving_ref',
							'class' => 'form-control',
							'value' => set_value('proofid_driving_ref', $this->crm_library->htmlspecialchars_decode($proofid_driving_ref), FALSE),
							'maxlength' => 30
						);
						echo form_input($data);
					?></div>
					<?php } ?>
				</div>
				<div class="col-sm-6 pl-0" area="proofid">
					<?php if (show_field('birth_certificate', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('birth_certificate', $fields);
						$data = array(
							'name' => 'proofid_birth',
							'id' => 'proofid_birth',
							'data-togglecheckbox' => 'proofid_birth_date proofid_birth_ref',
							'value' => 1
						);
						$proofid_birth = NULL;
						if (isset($staff_info->proofid_birth)) {
							$proofid_birth = $staff_info->proofid_birth;
						}
						if (set_value('proofid_birth', $this->crm_library->htmlspecialchars_decode($proofid_birth), FALSE) == 1) {
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
						echo form_label('Birth Certificate - Date', 'proofid_birth_date');
						$proofid_birth_date = NULL;
						if (isset($staff_info->proofid_birth_date) && !empty($staff_info->proofid_birth_date)) {
							$proofid_birth_date = date("d/m/Y", strtotime($staff_info->proofid_birth_date));
						}
						$data = array(
							'name' => 'proofid_birth_date',
							'id' => 'proofid_birth_date',
							'class' => 'form-control datepicker',
							'value' => set_value('proofid_birth_date', $this->crm_library->htmlspecialchars_decode($proofid_birth_date), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Birth Certificate - Reference', 'proofid_birth_ref');
						$proofid_birth_ref = NULL;
						if (isset($staff_info->proofid_birth_ref)) {
							$proofid_birth_ref = $staff_info->proofid_birth_ref;
						}
						$data = array(
							'name' => 'proofid_birth_ref',
							'id' => 'proofid_birth_ref',
							'class' => 'form-control',
							'value' => set_value('proofid_birth_ref', $this->crm_library->htmlspecialchars_decode($proofid_birth_ref), FALSE),
							'maxlength' => 30
						);
						echo form_input($data);
					?></div>
					<?php } if (show_field('utility_bill', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('utility_bill', $fields);
						$data = array(
							'name' => 'proofid_utility',
							'id' => 'proofid_utility',
							'value' => 1
						);
						$proofid_utility = NULL;
						if (isset($staff_info->proofid_utility)) {
							$proofid_utility = $staff_info->proofid_utility;
						}
						if (set_value('proofid_utility', $this->crm_library->htmlspecialchars_decode($proofid_utility), FALSE) == 1) {
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
					<?php } if (show_field('other', $fields)) {
					?>
					<div class='form-group'><?php
						echo field_label('other', $fields);
						$data = array(
							'name' => 'proofid_other',
							'id' => 'proofid_other',
							'data-togglecheckbox' => 'proofid_other_specify',
							'value' => 1
						);
						$proofid_other = NULL;
						if (isset($staff_info->proofid_other)) {
							$proofid_other = $staff_info->proofid_other;
						}
						if (set_value('proofid_other', $this->crm_library->htmlspecialchars_decode($proofid_other), FALSE) == 1) {
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
						echo form_label('Other - Please Specify', 'proofid_other_specify');
						$proofid_other_specify = NULL;
						if (isset($staff_info->proofid_other_specify)) {
							$proofid_other_specify = $staff_info->proofid_other_specify;
						}
						$data = array(
							'name' => 'proofid_other_specify',
							'id' => 'proofid_other_specify',
							'class' => 'form-control',
							'value' => set_value('proofid_other_specify', $this->crm_library->htmlspecialchars_decode($proofid_other_specify), FALSE),
							'maxlength' => 30
						);
						echo form_input($data);
					?></div>
				<?php }  ?>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
<?php } ?>
<?php
if($mileage_section == 1){
	if (show_field('mileage_default_start_location', $fields) || show_field('mileage_activate_fuel_cards', $fields) || show_field('mileage_default_mode_of_transport', $fields) || show_field('activate_mileage', $fields)) {
		echo form_fieldset('', ['class' => 'card card-custom']); ?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Mileage</h3>
			</div>
            <div class="card-toolbar">
                <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                    <i class="ki ki-arrow-down icon-nm"></i>
                </a>
            </div>
		</div>
		<div class="card-body">
			<div class="row">
				<?php if (show_field('activate_mileage', $fields)) {?>
					<div class="col-md-6">
						<div class='form-group'><?php
							echo field_label('activate_mileage', $fields);
							$data = array(
								'name' => 'activate_mileage',
								'id' => 'activate_mileage',
								'value' => 1
							);
							$activate_mileage = NULL;
							if (isset($staff_info->activate_mileage)) {
								$activate_mileage = $staff_info->activate_mileage;
							}
							if (set_value('activate_mileage', $this->crm_library->htmlspecialchars_decode($activate_mileage), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>Yes
									<span></span>
								</label>
							</div>
						</div>
					</div>
				<?php } ?>
				<?php if (show_field('mileage_default_start_location', $fields)) {?>
					<div class="col-md-6">
						<div class="form-group">
							<?php
							echo field_label('mileage_default_start_location', $fields);
							$options = array(
								'' => 'Select'
							);
							$mileage_default_start_location = NULL;
							if (isset($staff_info->default_start_location)) {
								$mileage_default_start_location = $staff_info->default_start_location;
							}
							$options["staff_main_address"] = "Staff Main Address";
							$options["work_address"] = "Work Address";
							echo form_dropdown('mileage_default_start_location', $options, $this->crm_library->htmlspecialchars_decode($mileage_default_start_location) , 'id="mileage_default_start_location" class="form-control select2"');
							?>
						</div>
					</div>
				<?php } ?>
				<?php if (show_field('mileage_default_mode_of_transport', $fields)) {?>
					<div class="col-md-6">
						<div class="form-group">
							<?php
							echo field_label('mileage_default_mode_of_transport', $fields);
							$options = array(
								'' => 'Select'
							);
							$mileage_default_mode_of_transport = NULL;
							if (isset($staff_info->mileage_default_mode_of_transport)) {
								$mileage_default_mode_of_transport = $staff_info->mileage_default_mode_of_transport;
							}
							foreach($mileage_data->result() as $mileages){
								$options[$mileages->mileageID] = $mileages->name;
							}
							echo form_dropdown('mileage_default_mode_of_transport', $options, $this->crm_library->htmlspecialchars_decode($mileage_default_mode_of_transport) , 'id="mileage_default_mode_of_transport" class="form-control select2"');
							?>
						</div>
					</div>
				<?php } ?>
				<?php if($mileage_activate_fuel_cards == 1 && show_field('mileage_activate_fuel_cards', $fields)){
					?>
					<div class="col-md-6">
						<div class='form-group'><?php
							echo field_label('mileage_activate_fuel_cards', $fields);
							$data = array(
								'name' => 'mileage_activate_fuel_cards',
								'id' => 'mileage_activate_fuel_cards',
								'value' => 1
							);
							$mileage_activate_fuel_cards = NULL;
							if (isset($staff_info->mileage_activate_fuel_cards)) {
								$mileage_activate_fuel_cards = $staff_info->mileage_activate_fuel_cards;
							}
							if (set_value('mileage_activate_fuel_cards', $this->crm_library->htmlspecialchars_decode($mileage_activate_fuel_cards), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									This staff member uses a Fuel Card
									<span></span>
								</label>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
		<?php echo form_fieldset_close(); ?>
	<?php } ?>
<?php } ?>
<?php
echo form_fieldset('', ['class' => 'card card-custom']);
if (show_field('mot', $fields) || show_field('insurance', $fields)) {
	?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Driving</h3>
		</div>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                <i class="ki ki-arrow-down icon-nm"></i>
            </a>
        </div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php if (show_field('mot', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('mot', $fields);
				$data = array(
					'name' => 'driving_mot',
					'id' => 'driving_mot',
					'value' => 1,
					'data-togglecheckbox' => 'driving_mot_expiry'
				);
				$driving_mot = NULL;
				if (isset($staff_info->driving_mot)) {
					$driving_mot = $staff_info->driving_mot;
				}
				if (set_value('driving_mot', $this->crm_library->htmlspecialchars_decode($driving_mot), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				?>
				<div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Yes
						<span></span>
					</label>
				</div>
			</div>
			<div class='form-group'><?php
				echo form_label('MOT - Date', 'driving_mot_expiry');
				$driving_mot_expiry = NULL;
				if (isset($staff_info->driving_mot_expiry) && !empty($staff_info->driving_mot_expiry)) {
					$driving_mot_expiry = date("d/m/Y", strtotime($staff_info->driving_mot_expiry));
				}
				$data = array(
					'name' => 'driving_mot_expiry',
					'id' => 'driving_mot_expiry',
					'class' => 'form-control datepicker',
					'value' => set_value('driving_mot_expiry', $this->crm_library->htmlspecialchars_decode($driving_mot_expiry), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php }
			if (show_field('insurance', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('insurance', $fields);
				$data = array(
					'name' => 'driving_insurance',
					'id' => 'driving_insurance',
					'value' => 1,
					'data-togglecheckbox' => 'driving_insurance_expiry'
				);
				$driving_insurance = NULL;
				if (isset($staff_info->driving_insurance)) {
					$driving_insurance = $staff_info->driving_insurance;
				}
				if (set_value('driving_insurance', $this->crm_library->htmlspecialchars_decode($driving_insurance), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
				?>
				<div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						Yes
						<span></span>
					</label>
				</div>
			</div>
			<div class='form-group'><?php
				echo form_label('Insurance - Date', 'driving_insurance_expiry');
				$driving_insurance_expiry = NULL;
				if (isset($staff_info->driving_insurance_expiry) && !empty($staff_info->driving_insurance_expiry)) {
					$driving_insurance_expiry = date("d/m/Y", strtotime($staff_info->driving_insurance_expiry));
				}
				$data = array(
					'name' => 'driving_insurance_expiry',
					'id' => 'driving_insurance_expiry',
					'class' => 'form-control datepicker',
					'value' => set_value('driving_insurance_expiry', $this->crm_library->htmlspecialchars_decode($driving_insurance_expiry), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php } ?>
			<div class='form-group'><?php
				echo form_label('Declaration', 'driving_declaration');
				$data = array(
					'name' => 'driving_declaration',
					'id' => 'driving_declaration',
					'value' => 1
				);
				$driving_declaration = NULL;
				if (isset($staff_info->driving_declaration)) {
					$driving_declaration = $staff_info->driving_declaration;
				}
				if (set_value('driving_declaration', $this->crm_library->htmlspecialchars_decode($driving_declaration), FALSE) == 1) {
					$data['checked'] = TRUE;
				}
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
	<?php echo form_fieldset_close(); ?>
<?php }
if (show_field('proof_of_address', $fields) || show_field('proof_of_national_insurance', $fields)
	|| show_field('proof_of_qualifications', $fields) || show_field('valid_working_permit', $fields)) {
	echo form_fieldset('', ['class' => 'card card-custom']);	?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Validation</h3>
		</div>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                <i class="ki ki-arrow-down icon-nm"></i>
            </a>
        </div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php if (show_field('proof_of_address', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('proof_of_address', $fields);
				$data = array(
					'name' => 'proof_address',
					'id' => 'proof_address',
					'value' => 1
				);
				$proof_address = NULL;
				if (isset($staff_info->proof_address)) {
					$proof_address = $staff_info->proof_address;
				}
				if (set_value('proof_address', $this->crm_library->htmlspecialchars_decode($proof_address), FALSE) == 1) {
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
			<?php } if (show_field('proof_of_national_insurance', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('proof_of_national_insurance', $fields);
				$data = array(
					'name' => 'proof_nationalinsurance',
					'id' => 'proof_nationalinsurance',
					'value' => 1
				);
				$proof_nationalinsurance = NULL;
				if (isset($staff_info->proof_nationalinsurance)) {
					$proof_nationalinsurance = $staff_info->proof_nationalinsurance;
				}
				if (set_value('proof_nationalinsurance', $this->crm_library->htmlspecialchars_decode($proof_nationalinsurance), FALSE) == 1) {
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
			<?php }
			if (show_field('proof_of_qualifications', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('proof_of_qualifications', $fields);
				$data = array(
					'name' => 'proof_quals',
					'id' => 'proof_quals',
					'value' => 1
				);
				$proof_quals = NULL;
				if (isset($staff_info->proof_quals)) {
					$proof_quals = $staff_info->proof_quals;
				}
				if (set_value('proof_quals', $this->crm_library->htmlspecialchars_decode($proof_quals), FALSE) == 1) {
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
			<?php } if (show_field('valid_working_permit', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('valid_working_permit', $fields);
				$data = array(
					'name' => 'proof_permit',
					'id' => 'proof_permit',
					'value' => 1
				);
				$proof_permit = NULL;
				if (isset($staff_info->proof_permit)) {
					$proof_permit = $staff_info->proof_permit;
				}
				if (set_value('proof_permit', $this->crm_library->htmlspecialchars_decode($proof_permit), FALSE) == 1) {
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
			<?php } ?>
		</div>
	<?php echo form_fieldset_close(); ?>
<?php }
if (show_field('id_card', $fields) || show_field('pay_dates', $fields)
|| show_field('timesheet', $fields) || show_field('policy_agreement', $fields)
|| show_field('travel_expenses', $fields) || show_field('equal_opportunities', $fields)
|| show_field('employment_contract', $fields) || show_field('p45', $fields)
|| show_field('dbs', $fields) || show_field('policies', $fields)
|| show_field('details_updated', $fields) || show_field('tshirt', $fields)) {
echo form_fieldset('', ['class' => 'card card-custom card-collapsed']);	?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Checklist</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php if (show_field('id_card', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('id_card', $fields);
				$data = array(
					'name' => 'checklist_idcard',
					'id' => 'checklist_idcard',
					'value' => 1
				);
				$checklist_idcard = NULL;
				if (isset($staff_info->checklist_idcard)) {
					$checklist_idcard = $staff_info->checklist_idcard;
				}
				if (set_value('checklist_idcard', $this->crm_library->htmlspecialchars_decode($checklist_idcard), FALSE) == 1) {
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
			<?php } if (show_field('pay_dates', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('pay_dates', $fields);
				$data = array(
					'name' => 'checklist_paydates',
					'id' => 'checklist_paydates',
					'value' => 1
				);
				$checklist_paydates = NULL;
				if (isset($staff_info->checklist_paydates)) {
					$checklist_paydates = $staff_info->checklist_paydates;
				}
				if (set_value('checklist_paydates', $this->crm_library->htmlspecialchars_decode($checklist_paydates), FALSE) == 1) {
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
			<?php } if (show_field('timesheet', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('timesheet', $fields);
				$data = array(
					'name' => 'checklist_timesheet',
					'id' => 'checklist_timesheet',
					'value' => 1
				);
				$checklist_timesheet = NULL;
				if (isset($staff_info->checklist_timesheet)) {
					$checklist_timesheet = $staff_info->checklist_timesheet;
				}
				if (set_value('checklist_timesheet', $this->crm_library->htmlspecialchars_decode($checklist_timesheet), FALSE) == 1) {
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
			<?php } if (show_field('policy_agreement', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('policy_agreement', $fields);
				$data = array(
					'name' => 'checklist_policy',
					'id' => 'checklist_policy',
					'value' => 1
				);
				$checklist_policy = NULL;
				if (isset($staff_info->checklist_policy)) {
					$checklist_policy = $staff_info->checklist_policy;
				}
				if (set_value('checklist_policy', $this->crm_library->htmlspecialchars_decode($checklist_policy), FALSE) == 1) {
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
			<?php } if (show_field('travel_expenses', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('travel_expenses', $fields);
				$data = array(
					'name' => 'checklist_travel',
					'id' => 'checklist_travel',
					'value' => 1
				);
				$checklist_travel = NULL;
				if (isset($staff_info->checklist_travel)) {
					$checklist_travel = $staff_info->checklist_travel;
				}
				if (set_value('checklist_travel', $this->crm_library->htmlspecialchars_decode($checklist_travel), FALSE) == 1) {
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
			<?php } if (show_field('equal_opportunities', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('equal_opportunities', $fields);
				$data = array(
					'name' => 'checklist_equal',
					'id' => 'checklist_equal',
					'value' => 1
				);
				$checklist_equal = NULL;
				if (isset($staff_info->checklist_equal)) {
					$checklist_equal = $staff_info->checklist_equal;
				}
				if (set_value('checklist_equal', $this->crm_library->htmlspecialchars_decode($checklist_equal), FALSE) == 1) {
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
			<?php }
			if (show_field('employment_contract', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('employment_contract', $fields);
				$data = array(
					'name' => 'checklist_contract',
					'id' => 'checklist_contract',
					'value' => 1
				);
				$checklist_contract = NULL;
				if (isset($staff_info->checklist_contract)) {
					$checklist_contract = $staff_info->checklist_contract;
				}
				if (set_value('checklist_contract', $this->crm_library->htmlspecialchars_decode($checklist_contract), FALSE) == 1) {
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
			<?php } if (show_field('p45', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('p45', $fields);
				$data = array(
					'name' => 'checklist_p45',
					'id' => 'checklist_p45',
					'value' => 1
				);
				$checklist_p45 = NULL;
				if (isset($staff_info->checklist_p45)) {
					$checklist_p45 = $staff_info->checklist_p45;
				}
				if (set_value('checklist_p45', $this->crm_library->htmlspecialchars_decode($checklist_p45), FALSE) == 1) {
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
			<?php } if (show_field('dbs', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('dbs', $fields);
				$data = array(
					'name' => 'checklist_crb',
					'id' => 'checklist_crb',
					'value' => 1
				);
				$checklist_crb = NULL;
				if (isset($staff_info->checklist_crb)) {
					$checklist_crb = $staff_info->checklist_crb;
				}
				if (set_value('checklist_crb', $this->crm_library->htmlspecialchars_decode($checklist_crb), FALSE) == 1) {
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
			<?php } if (show_field('policies', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('policies', $fields);
				$data = array(
					'name' => 'checklist_policies',
					'id' => 'checklist_policies',
					'value' => 1
				);
				$checklist_policies = NULL;
				if (isset($staff_info->checklist_policies)) {
					$checklist_policies = $staff_info->checklist_policies;
				}
				if (set_value('checklist_policies', $this->crm_library->htmlspecialchars_decode($checklist_policies), FALSE) == 1) {
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
			<?php } if (show_field('details_updated', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('details_updated', $fields);
				$data = array(
					'name' => 'checklist_details',
					'id' => 'checklist_details',
					'value' => 1
				);
				$checklist_details = NULL;
				if (isset($staff_info->checklist_details)) {
					$checklist_details = $staff_info->checklist_details;
				}
				if (set_value('checklist_details', $this->crm_library->htmlspecialchars_decode($checklist_details), FALSE) == 1) {
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
			<?php } if (show_field('tshirt', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('tshirt', $fields);
				$data = array(
					'name' => 'checklist_tshirt',
					'id' => 'checklist_tshirt',
					'value' => 1
				);
				$checklist_tshirt = NULL;
				if (isset($staff_info->checklist_tshirt)) {
					$checklist_tshirt = $staff_info->checklist_tshirt;
				}
				if (set_value('checklist_tshirt', $this->crm_library->htmlspecialchars_decode($checklist_tshirt), FALSE) == 1) {
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
			<?php } ?>
		</div>
	<?php echo form_fieldset_close(); ?>
<?php }
if (show_field('start_date', $fields) || show_field('end_date', $fields)
	|| show_field('probation_date', $fields) || show_field('probation_complete', $fields)
	|| show_field('salaried_hours', $fields) || show_field('target_utilisation', $fields)
	|| show_field('target_observation_score', $fields) || show_field('team_leader', $fields)) {
	echo form_fieldset('', ['class' => 'card card-custom']); ?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Dates &amp; Targets</h3>
		</div>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                <i class="ki ki-arrow-down icon-nm"></i>
            </a>
        </div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php if (show_field('start_date', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('start_date', $fields);
				$employment_start_date = NULL;
				if (isset($staff_info->employment_start_date) && !empty($staff_info->employment_start_date)) {
					$employment_start_date = date("d/m/Y", strtotime($staff_info->employment_start_date));
				}
				$data = array(
					'name' => 'employment_start_date',
					'id' => 'employment_start_date',
					'class' => 'form-control datepicker',
					'value' => set_value('employment_start_date', $this->crm_library->htmlspecialchars_decode($employment_start_date), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('end_date', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('end_date', $fields);
				$employment_end_date = NULL;
				if (isset($staff_info->employment_end_date) && !empty($staff_info->employment_end_date)) {
					$employment_end_date = date("d/m/Y", strtotime($staff_info->employment_end_date));
				}
				$data = array(
					'name' => 'employment_end_date',
					'id' => 'employment_end_date',
					'class' => 'form-control datepicker',
					'value' => set_value('employment_end_date', $this->crm_library->htmlspecialchars_decode($employment_end_date), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('probation_date', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('probation_date', $fields);
				$employment_probation_date = NULL;
				if (isset($staff_info->employment_probation_date) && !empty($staff_info->employment_probation_date)) {
					$employment_probation_date = date("d/m/Y", strtotime($staff_info->employment_probation_date));
				}
				$data = array(
					'name' => 'employment_probation_date',
					'id' => 'employment_probation_date',
					'class' => 'form-control datepicker',
					'value' => set_value('employment_probation_date', $this->crm_library->htmlspecialchars_decode($employment_probation_date), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('probation_complete', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('probation_complete', $fields);
				$data = array(
					'name' => 'employment_probation_complete',
					'id' => 'employment_probation_complete',
					'value' => 1
				);
				$employment_probation_complete = NULL;
				if (isset($staff_info->employment_probation_complete)) {
					$employment_probation_complete = $staff_info->employment_probation_complete;
				}
				if (set_value('employment_probation_complete', $this->crm_library->htmlspecialchars_decode($employment_probation_complete), FALSE) == 1) {
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
			<?php }
			if (show_field('salaried_hours', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('salaried_hours', $fields);
				$target_hours = NULL;
				if (isset($staff_info->target_hours) && $staff_info->target_hours > 0) {
					$target_hours = $staff_info->target_hours;
				}
				$data = array(
					'name' => 'target_hours',
					'id' => 'target_hours',
					'class' => 'form-control',
					'value' => set_value('target_hours', $this->crm_library->htmlspecialchars_decode($target_hours), FALSE),
					'min' => 0,
					'max' => 99999,
					'step' => .5
				);
				echo form_number($data);
			?></div>
			<?php } if (show_field('target_utilisation', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('target_utilisation', $fields);
				$target_utilisation = NULL;
				if (isset($staff_info->target_utilisation) && $staff_info->target_utilisation > 0) {
					$target_utilisation = $staff_info->target_utilisation;
				}
				$data = array(
					'name' => 'target_utilisation',
					'id' => 'target_utilisation',
					'class' => 'form-control',
					'value' => set_value('target_utilisation', $this->crm_library->htmlspecialchars_decode($target_utilisation), FALSE),
					'maxlength' => 3,
					'min' => 0,
					'max' => 100,
					'step' => 1
				);
				?><div class="input-group">
					<?php echo form_number($data); ?>
					<span class="input-group-prepend"><span class="input-group-text">%</span></span>
				</div>
			</div>
			<?php } if (show_field('target_observation_score', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('target_observation_score', $fields);
				$target_observation_score = NULL;
				if (isset($staff_info->target_observation_score) && $staff_info->target_observation_score > 0) {
					$target_observation_score = $staff_info->target_observation_score;
				}
				$data = array(
					'name' => 'target_observation_score',
					'id' => 'target_observation_score',
					'class' => 'form-control',
					'value' => set_value('target_observation_score', $this->crm_library->htmlspecialchars_decode($target_observation_score), FALSE),
					'maxlength' => 3,
					'min' => 0,
					'max' => 100,
					'step' => 1
				);
				?><div class="input-group">
					<?php echo form_number($data); ?>
					<span class="input-group-prepend"><span class="input-group-text">%</span></span>
				</div>
			</div>
			<?php } if (show_field('team_leader', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('team_leader', $fields);
				$options = array(
					'' => 'Select'
				);
				if ($team_leaders->num_rows() > 0) {
					foreach ($team_leaders->result() as $row) {
						$options[$row->staffID] = $row->first . ' ' . $row->surname;
					}
				}
				echo form_dropdown('approverID[]', $options, set_value('approverID', $selected_team_leaders), 'id="approverID" multiple="multiple" class="form-control select2-tags"');
			?></div>
			<?php } ?>
		</div>
	<?php echo form_fieldset_close(); ?>
<?php }
if (show_field('payscales', $fields)) {
	echo form_fieldset('', ['class' => 'card card-custom']); ?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
				<h3 class="card-label">Payscales</h3>
			</div>
            <div class="card-toolbar">
                <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                    <i class="ki ki-arrow-down icon-nm"></i>
                </a>
            </div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class="col-sm-6 pl-0" area="payscales">
				<?php
				foreach ($roles as $key => $role) {
				    ?>
					<div class='form-group'><?php
						echo form_label($this->settings_library->get_staffing_type_label($key), 'payments_scale_' . $key);
                        if ($key == 'assistant') {
                            $key = 'assist';
                        }
						$payments_scale = NULL;
						if (isset($staff_info->{'payments_scale_' . $key}) && $staff_info->{'payments_scale_' . $key} > 0) {
							$payments_scale = $staff_info->{'payments_scale_' . $key};
						}
						$data = array(
							'name' => 'payments_scale_' . $key,
							'id' => 'payments_scale_' . $key,
							'class' => 'form-control',
							'value' => set_value('payments_scale_' . $key, $this->crm_library->htmlspecialchars_decode($payments_scale), FALSE),
							'maxlength' => 10,
							'step' => 0.01
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<span class="input-group-prepend"><span class="input-group-text">Per hour</span></span>
						</div>
					</div>
					<?php
				}
				 if (show_field('salaried_staff', $fields)) {
				?>
				<div class='form-group'><?php
					echo field_label('salaried_staff', $fields);
					$data = array(
						'name' => 'payments_scale_salaried',
						'id' => 'payments_scale_salaried',
						'value' => 1,
						'data-togglecheckbox' => 'payments_scale_salary'
					);
					$payments_scale_salaried = NULL;
					if (isset($staff_info->payments_scale_salaried)) {
						$payments_scale_salaried = $staff_info->payments_scale_salaried;
					}
					if (set_value('payments_scale_salaried', $this->crm_library->htmlspecialchars_decode($payments_scale_salaried), FALSE) == 1) {
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
					echo form_label('Salary', 'payments_scale_salary');
					$payments_scale_salary = NULL;
					if (isset($staff_info->payments_scale_salary) && $staff_info->payments_scale_salary > 0) {
						$payments_scale_salary = $staff_info->payments_scale_salary;
					}
					$data = array(
						'name' => 'payments_scale_salary',
						'id' => 'payments_scale_salary',
						'class' => 'form-control',
						'value' => set_value('payments_scale_salary', $this->crm_library->htmlspecialchars_decode($payments_scale_salary), FALSE),
						'min' => 0,
						'step' => "any"
					);
					echo form_number($data);
				?></div>
				<?php } ?>
				</div>
				<div class="col-sm-6 pl-0" area="payscales">
				<?php
				if (show_field('system_pay_rates', $fields) && $this->auth->has_features('payroll')) {
				?>
					<div class='form-group'><?php
						echo field_label('system_pay_rates', $fields);
						$data = array(
							'name' => 'system_pay_rates',
							'id' => 'system_pay_rates',
							'value' => 1,
						);
						$system_pay_rates = NULL;
						if (isset($staff_info->system_pay_rates)) {
							$system_pay_rates = $staff_info->system_pay_rates;
						}
						if (set_value('driving_mot', $this->crm_library->htmlspecialchars_decode($system_pay_rates), FALSE) == 1) {
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
					<div class='system_pay_rates_text' <?php
					if (!$system_pay_rates) {
						echo 'style="display:none"';
					}?>>
						<p>
							System Pay Rates will be generated automatically by the qualification and length of service - please go to <a href="/settings/mandatoryquals">Settings >  Mandatory Qualifications</a> to check the payrate for each qualification level
						</p>
					</div>
				<?php }
				if (show_field('standard_hourly_rate', $fields) && $this->auth->has_features('payroll')) {
					?>
					<div class='form-group hourly_rate_checkbox_form'><?php
						echo field_label('standard_hourly_rate', $fields);
						$data = array(
							'id' => 'houry_rate',
							'name' => 'hourly_rate',
							'value' => 1,
							'data-togglecheckbox' => 'houry_rate_value'
						);
						$hourly_rate = 0;
						if (isset($staff_info->hourly_rate)) {
							$hourly_rate = $staff_info->hourly_rate;
						}
						if ($hourly_rate > 0) {
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
					<div class='form-group hourly_rate_value_form'><?php
						echo form_label('Standard Hourly Rate', 'houry_rate_value');
						$data = array(
							'name' => 'houry_rate_value',
							'id' => 'houry_rate_value',
							'class' => 'form-control',
							'value' => set_value('houry_rate', $this->crm_library->htmlspecialchars_decode($hourly_rate, FALSE)),
							'maxlength' => 10,
							'step' => 0.01
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<span class="input-group-prepend"><span class="input-group-text">Per hour</span></span>
						</div>
					</div>
				<?php } ?>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
<?php }
if (show_field('bank_name', $fields) || show_field('sort_code', $fields)
|| show_field('account_number', $fields) || show_field('payroll_number', $fields)) {
echo form_fieldset('', ['class' => 'card card-custom']); ?>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Payment Details</h3>
		</div>
        <div class="card-toolbar">
            <a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
                <i class="ki ki-arrow-down icon-nm"></i>
            </a>
        </div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<?php if (show_field('bank_name', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('bank_name', $fields);
				$payments_bankName = NULL;
				if (isset($staff_info->payments_bankName)) {
					$payments_bankName = $staff_info->payments_bankName;
				}
				$data = array(
					'name' => 'payments_bankName',
					'id' => 'payments_bankName',
					'class' => 'form-control',
					'value' => set_value('payments_bankName', $this->crm_library->htmlspecialchars_decode($payments_bankName), FALSE),
					'maxlength' => 30
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('sort_code', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('sort_code', $fields);
				$payments_sortCode = NULL;
				if (isset($staff_info->payments_sortCode)) {
					$payments_sortCode = $staff_info->payments_sortCode;
				}
				$data = array(
					'name' => 'payments_sortCode',
					'id' => 'payments_sortCode',
					'class' => 'form-control',
					'value' => set_value('payments_sortCode', $this->crm_library->htmlspecialchars_decode($payments_sortCode), FALSE),
					'maxlength' => 10
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('account_number', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('account_number', $fields);
				$payments_accountNumber = NULL;
				if (isset($staff_info->payments_accountNumber)) {
					$payments_accountNumber = $staff_info->payments_accountNumber;
				}
				$data = array(
					'name' => 'payments_accountNumber',
					'id' => 'payments_accountNumber',
					'class' => 'form-control',
					'value' => set_value('payments_accountNumber', $this->crm_library->htmlspecialchars_decode($payments_accountNumber), FALSE),
					'maxlength' => 20
				);
				echo form_input($data);
			?></div>
			<?php } if (show_field('payroll_number', $fields)) {
			?>
			<div class='form-group'><?php
				echo field_label('payroll_number', $fields);
				$payroll_number = NULL;
				if (isset($staff_info->payroll_number)) {
					$payroll_number = $staff_info->payroll_number;
				}
				$data = array(
					'name' => 'payroll_number',
					'id' => 'payroll_number',
					'class' => 'form-control',
					'value' => set_value('payroll_number', $this->crm_library->htmlspecialchars_decode($payroll_number), FALSE),
					'maxlength' => 50
				);
				echo form_input($data);
			?></div>
			<?php } ?>
		</div>
	</div>
<?php echo form_fieldset_close(); } ?>
<div class='form-actions d-flex justify-content-between'>
	<button class='btn btn-primary btn-submit' type="submit">
		<i class='far fa-save'></i> Save
	</button>
	<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
</div>
<?php
echo form_close();
