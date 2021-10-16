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
				<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
				<h3 class="card-label">Contact</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($contact_info->name)) {
						$name = $contact_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Position <em>*</em>', 'position');
					$position = NULL;
					if (isset($contact_info->position)) {
						$position = $contact_info->position;
					}
					$data = array(
						'name' => 'position',
						'id' => 'position',
						'class' => 'form-control',
						'value' => set_value('position', $this->crm_library->htmlspecialchars_decode($position), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Phone', 'tel');
					$tel = NULL;
					if (isset($contact_info->tel)) {
						$tel = $contact_info->tel;
					}
					$data = array(
						'name' => 'tel',
						'id' => 'tel',
						'class' => 'form-control',
						'value' => set_value('tel', $this->crm_library->htmlspecialchars_decode($tel), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Mobile', 'mobile');
					$mobile = NULL;
					if (isset($contact_info->mobile)) {
						$mobile = $contact_info->mobile;
					}
					$data = array(
						'name' => 'mobile',
						'id' => 'mobile',
						'class' => 'form-control',
						'value' => set_value('mobile', $this->crm_library->htmlspecialchars_decode($mobile), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Email <em>*</em>', 'email');
					$email = NULL;
					if (isset($contact_info->email)) {
						$email = $contact_info->email;
					}
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE),
						'maxlength' => 150
					);
					echo form_email($data);
				?></div><?php
			?></div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
				<h3 class="card-label">Customer Login</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Password (<a href="#" class="generatepassword">Generate?)</a>', 'password');
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
				if ($this->settings_library->get('send_customer_password') == 1) {
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
						<?php
						if ($contactID != NULL) {
							?><small class="text-muted form-text">Requires a new password entering as they are stored encrypted and can't be retrieved</small><?php
						}
						?>
					</div><?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	if ($brands->num_rows() > 0) {
		echo form_fieldset('', ['class' => 'card card-custom']);
			?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label">Newsletters</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'>
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
						?>
					</div>
				</div>
			</div><?php
		echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
