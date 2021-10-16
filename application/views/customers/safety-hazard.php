<?php
display_messages();

if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
}
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
					echo form_label('Hazard <em>*</em>', 'field_hazard');
					$hazard = NULL;
					if (isset($hazard_info->hazard)) {
						$hazard = $hazard_info->hazard;
					}
					$data = array(
						'name' => 'hazard',
						'id' => 'field_hazard',
						'class' => 'form-control',
						'value' => set_value('hazard', $this->crm_library->htmlspecialchars_decode($hazard), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Potential Effect <em>*</em>', 'field_potential_effect');
					$potential_effect = NULL;
					if (isset($hazard_info->potential_effect)) {
						$potential_effect = $hazard_info->potential_effect;
					}
					// convert pre-wysiwyg fields to html
					if ($potential_effect == strip_tags($potential_effect)) {
						$potential_effect = '<p>' . nl2br($potential_effect) . '</p>';
					}
					$data = array(
						'name' => 'potential_effect',
						'id' => 'field_potential_effect',
						'class' => 'form-control wysiwyg',
						'value' => set_value('potential_effect', $this->crm_library->htmlspecialchars_decode($potential_effect), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Likelihood (1-5) <em>*</em>', 'field_likelihood');
					$likelihood = NULL;
					if (isset($hazard_info->likelihood)) {
						$likelihood = $hazard_info->likelihood;
					}
					$data = array(
						'name' => 'likelihood',
						'id' => 'field_likelihood',
						'class' => 'form-control',
						'value' => set_value('likelihood', $this->crm_library->htmlspecialchars_decode($likelihood), FALSE),
						'maxlength' => 1
					);
					echo form_number($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Severity (1-5) <em>*</em>', 'field_severity');
					$severity = NULL;
					if (isset($hazard_info->severity)) {
						$severity = $hazard_info->severity;
					}
					$data = array(
						'name' => 'severity',
						'id' => 'field_severity',
						'class' => 'form-control',
						'value' => set_value('severity', $this->crm_library->htmlspecialchars_decode($severity), FALSE),
						'maxlength' => 1
					);
					echo form_number($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Risk (1-25) <em>*</em>', 'field_risk');
					$risk = NULL;
					if (isset($hazard_info->risk)) {
						$risk = $hazard_info->risk;
					}
					$data = array(
						'name' => 'risk',
						'id' => 'field_risk',
						'class' => 'form-control',
						'value' => set_value('risk', $this->crm_library->htmlspecialchars_decode($risk), FALSE),
						'maxlength' => 2
					);
					echo form_number($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Control Measures <em>*</em>', 'field_control_measures');
					$control_measures = NULL;
					if (isset($hazard_info->control_measures)) {
						$control_measures = $hazard_info->control_measures;
					}
					// convert pre-wysiwyg fields to html
					if ($control_measures == strip_tags($control_measures)) {
						$control_measures = '<p>' . nl2br($control_measures) . '</p>';
					}
					$data = array(
						'name' => 'control_measures',
						'id' => 'field_control_measures',
						'class' => 'form-control wysiwyg',
						'value' => set_value('control_measures', $this->crm_library->htmlspecialchars_decode($control_measures), FALSE)
					);
					echo form_textarea($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Residual Risk (1-25) <em>*</em>', 'field_residual_risk');
					$residual_risk = NULL;
					if (isset($hazard_info->residual_risk)) {
						$residual_risk = $hazard_info->residual_risk;
					}
					$data = array(
						'name' => 'residual_risk',
						'id' => 'field_residual_risk',
						'class' => 'form-control',
						'value' => set_value('residual_risk', $this->crm_library->htmlspecialchars_decode($residual_risk), FALSE),
						'maxlength' => 2
					);
					echo form_number($data);
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
