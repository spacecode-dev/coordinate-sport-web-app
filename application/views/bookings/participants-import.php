<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_hidden(array('action', 'upload'));
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-paperclip text-contrast'></i></span>
				<h3 class="card-label">Import Names</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='form-group'><?php
				echo form_label('Excel File <em>*</em>', 'excel_file');
				$data = array(
					'name' => 'excel_file',
					'id' => 'excel_file',
					'class' => 'custom-file-input'
				);
				?>
				<div class="custom-file">
					<?php echo form_upload($data); ?>
					<label class="custom-file-label" for="excel_file">Choose file</label>
				</div>
				<small class="text-muted form-text"><?php echo anchor('bookings/participants/importsample/' . $bookingID, 'Download Sample File', 'target="_blank"'); ?> (Column ordering should not be changed)</small>
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
