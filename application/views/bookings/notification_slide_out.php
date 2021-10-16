
	<?php
		echo '<strong>' .form_label('Date/Time'). '</strong>';
		echo '<p>' . mysql_to_uk_datetime($notification_info->added) . '</p>';
		echo '<strong>' .form_label('Contact') . '</strong>';
		echo '<p>' . $notification_info->name . '</p>';
	?>
	<?php
	if (!empty($notification_info->subject)) {
		echo '<strong>' .form_label('Subject'). '</strong>';
		echo '<p>' . $notification_info->subject . '</p>';
	}
	?>
	<?php
		echo '<strong>' .form_label('Message'). '</strong>';
		if (!empty($notification_info->contentHTML)) {
			echo "<div class='card card-custom h-50 overflow-auto'><div class='card-body'>";
			echo '<div class="html_email">' . $notification_info->contentHTML . '</div>';
			echo '</div></div>';
		} else {
			echo '<p>' . nl2br($notification_info->contentText) . '</p>';
		}
	?>
	<?php
	if ($attachments->num_rows() > 0) {
		?><?php
			if ($attachments->num_rows() == 1) {
				echo '<strong>' .form_label('Attachment'). '</strong>';
			} else {
				echo '<strong>' .form_label('Attachments'). '</strong>';
			}
			?><ul><?php
				foreach ($attachments->result() as $row) {
					echo '<li>' . anchor('attachment/event/' . $row->path, $row->name, 'target="_blank"') . '</li>';
				}
				?></ul><?php
		}
	?>
	<?php
	echo '<strong>' .form_label('Destination'). '</strong>';
	if ($notification_info->type == 'email' && !empty($notification_info->destination)) {
		?><p><a href="mailto:<?php echo $notification_info->destination; ?>"><?php echo $notification_info->destination; ?></a></p><?php
	} else {
		echo '<p>' . $notification_info->destination . '</p>';
	}
	?>
	<?php
	echo '<strong>' .form_label('Status'). '</strong>';
	echo '<p>' . ucwords($notification_info->status) . '</p>';
	?>
	<?php
	echo '<strong>' .form_label('Sent By'). '</strong>';
	echo '<p>' . $notification_info->staff . '</p>';
	?>

