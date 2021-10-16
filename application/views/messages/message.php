<?php
echo form_open_multipart();
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-inbox text-contrast'></i></span>
				<h3 class="card-label">Message</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Date/Time');
					echo '<p>' . mysql_to_uk_datetime($message_info->added) . '</p>';
				?></div>
				<div class='form-group'><?php
					echo form_label('Subject');
					echo '<p>' . $message_info->subject . '</p>';
				?></div>
				<div class='form-group'><?php
					echo form_label('Message');
					echo '<p>' . $message_info->message . '</p>';
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
				if ($attachments->num_rows() > 0) {
					?><div class='form-group'><?php
						if ($attachments->num_rows() == 1) {
							echo form_label('Attachment');
						} else {
							echo form_label('Attachments');
						}
						?><ul><?php
							foreach ($attachments->result() as $row) {
								echo '<li>' . anchor('attachment/message/' . $row->path . '_' . $row->attachmentID, $row->name, 'target="_blank"') . '</li>';
							}
							?></ul>
						</div><?php
					}
				?>
				<div class='form-group'><?php
					if ($message_info->folder == 'inbox') {
						echo form_label('Sent By');
					} else {
						echo form_label('Sent To');
					}
					$group_origin = $group;
					if($group == "archive"){
						$group_origin = $message_info->group;
					}
					switch($group_origin){
						case "staff":
							echo '<p>' .$staff_info->first . ' ' . $staff_info->surname. '</p>';
							break;
						case "participants":
							echo '<p>' .$staff_info->first_name . ' ' . $staff_info->last_name. '</p>';
							break;
						default:
							echo '<p>' .$staff_info->name. '</p>';
							break;
					}
				?></div>
			</div>
		</div><?php
	echo form_fieldset_close();
echo form_close();
