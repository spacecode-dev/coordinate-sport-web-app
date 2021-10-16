<?php
$phone_script = $this->settings_library->get('participant_privacy_phone_script');
if (!empty($phone_script)) {
	?><div class="alert alert-info">
		<h4>Please read the following script to the participant</h4>
		<p><?php echo nl2br($phone_script); ?></p>
	</div><?php
}
echo form_fieldset('', ['class' => 'card card-custom']);
	?><div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-envelope text-contrast'></i></span>
			<h3 class="card-label">Marketing Consent</h3>
		</div>
	</div>
	<div class="card-body">
		<div class='multi-columns'>
			<div class='form-group'><?php
				$marketing_consent = NULL;
				if (isset($contact_info->marketing_consent)) {
					$marketing_consent = $contact_info->marketing_consent;
				}
				$data = array(
					'name' => 'marketing_consent',
					'id' => 'marketing_consent',
					'value' => 1
				);
				if (set_value('marketing_consent', $this->crm_library->htmlspecialchars_decode($marketing_consent)) == 1) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox-single">
					<label class="checkbox">
						<?php echo form_checkbox($data); ?>
						<?php
						$question = $this->settings_library->get('participant_marketing_consent_question');
						$smart_tags = array(
							'{company}' => $this->auth->account->company
						);
						foreach($smart_tags as $key => $replace) {
							$question = str_replace($key, $replace, $question);
						}
						echo $question;
						?>
						<span></span>
					</label>
				</div>
			</div>
			<?php
			$newsletters = array();
			if (isset($contact_info->newsletters)) {
				$newsletters = explode(",", $contact_info->newsletters);
				if (!is_array($newsletters)) {
					$newsletters = array();
				}
			}
			if (is_array($this->input->post('newsletters'))) {
				$newsletters = $this->input->post('newsletters');
			}
			?><div class='form-group marketing_allowed' style="display: none;"><?php
				echo form_label('Select Subscriptions');
				foreach ($brands->result() as $brand) {
					$data = array(
						'name' => 'newsletters[]',
						'id' => 'newsletter_' . $brand->brandID,
						'value' => $brand->brandID
					);
					if (in_array($brand->brandID, $newsletters)) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							<?php echo $brand->name; ?>
							<span></span>
						</label>
					</div><?php
				}
			?></div>
			<div class="form-group marketing_allowed" style="display: none;">
				<?php
				$source = NULL;
				if (isset($contact_info->source)) {
					$source = $contact_info->source;
				}
				echo form_label('Where did you hear about us?', 'source');
				$options = array(
					'' => 'Select',
					'Twitter' => 'Twitter',
					'Facebook' => 'Facebook',
					'Website' => 'Website',
					'Email' => 'Email',
					'SMS' => 'SMS',
					'Flyer' => 'Flyer',
					'Newspaper' => 'Newspaper',
					'Poster' => 'Poster',
					'Referral' => 'Referral',
					'Existing Customer' => 'Existing Customer',
					'Other' => 'Other (Please specify)'
				);
				echo form_dropdown('source', $options, set_value('source', $this->crm_library->htmlspecialchars_decode($source), FALSE), 'id="source" class="form-control select2"');
				?>
			</div>
			<div class="form-group" style="display: none;">
				<?php
				$source_other = NULL;
				if (isset($contact_info->source_other)) {
					$source_other = $contact_info->source_other;
				}
				echo form_label('Other (Please specify) <em>*</em>', 'source_other');
				$data = array(
					'name' => 'source_other',
					'id' => 'source_other',
					'class' => 'form-control',
					'value' => set_value('source_other', $this->crm_library->htmlspecialchars_decode($source_other), FALSE)
				);
				echo form_input($data);
				?>
			</div>
		</div>
	</div><?php
echo form_fieldset_close();
echo form_fieldset('', ['class' => 'card card-custom']);
	?><div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-eye text-contrast'></i></span>
			<h3 class="card-label">Privacy Policy</h3>
		</div>
	</div>
	<div class="card-body">
		<div class="terms_box">
			<?php
			$policies = array();
			$account_policy = $this->settings_library->get('staff_privacy');
			if (!empty($account_policy)) {
				echo '<h3>' . $this->auth->account->company . '</h3>';
				echo '<p>' . nl2br($account_policy) . '</p>';
				$policies[] = $this->auth->account->company;
			}
			$company_policy = $this->settings_library->get('company_privacy', 'default');
			if (!empty($company_policy)) {
				echo '<h3>' . $this->settings_library->get('company', 'default') . '</h3>';
				echo '<p>' . nl2br($company_policy) . '</p>';
				$policies[] = $this->settings_library->get('company', 'default');
			}
			?>
		</div>
		<div class='form-group'><?php
			$privacy_agreed = NULL;
			if (isset($contact_info->privacy_agreed)) {
				$privacy_agreed = $contact_info->privacy_agreed;
			}
			$data = array(
				'name' => 'privacy_agreed',
				'id' => 'privacy_agreed',
				'value' => 1
			);
			if (set_value('privacy_agreed', $this->crm_library->htmlspecialchars_decode($privacy_agreed)) == 1) {
				$data['checked'] = TRUE;
			}
			?><div class="checkbox-single">
				<label class="checkbox">
					<?php echo form_checkbox($data); ?>
					Participant has read the <?php echo implode(" and ", $policies); ?> privacy policy and accepts the terms of service
					<span></span>
				</label>
			</div>
		</div>
    </div><?php
echo form_fieldset_close();
