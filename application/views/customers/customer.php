<?php
display_messages();
if ($org_id != NULL) {
	$data = array(
		'orgID' => $org_id,
		'tab' => $tab
	);
	$this->load->view('customers/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'class="org"');
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Information</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($org_info->name)) {
						$name = $org_info->name;
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
				<?php
				switch ($org_type) {
					case 'school':
						?><div class='form-group'><?php
							echo form_label('Private/Local Authority <em>*</em>', 'isPrivate');
							$isPrivate = NULL;
							if (isset($org_info->isPrivate)) {
								$isPrivate = $org_info->isPrivate;
							}
							$options = array(
								'' => 'Select',
								'0' => 'Local Authority',
								'1' => 'Private'
							);
							echo form_dropdown('isPrivate', $options, set_value('isPrivate', $this->crm_library->htmlspecialchars_decode($isPrivate), FALSE), 'id="isPrivate" class="form-control select2"');
						?></div>
						<div class='form-group'><?php
							echo form_label('Type <em>*</em>', 'schoolType');
							$schoolType = NULL;
							if (isset($org_info->schoolType)) {
								$schoolType = $org_info->schoolType;
							}
							$options = array(
								'' => 'Select',
								'infant' => 'Infant',
								'junior' => 'Junior',
								'primary' => 'Primary',
								'secondary' => 'Secondary',
								'college' => 'College',
								'special' => 'Special',
								'other' => 'Other'
							);
							echo form_dropdown('schoolType', $options, set_value('schoolType', $this->crm_library->htmlspecialchars_decode($schoolType), FALSE), 'id="schoolType" class="form-control select2"');
						?></div><?php
						break;
				}
				?>
				<div class='form-group'><?php
					echo form_label('Email <em>*</em>', 'email');
					$email = NULL;
					if (isset($org_info->email)) {
						$email = $org_info->email;
					}
					$data = array(
						'name' => 'email',
						'id' => 'email',
						'class' => 'form-control',
						'value' => set_value('email', $this->crm_library->htmlspecialchars_decode($email), FALSE),
						'maxlength' => 150
					);
					echo form_email($data);
				?></div>
				<?php
				if (count($org_types) > 0){
					switch ($org_type) {
						case 'organisation':
							?><div class='form-group'><?php
							echo form_label('Organisation Type', 'org_typeID');
							$org_type_ID = NULL;
							if (isset($org_info->org_typeID)) {
								$org_type_ID = $org_info->org_typeID;
							}
							$options = array(
								'' => 'Select'
							);
							foreach($org_types as $key => $value){
								$options[$key] = $value;
							}
							echo form_dropdown('org_typeID', $options, set_value('org_typeID', $this->crm_library->htmlspecialchars_decode($org_type_ID), FALSE), 'id="org_typeID" class="form-control select2"');
							?></div>
							<?php
							break;
					}
				}
				?>
				<div class='form-group'><?php
					echo form_label('Web Site', 'website');
					$website = NULL;
					if (isset($org_info->website)) {
						$website = $org_info->website;
					}
					$data = array(
						'name' => 'website',
						'id' => 'website',
						'class' => 'form-control',
						'value' => set_value('website', $this->crm_library->htmlspecialchars_decode($website), FALSE),
						'maxlength' => 150
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Rate/Charge', 'rate');
					$rate = NULL;
					if (isset($org_info->rate)) {
						$rate = $org_info->rate;
					}
					$data = array(
						'name' => 'rate',
						'id' => 'rate',
						'class' => 'form-control',
						'value' => set_value('rate', $this->crm_library->htmlspecialchars_decode($rate), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Invoice Frequency', 'invoiceFrequency');
					$invoiceFrequency = NULL;
					if (isset($org_info->invoiceFrequency)) {
						$invoiceFrequency = $org_info->invoiceFrequency;
					}
					$options = array(
						'' => 'Select',
						'weekly' => 'Weekly',
						'monthly' => 'Monthly',
						'half termly' => 'Half Termly',
						'termly' => 'Termly',
						'annually' => 'Annually'
					);
					echo form_dropdown('invoiceFrequency', $options, set_value('invoiceFrequency', $this->crm_library->htmlspecialchars_decode($invoiceFrequency), FALSE), 'id="invoiceFrequency" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Status <em>*</em>', 'prospect');
					$prospect = NULL;
					if (isset($org_info->prospect)) {
						$prospect = $org_info->prospect;
					}
					$options = array();
					if (($org_type == 'school' && $this->auth->has_features('customers_schools_prospects') || ($org_type == 'organisation' && $this->auth->has_features('customers_orgs_prospects')))) {
						$options[1] = 'Prospect';
					}
					if (($org_type == 'school' && $this->auth->has_features('customers_schools') || ($org_type == 'organisation' && $this->auth->has_features('customers_orgs')))) {
						$options[0] = $this->settings_library->get_label('customer');
					}
					echo form_dropdown('prospect', $options, set_value('prospect', $this->crm_library->htmlspecialchars_decode($prospect), FALSE), 'id="prospect" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Region', 'regionID');
					$regionID = NULL;
					if (isset($org_info->regionID)) {
						$regionID = $org_info->regionID;
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
					echo form_label('Area', 'areaID');
					$areaID = NULL;
					if (isset($org_info->areaID)) {
						$areaID = $org_info->areaID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($areas->num_rows() > 0) {
						foreach ($areas->result() as $row) {
							$options[$row->areaID] = array(
								'name' => $row->name,
								'extras' => 'data-region="' . $row->regionID . '"'
							);
						}
					}
					echo form_dropdown_advanced('areaID', $options, set_value('areaID', $this->crm_library->htmlspecialchars_decode($areaID), FALSE), 'id="areaID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Staffing Notes', 'staffing_notes');
					$staffing_notes = NULL;
					if (isset($org_info->staffing_notes)) {
						$staffing_notes = $org_info->staffing_notes;
					}
					$data = array(
						'name' => 'staffing_notes',
						'id' => 'staffing_notes',
						'class' => 'form-control',
						'value' => set_value('staffing_notes', $this->crm_library->htmlspecialchars_decode($staffing_notes), FALSE)
					);
					echo form_textarea($data);
					?><small class="text-muted form-text">Shown above sessions list</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Tags', 'tags');
					$tags = array();
					if (isset($org_info->tags) && is_array($org_info->tags)) {
						$tags = $org_info->tags;
					}
					$options = array();
					if (count($tag_list) > 0) {
						foreach ($tag_list as $tag) {
							$options[$tag] = $tag;
						}
					}
					echo form_dropdown('tags[]', $options, set_value('tags', $tags), 'id="tags" multiple="multiple" class="form-control select2-tags"');
					?>
					<small class="text-muted form-text">Start typing to select a tag or create a new one.</small>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close();
	if ($org_id == NULL) {
		?>
		<?php echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-home text-contrast'></i></span>
					<h3 class="card-label">Address</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>

					<div class='form-group'><?php
						echo form_label('Address <em>*</em>', 'address1');
						$address1 = NULL;
						if (isset($org_info->address1)) {
							$address1 = $org_info->address1;
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
						if (isset($org_info->address2)) {
							$address2 = $org_info->address2;
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
						if (isset($org_info->address3)) {
							$address3 = $org_info->address3;
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
						if (isset($org_info->town)) {
							$town = $org_info->town;
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
						if (isset($org_info->county)) {
							$county = $org_info->county;
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
						if (isset($org_info->postcode)) {
							$postcode = $org_info->postcode;
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
						if (isset($org_info->phone)) {
							$phone = $org_info->phone;
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
	}
	if (count($lesson_types) > 0 && count($brands) > 0) {
		if ($this->input->post()) {
			$prices_array = $this->input->post('prices');
		}
		if (!is_array($prices_array)) {
			$prices_array = array();
		}
		echo form_fieldset('', ['class' => 'card card-custom']); ?>
			<div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-sack-dollar text-contrast'></i></span>
					<h3 class="card-label">Default Prices</h3>
				</div>
			</div>
			<div class="fixed-scrollbar customers"></div>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered' id="customerTable">
					<thead>
						<tr>
							<th rowspan="2"></th>
							<?php
							foreach ($brands as $brandID => $brand) {
								echo '<th colspan="2"><span class="label label-inline" style="' . label_style($brand_colours[$brandID]) . '">' . $brand . '</span></th>';
							}
							?>
						</tr>
						<tr>
							<?php
							foreach ($brands as $brandID => $brand) {
								echo '<th>Price</th>';
								echo '<th>Contract Pricing</th>';
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($lesson_types as $typeID => $type) {
							?><tr>
								<th><?php echo $type; ?></th>
								<?php
								foreach ($brands as $brandID => $brand) {
									$price = NULL;
									if (isset($prices_array[$typeID][$brandID]['amount'])) {
										$price = $prices_array[$typeID][$brandID]['amount'];
									}
									$data = array(
										'name' => 'prices[' . $typeID . '][' . $brandID . '][amount]',
										'class' => 'form-control',
										'value' => $price,
										'maxlength' => 10
									);
									?><td><?php echo form_input($data); ?></td><?php
									$data = array(
										'name' => 'prices[' . $typeID . '][' . $brandID . '][contract]',
										'value' => 1
									);
									if (isset($prices_array[$typeID][$brandID]['contract']) && $prices_array[$typeID][$brandID]['contract'] == 1) {
										$data['checked'] = TRUE;
									}
									?><td class="text-center"><?php echo form_checkbox($data); ?></td><?php
								}
								?>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div><?php
		echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close();
