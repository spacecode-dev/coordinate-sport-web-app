<?php
display_messages();

if ($staffID != NULL) {
	$data = array(
		'staffID' => $staffID,
		'tab' => $tab
	);
	$this->load->view('staff/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
				<h3 class="card-label">Address</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<?php
				$type = NULL;
				if (isset($address_info->type)) {
					$type = $address_info->type;
				}
				if ($type != 'main') {
					?><div class='form-group'><?php
						echo form_label('Type <em>*</em>', 'staff_addresstype');
						$type = NULL;
						if (isset($address_info->type)) {
							$type = $address_info->type;
						}
						$options = array(
							'' => 'Select',
							'additional' => 'Additional',
							'emergency' => 'Emergency'
						);
						echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="staff_addresstype" class="form-control select2"');
					?></div><?php
				} else {
					echo '<input type="hidden" name="type" value="main" id="staff_addresstype" />';
				}
				?>
				<div class='form-group emergency'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($address_info->name)) {
						$name = $address_info->name;
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
				<div class='form-group emergency'><?php
					echo form_label('Relationship <em>*</em>', 'relationship');
					$relationship = NULL;
					if (isset($address_info->relationship)) {
						$relationship = $address_info->relationship;
					}
					$data = array(
						'name' => 'relationship',
						'id' => 'relationship',
						'class' => 'form-control',
						'value' => set_value('relationship', $this->crm_library->htmlspecialchars_decode($relationship), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Address <em>*</em>', 'address1');
					$address1 = NULL;
					if (isset($address_info->address1)) {
						$address1 = $address_info->address1;
					}
					$data = array(
						'name' => 'address1',
						'id' => 'address1',
						'class' => 'form-control',
						'value' => set_value('address1', $this->crm_library->htmlspecialchars_decode($address1), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?><br /><?php
					$address2 = NULL;
					if (isset($address_info->address2)) {
						$address2 = $address_info->address2;
					}
					$data = array(
						'name' => 'address2',
						'id' => 'address2',
						'class' => 'form-control',
						'value' => set_value('address2', $this->crm_library->htmlspecialchars_decode($address2), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Town <em>*</em>', 'town');
					$town = NULL;
					if (isset($address_info->town)) {
						$town = $address_info->town;
					}
					$data = array(
						'name' => 'town',
						'id' => 'town',
						'class' => 'form-control',
						'value' => set_value('town', $this->crm_library->htmlspecialchars_decode($town), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label(localise('county') . ' <em>*</em>', 'county');
					$county = NULL;
					if (isset($address_info->county)) {
						$county = $address_info->county;
					}
					$data = array(
						'name' => 'county',
						'id' => 'county',
						'class' => 'form-control',
						'value' => set_value('county', $this->crm_library->htmlspecialchars_decode($county), FALSE),
						'maxlength' => 50
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Post Code <em>*</em>', 'postcode');
					$postcode = NULL;
					if (isset($address_info->postcode)) {
						$postcode = $address_info->postcode;
					}
					$data = array(
						'name' => 'postcode',
						'id' => 'postcode',
						'class' => 'form-control',
						'value' => set_value('postcode', $this->crm_library->htmlspecialchars_decode($postcode), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group emergency'><?php
					echo form_label('Phone', 'phone');
					$phone = NULL;
					if (isset($address_info->phone)) {
						$phone = $address_info->phone;
					}
					$data = array(
						'name' => 'phone',
						'id' => 'phone',
						'class' => 'form-control',
						'value' => set_value('phone', $this->crm_library->htmlspecialchars_decode($phone), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group emergency'><?php
					echo form_label('Mobile', 'mobile');
					$mobile = NULL;
					if (isset($address_info->mobile)) {
						$mobile = $address_info->mobile;
					}
					$data = array(
						'name' => 'mobile',
						'id' => 'mobile',
						'class' => 'form-control',
						'value' => set_value('mobile', $this->crm_library->htmlspecialchars_decode($mobile), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group non-emergency'><?php
					echo form_label('From <em>*</em>', 'fromM');
					$fromM = NULL;
					if (isset($address_info->from)) {
						$fromM = date("n", strtotime($address_info->from));
					}
					$options = array(
						'' => 'Select',
						'1' => 'January',
						'2' => 'February',
						'3' => 'March',
						'4' => 'April',
						'5' => 'May',
						'6' => 'June',
						'7' => 'July',
						'8' => 'August',
						'9' => 'September',
						'10' => 'October',
						'11' => 'November',
						'12' => 'December'
					);
					echo form_dropdown('fromM', $options, set_value('fromM', $this->crm_library->htmlspecialchars_decode($fromM), FALSE), 'id="fromM" class="form-control select2"');
					$fromY = NULL;
					if (isset($address_info->from)) {
						$fromY = date("Y", strtotime($address_info->from));
					}
					$options = array(
						'' => 'Select',
					);
					$y = date("Y");
					while ($y >= date("Y")-100) {
						$options[$y] = $y;
						$y--;
					}
					echo form_dropdown('fromY', $options, set_value('fromY', $this->crm_library->htmlspecialchars_decode($fromY), FALSE), 'id="fromY" class="form-control select2"');
				?></div>
				<div class='form-group non-emergency'><?php
					echo form_label('To', 'toM');
					$toM = NULL;
					if (isset($address_info->to)) {
						$toM = date("n", strtotime($address_info->to));
					}
					$options = array(
						'' => 'Select',
						'1' => 'January',
						'2' => 'February',
						'3' => 'March',
						'4' => 'April',
						'5' => 'May',
						'6' => 'June',
						'7' => 'July',
						'8' => 'August',
						'9' => 'September',
						'10' => 'October',
						'11' => 'November',
						'12' => 'December'
					);
					echo form_dropdown('toM', $options, set_value('toM', $this->crm_library->htmlspecialchars_decode($toM), FALSE), 'id="toM" class="form-control select2"');
					$toY = NULL;
					if (isset($address_info->to)) {
						$toY = date("Y", strtotime($address_info->to));
					}
					$options = array(
						'' => 'Select',
					);
					$y = date("Y");
					while ($y >= date("Y")-100) {
						$options[$y] = $y;
						$y--;
					}
					echo form_dropdown('toY', $options, set_value('toY', $this->crm_library->htmlspecialchars_decode($toY), FALSE), 'id="toY" class="form-control select2"');
				?></div><?php
			?></div><?php
		echo form_fieldset_close();
		?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url(); ?>" class="btn btn-default">Cancel</a>
	</div><?php
echo form_close();
