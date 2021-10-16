<?php
display_messages();
echo form_open_multipart($submit_to, 'class="resource"');
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
        <div class='card-header'>
        	<div class="card-title">
        		<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
        		<h3 class="card-label">Details</h3>
        	</div>
        </div>
        <div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('File', 'file');
					$data = array(
						'name' => 'file',
						'id' => 'file',
						'class' => 'custom-file-input'
					);
					?><div class="custom-file">
						<?php echo form_upload($data); ?>
						<label class="custom-file-label" for="file">Choose file</label>
					</div>
				</div>
				<div class='form-group'><?php
					echo form_label('Category <em>*</em>', 'category');
					$options = array(
						'' => 'Select'
					);
					foreach ($resources as $key => $value) {
						$options[$key] = array(
							'name' => $value['resourceName']  ,
							'extras' => 'data-type="' . $value['resourceName'] . '"'
						);
					}
					$selectedResourceID = isset($resource)?$resource['resourceID']:'';
					echo form_dropdown_advanced('category', $options, set_value('category', $this->crm_library->htmlspecialchars_decode($selectedResourceID), FALSE), 'id="category" class="form-control select2"');
				?></div>				 
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