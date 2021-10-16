<?php
display_messages();

if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
}
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
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
						echo form_label('Type <em>*</em>', 'type');
						$type = NULL;
						if (isset($address_info->type)) {
							$type = $address_info->type;
						}
						$options = array(
							'' => 'Select',
							'delivery' => 'Delivery',
							'billing' => 'Billing',
							'other' => 'Other'
						);
						echo form_dropdown('type', $options, set_value('type', $this->crm_library->htmlspecialchars_decode($type), FALSE), 'id="type" class="form-control select2"');
					?></div><?php
				} else {
					echo form_hidden('type', 'main');
				}
				?>
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
						'maxlength' => 255
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
						'maxlength' => 255
					);
					echo form_input($data);
				?><br /><?php
					$address3 = NULL;
					if (isset($address_info->address3)) {
						$address3 = $address_info->address3;
					}
					$data = array(
						'name' => 'address3',
						'id' => 'address3',
						'class' => 'form-control',
						'value' => set_value('address3', $this->crm_library->htmlspecialchars_decode($address3), FALSE),
						'maxlength' => 255
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
				<div class='form-group'><?php
					echo form_label('Phone <em>*</em>', 'phone');
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
				?></div><?php
			?></div>
		</div><?php
	echo form_fieldset_close();
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
