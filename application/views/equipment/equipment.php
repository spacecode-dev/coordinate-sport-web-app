<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-futbol text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($equipment_info->name)) {
						$name = $equipment_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Location <em>*</em>', 'location');
					$location = NULL;
					if (isset($equipment_info->location)) {
						$location = $equipment_info->location;
					}
					$data = array(
						'name' => 'location',
						'id' => 'location',
						'class' => 'form-control',
						'value' => set_value('location', $this->crm_library->htmlspecialchars_decode($location), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Quantity <em>*</em>', 'quantity');
					$quantity = NULL;
					if (isset($equipment_info->quantity)) {
						$quantity = $equipment_info->quantity;
					}
					$data = array(
						'name' => 'quantity',
						'id' => 'quantity',
						'class' => 'form-control',
						'value' => set_value('quantity', $this->crm_library->htmlspecialchars_decode($quantity), FALSE),
						'maxlength' => 10,
						'min' => 1
					);
					echo form_number($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Notes', 'notes');
					$notes = NULL;
					if (isset($equipment_info->notes)) {
						$notes = $equipment_info->notes;
					}
					$data = array(
						'name' => 'notes',
						'id' => 'notes',
						'class' => 'form-control',
						'value' => set_value('notes', $this->crm_library->htmlspecialchars_decode($notes), FALSE),								);
					echo form_textarea($data);
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
