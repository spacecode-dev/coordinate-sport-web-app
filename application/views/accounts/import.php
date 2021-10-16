<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-server text-contrast'></i></span>
				<h3 class="card-label">Import Information</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='form-group'><?php
				echo form_label('Import Type <em>*</em>', 'import_type');
				$options = array(
					'' => 'Select',
					'Participants' => 'Participants',
					'Customers' => 'Customers',
					'Staff' => 'Staff',
					'Equipment' => 'Equipment'
				);
				echo form_dropdown('import_type', $options, set_value('import_type'), 'id="import_type" class="form-control select2"');
			?></div>
			<div class='form-group'><?php
				echo form_label('Excel File <em>*</em>', 'excel_file');
				$data = array(
					'name' => 'excel_file',
					'id' => 'excel_file',
					'class="custom-file-input'
				);
				?>
				<div class="custom-file">
					<?php echo form_upload($data); ?>
					<label class="custom-file-label" for="excel_file">Choose file</label>
				</div>
				<small class="text-muted form-text"><?php echo anchor('public/documents/Coordinate Data Capture Sheet.xlsx', 'Download Sample File', 'target="_blank"'); ?></small>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Import
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
