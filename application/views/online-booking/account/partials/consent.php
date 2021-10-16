<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Marketing Consent</h3>
			<div class="form-group">
				<div class="checkbox">
					<label>
						<?php
						$data = array(
							'name' => 'marketing_consent',
							'id' => 'marketing_consent',
							'value' => 1
						);
						if (!isset($marketing_consent)) {
							$marketing_consent = 0;
						}
						if (set_value('marketing_consent', $marketing_consent) == 1) {
							$data['checked'] = TRUE;
						}
						echo form_checkbox($data);
						$question = $this->settings_library->get('participant_marketing_consent_question', $this->online_booking->accountID);
						$smart_tags = array(
							'{company}' => $this->online_booking->account->company
						);
						foreach($smart_tags as $key => $replace) {
							$question = str_replace($key, $replace, $question);
						}
						echo $question;
						?>
					</label>
				</div>
			</div>
			<?php
			if ($brands->num_rows() > 0) {
				?><div class="marketing_allowed">
					<p>Select the newsletters you wish to receive:</p>
					<div class="form-group">
						<?php
						$newsletters = array();
						if ($this->input->post()) {
							if (is_array($this->input->post('newsletters'))) {
								$newsletters = $this->input->post('newsletters');
							}
						} else {
							$newsletters = $existing_newsletters;
						}
						foreach ($brands->result() as $brand) {
							?><div class="checkbox">
								<label>
									<?php
									$data = array(
										'name' => 'newsletters[]',
										'id' => 'newsletter_' . $brand->brandID,
										'value' => $brand->brandID
									);
									if (in_array($brand->brandID, $newsletters)) {
										$data['checked'] = TRUE;
									}
									echo form_checkbox($data);
									echo $brand->name;
									?>
								</label>
							</div><?php
						}
						?>
					</div>
				</div><?php
			}
			?>
		</div>
		<div class="col-sm-6 marketing_allowed">
			<div class="form-group">
				<?php
				if (!isset($source)) {
					$source = NULL;
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
		</div>
		<div class="col-sm-6 marketing_allowed">
			<div class="form-group">
				<?php
				if (!isset($source_other)) {
					$source_other = NULL;
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
	</div>
</fieldset>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Privacy Policy</h3>
			<div class="terms_box">
				<?php
				$policies = array();
				$account_policy = $this->settings_library->get('participant_privacy', $this->online_booking->accountID);
				if (!empty($account_policy)) {
					echo '<h4>' . $this->online_booking->account->company . '</h4>';
					echo '<p>' . nl2br($account_policy) . '</p>';
					$policies[] = $this->online_booking->account->company;
				}
				$company_policy = $this->settings_library->get('company_privacy', 'default');
				if (!empty($company_policy)) {
					echo '<h4>' . $this->settings_library->get('company', 'default') . '</h4>';
					echo '<p>' . nl2br($company_policy) . '</p>';
					$policies[] = $this->settings_library->get('company', 'default');
				}
				?>
			</div>
			<div class="checkbox">
				<label>
					<?php
					$data = array(
						'name' => 'privacy_agreed',
						'id' => 'privacy_agreed',
						'value' => 1,
						'required' => 'required'
					);
					if (set_value('privacy_agreed') == 1) {
						$data['checked'] = TRUE;
					}
					echo form_checkbox($data);
					?>I have read the <?php echo implode(" and ", $policies); ?> privacy policy and accept the terms of service
				</label>
			</div>
		</div>
	</div>
</fieldset>
<?php
$account_safeguarding = $this->settings_library->get('participant_safeguarding', $this->online_booking->accountID);
if (!empty($account_safeguarding)) {
?>
<fieldset>
	<div class="row">
		<div class="col-xs-12">
			<h3 class="h4 with-line">Safeguarding</h3>
			<div class="terms_box">
				<?php
				echo '<h4>' . $this->online_booking->account->company . '</h4>';
				echo '<p>' . nl2br($account_safeguarding) . '</p>';
				?>
			</div>
			<div class="checkbox">
				<label>
					<?php
					$data = array(
						'name' => 'safeguarding_agreed',
						'id' => 'safeguarding_agreed',
						'value' => 1,
						'required' => 'required'
					);
					if (set_value('safeguarding_agreed') == 1) {
						$data['checked'] = TRUE;
					}
					echo form_checkbox($data);
					?>I have read the <?php echo $this->online_booking->account->company; ?> safeguarding policy
				</label>
			</div>
		</div>
	</div>
</fieldset>
<?php } ?>
<?php
$account_data_notice = $this->settings_library->get('participant_data_protection_notice', $this->online_booking->accountID);
if (!empty($account_data_notice)) {
	?>
	<fieldset>
		<div class="row">
			<div class="col-xs-12">
				<h3 class="h4 with-line">Data Protection Notice</h3>
				<div class="terms_box">
					<?php
					echo '<h4>' . $this->online_booking->account->company . '</h4>';
					echo '<p>' . nl2br($account_data_notice) . '</p>';
					?>
				</div>
				<div class="checkbox">
					<label>
						<?php
						$data = array(
							'name' => 'data_protection_agreed',
							'id' => 'data_protection_agreed',
							'value' => 1,
							'required' => 'required'
						);
						if (set_value('data_protection_agreed') == 1) {
							$data['checked'] = TRUE;
						}
						echo form_checkbox($data);
						?>I have read the <?php echo $this->online_booking->account->company; ?> data protection notice
					</label>
				</div>
			</div>
		</div>
	</fieldset>
<?php } ?>
