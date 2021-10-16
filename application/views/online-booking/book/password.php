<h1 class="with-line"><?php echo $title; ?></h1>
<?php display_messages('fas'); ?>
<p>Only people with a password for this event can book. If you have the password, please enter it below to continue.</p>
<div class='row'>
	<div class='col-sm-12 col-md-6'>
		<?php
		echo form_open();
			?><fieldset>
				<div class='form-group'>
					<?php
					echo form_label('Password <em>*</em>', 'online_booking_password');
					$data = array(
						'name' => 'online_booking_password',
						'id' => 'online_booking_password',
						'class' => 'form-control',
						'value' => set_value('online_booking_password', NULL, FALSE)
					);
					echo form_password($data);
					?>
				</div>
			</fieldset>
			<button class='btn'>Continue</button>
		<?php echo form_close(); ?>
	</div>
</div>
