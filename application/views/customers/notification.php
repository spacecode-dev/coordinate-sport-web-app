<?php
display_messages();

if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
}
echo form_open_multipart();
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-bell text-contrast'></i></span>
				<h3 class="card-label">Notification</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Date/Time');
					echo '<p>' . mysql_to_uk_datetime($notification_info->added) . '</p>';
				?></div>
				<div class='form-group'><?php
					echo form_label('Contact');
					echo '<p>' . $notification_info->contact . '</p>';
				?></div>
				<?php
				if (!empty($notification_info->subject)) {
					?><div class='form-group'><?php
						echo form_label('Subject');
						echo '<p>' . $notification_info->subject . '</p>';
					?></div><?php
				}
				?>
				<div class='form-group'><?php
					echo form_label('Message');
					if (!empty($notification_info->contentHTML)) {
						echo '<div class="html_email">' . $notification_info->contentHTML . '</div>';
					} else {
						echo '<p>' . nl2br($notification_info->contentText) . '</p>';
					}
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (count($attachments) > 0) {
					?><div class='form-group'><?php
						if (count($attachments) == 1) {
							echo form_label('Attachment');
						} else {
							echo form_label('Attachments');
						}
						?><ul><?php
							foreach ($attachments as $path => $name) {
								echo '<li>' . anchor($path, $name, 'target="_blank"') . '</li>';
							}
							?></ul>
						</div><?php
					}
				?>
				<div class='form-group'><?php
					echo form_label('Destination');
					if ($notification_info->type == 'email' && !empty($notification_info->destination)) {
						?><p><a href="mailto:<?php echo $notification_info->destination; ?>"><?php echo $notification_info->destination; ?></a></p><?php
					} else {
						echo '<p>' . $notification_info->destination . '</p>';
					}
				?></div>
				<div class='form-group'><?php
					echo form_label('Status');
					echo '<p>' . ucwords($notification_info->status) . '</p>';
				?></div>
				<div class='form-group'><?php
					echo form_label('Sent By');
					echo '<p>' . $notification_info->staff . '</p>';
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
echo form_close();
