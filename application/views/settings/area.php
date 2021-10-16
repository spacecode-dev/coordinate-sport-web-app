<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Region <em>*</em>', 'regionID');
					$regionID = NULL;
					if (isset($area_info->regionID)) {
						$regionID = $area_info->regionID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($regions->num_rows() > 0) {
						foreach ($regions->result() as $row) {
							$options[$row->regionID] = $row->name;
						}
					}
					echo form_dropdown('regionID', $options, set_value('regionID', $this->crm_library->htmlspecialchars_decode($regionID), FALSE), 'id="regionID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($area_info->name)) {
						$name = $area_info->name;
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
			</div>
		<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
