<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-rss text-contrast'></i></span>
				<h3 class="card-label">Feed</h3>
			</div>
		</div>
		<div class="card-body">
			<?php
			if ($this->auth->user->feed_enabled == 1) {
				$data = array(
					'action' => 'disable'
				);
				echo form_hidden($data);
				?><p>Your calendar feed is currently <strong>enabled</strong>. To access it, use the following URL in your calendar application:</p>
				<div class='form-group'><?php
					echo form_label('Feed URL', 'feed_url');
					$data = array(
						'name' => 'feed_url',
						'id' => 'feed_url',
						'class' => 'form-control',
						'value' => str_replace(array('https', 'http'), 'webcal', site_url('feed/' . $this->auth->user->feed_key)),
						'readonly' => 'readonly'
					);
					echo form_input($data);
				?></div>
				<p>Setup Instructions: <?php echo anchor('https://support.google.com/calendar/answer/37100', 'Google Calendar', 'target="_blank"'); ?> | <?php echo anchor('https://support.office.com/en-us/article/Import-or-subscribe-to-a-calendar-in-Outlook-com-or-Outlook-on-the-web-cff1429c-5af6-41ec-a5b4-74f2c278e98c', 'Outlook.com/Office 365', 'target="_blank"'); ?> (Choose <strong>Subscribe</strong>, not Import or your calendar will not be automatically updated)</p>
				<p><strong>Warning:</strong> Anyone with this URL can access your timetable information. If you are no longer are using this feature or suspect someone is using your feed, disable it below to prevent access. You can always re-enable it later which will generate a new Feed URL. Information might be out of date depending on how often your application checks for updates.</p>
				<button class='btn btn-danger btn-submit' type="submit">
					Disable Feed
				</button><?php
			} else {
				$data = array(
					'action' => 'enable'
				);
				echo form_hidden($data);
				?><p>Your calendar feed is currently <strong>disabled</strong>, click the button below to enable it.</p>
				<button class='btn btn-success btn-submit' type="submit">
					Enable Feed
				</button><?php
			}
			?>
		</div><?php
	echo form_fieldset_close();
echo form_close();
