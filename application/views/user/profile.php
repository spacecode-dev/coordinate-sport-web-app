<?php
display_messages();
?>
<div class='row'>
	<div class='col-sm-12'>
		<?php
		echo form_open('profile');
			echo form_fieldset('', ['class' => 'card card-custom']);
				?><div class='card-header'>
					<div class="card-title">
						<span class="card-icon"><i class='far fa-lock text-contrast'></i></span>
						<h3 class="card-label">Account Information</h3>
					</div>
				</div>
				<div class="card-body">
					<div class='multi-columns'>
						<div class='form-group'>
							<?php
							echo form_label('Current Password <em>*</em>', 'password_current');
							$data = array(
								'name' => 'password_current',
								'id' => 'password_current',
								'class' => 'form-control',
								'value' => set_value('password_current', NULL, FALSE),
								'autocomplete' => 'off'
							);
							echo form_password($data);
							?>
						</div>
						<div class='form-group'>
							<?php
							echo form_label('Password <em>*</em>', 'password');
							$data = array(
								'name' => 'password',
								'id' => 'password',
								'class' => 'form-control pwstrength',
								'value' => set_value('password', NULL, FALSE),
								'autocomplete' => 'off'
							);
							echo form_password($data);
							?>
						</div>
						<div class='form-group'><?php
							echo form_label('Confirm Password <em>*</em>', 'password_confirm');
								$data = array(
									'name' => 'password_confirm',
									'id' => 'password_confirm',
									'class' => 'form-control pwstrength',
									'value' => set_value('password_confirm', NULL, FALSE),
									'autocomplete' => 'off'
								);
								echo form_password($data);
						?></div>
					</div>
				</div>
				<div class='card-footer'>
					<button class='btn btn-primary btn-submit' type="submit">
						<i class='far fa-save'></i> Save
					</button>
				</div><?php
				echo form_fieldset_close();
			echo form_close();
			?>
	</div>
</div>
