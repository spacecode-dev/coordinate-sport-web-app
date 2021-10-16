<?php
display_messages();
echo form_open_multipart($submit_to); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="multi-columns">
			    <div class='form-group'><?php
			        echo form_label('Name <em>*</em>', 'name');
			        $name = NULL;
			        if (isset($template_info->name)) {
			            $name = $template_info->name;
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
			        echo form_label('Subject <em>*</em>', 'subject');
			        $subject = NULL;
			        if (isset($template_info->subject)) {
			            $subject = $template_info->subject;
			        }
			        $data = array(
			            'name' => 'subject',
			            'id' => 'subject',
			            'class' => 'form-control',
			            'value' => set_value('subject', $this->crm_library->htmlspecialchars_decode($subject), FALSE),
			            'maxlength' => 100
			        );
			        echo form_input($data);
			        ?>
			    </div>
			    <div class='form-group message-form'><?php
			        echo form_label('Message <em>*</em>', 'message');
			        $message = NULL;
			        if (isset($template_info->message)) {
			            $message = $template_info->message;
			        }
			        $data = array(
			            'name' => 'message',
			            'id' => 'message',
			            'class' => 'form-control wysiwyg',
			            'value' => set_value('message', $message, FALSE),
			        );
			        echo form_textarea($data);
			        ?>
			    </div>
			    <div class='form-group'><?php
			        echo form_label('Attachments', 'file');
			        $data = array(
			            'name' => 'files[]',
			            'id' => 'file',
			            'class' => 'custom-file-input',
			            'multiple' => 'multiple'
			        );
			        ?><div class="custom-file">
			        	<?php echo form_upload($data); ?>
			        	<label class="custom-file-label" for="file">Choose file</label>
			        </div>
			        <small class='text-muted form-text'>Hold Ctrl/Command to select multiple files</small>
			    </div>
			    <?php if ($template_attachments) { ?>
				    <div class="attachments">
				        <ul>
				            <?php foreach ($template_attachments as $template_attachment) { ?>
				                <li>
				                    <a href="/attachment/message_template/<?php echo $template_attachment->path ?>" target="_blank"><?php echo $template_attachment->name ?></a> <a href="/messages/template/remove_attachment/<?php echo $template_attachment->id ?>"><i class="far fa-trash"></i></a>
				                </li>
				            <?php } ?>
				        </ul>
				    </div>
			    <?php } ?>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
