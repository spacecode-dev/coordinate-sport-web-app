<?php
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		echo form_hidden('section', 'customer');
		?>
		<div class='card-header'>
			<div class="card-title pt-4 pb-4 flex-column align-items-start">
				<div class="d-flex align-items-center">
					<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
					<h3 class="card-label d-flex flex-column">Customer Notification</h3>
				</div>
				<small class="pt-2">This notification will be sent to the funder, i.e. the school or organisation, associated with this booking.</small>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				if (isset($contacts)) {
					?><div class='form-group'><?php
						echo form_label('Contact <em>*</em>', 'contactID');
						$options = array();
						if ($contacts->num_rows() > 0) {
							foreach ($contacts->result() as $row) {
								$options[$row->contactID] = $row->name;
								if ($row->isMain == 1) {
									$options[$row->contactID] .= ' (Main)';
								}
							}
						}
						echo form_dropdown('contactID', $options, set_value('contactID'), 'id="contactID" class="form-control select2"');
						?>
					</div><?php
				}
				?>
				<div class='form-group'><?php
					echo form_label('Subject <em>*</em>', 'field_subject');
					$data = array(
						'name' => 'subject',
						'id' => 'field_subject',
						'class' => 'form-control',
						'value' => set_value('subject', $this->crm_library->htmlspecialchars_decode($subject), FALSE),
						'maxlength' => 200
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Content <em>*</em>', 'field_content');
					$data = array(
						'name' => 'content',
						'id' => 'field_content',
						'class' => 'form-control wysiwyg',
						'value' => set_value('content', $this->crm_library->htmlspecialchars_decode($content), FALSE)
					);
					echo form_textarea($data);
					if (isset($contacts)) {
						?><small class="text-muted form-text">Available tags: {contact_name}</small><?php
					}
				?></div>
				<?php
				if (isset($attachment_field) && $attachment_field === TRUE) {
					?><div class='form-group'><?php
						echo form_label('Attachment', 'field_file');
						$data = array(
							'name' => 'file',
							'id' => 'field_file',
							'class' => 'custom-file-input'
						);
						?><div class="custom-file">
							<?php echo form_upload($data); ?>
							<label class="custom-file-label" for="file">Choose file</label>
						</div>
					</div><?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			Send
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
if($exception_info->type === "cancellation"){
	if (!$all_refunds_processed && count($participant_contacts) > 0) {
		//Only add the callback (and as such the popup) if there are refunds to process
		echo form_open_multipart($refund_callback, array("class" => "refund-callback"));
		echo form_hidden('data', base64_encode(serialize($participant_contacts)));
		echo form_close();
	}

	echo form_open_multipart($submit_to);
		echo form_fieldset('', ['class' => 'card card-custom']);
			echo form_hidden('section', 'participants');
			?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Participant Customer Notification</h3>
			</div>
		</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (isset($contacts)) {
						?><div class='form-group'><?php
						echo form_label('Contact <em>*</em>', 'participant_contactID');?>
						<select name="participant_contactID[]" multiple="multiple" id="participant_contactID" multiple="multiple" class="form-control select2 select2-tag">
						<?php $options = array();
						if (count($participant_contacts) > 0) {
							$selected= 'selected="selected"';$all_flag = 0;
							if($form_status && ($this->input->post('participant_contactID') && in_array('all',$this->input->post('participant_contactID'))) ||
								count($this->input->post('participant_contactID')) == count($participant_contacts)
							){
								$all_flag = 1;
								$selected= 'selected="selected"';
							}
							echo '<option '.$selected.' value="all">All Participants</option>';
							foreach ($participant_contacts as $key => $value) {
								$selected= '';
								if($form_status && $this->input->post('participant_contactID') && in_array($key,$this->input->post('participant_contactID')) && !$all_flag){
									$selected= 'selected="selected"';
								}
								echo '<option '.$selected.' data-is-sub="'.$value['is_sub'].'" value="'.$key.'">'.$value['name'].'</option>';
							}
						}else{
							echo '<option>No Participants</option>';
						}?>
						</select>
						</div><?php
					}
					if (count($participant_contacts) > 0) { ?>
						<input type="hidden" name="data" value='<?php echo base64_encode(serialize($participant_contacts)); ?>' />
					<?php }
					?>
					<div class='form-group'><?php
						echo form_label('Subject <em>*</em>', 'field_subject');
						$data = array(
							'name' => 'participant_subject',
							'id' => 'field_subject',
							'class' => 'form-control',
							'value' => set_value('participant_subject', $this->crm_library->htmlspecialchars_decode($participant_subject), FALSE),
							'maxlength' => 200
						);
						echo form_input($data);
						?></div>
					<div class='form-group'><?php
						echo form_label('Content <em>*</em>', 'participant_content');
						$data = array(
							'name' => 'participant_content',
							'id' => 'field_content',
							'class' => 'form-control wysiwyg',
							'value' => set_value('participant_content', $this->crm_library->htmlspecialchars_decode($participant_content), FALSE)
						);
						echo form_textarea($data);
						if (isset($contacts)) {
							?><small class="text-muted form-text">Available tags: {contact_name}</small><?php
						}
						?></div>
				</div>
			</div><?php
		echo form_fieldset_close();
		?>
		<div class='form-actions d-flex justify-content-between'>
			<button class='btn btn-primary btn-submit' type="submit">
				Send
			</button>
			<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
		</div>
	<?php echo form_close();
}
$modal_show = 0;
if(!empty($success) || !empty($info) || !empty($error) || count($errors) > 0){
	$modal_show = 1;
}

?>
<div class="modal fade" id="myModal_message" role="dialog" data-display="<?php echo $modal_show;?>">
	<div class="modal-dialog " style="width:50%; min-width:600px">
		<!-- Modal content-->
		<div class="modal-content">
			<div class="modal-body p-0" id="verification">
				<div style="" id="msg">
					<?php display_messages(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
