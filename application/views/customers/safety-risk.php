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
					echo form_label('Address <em>*</em>', 'addressID');
					$addressID = NULL;
					if (isset($doc_info->addressID)) {
						$addressID = $doc_info->addressID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($addresses->num_rows() > 0) {
						foreach ($addresses->result() as $row) {
							$addresses = array();
							if (!empty($row->address1)) {
								$addresses[] = $row->address1;
							}
							if (!empty($row->address2)) {
								$addresses[] = $row->address2;
							}
							if (!empty($row->address3)) {
								$addresses[] = $row->address3;
							}
							if (!empty($row->town)) {
								$addresses[] = $row->town;
							}
							if (!empty($row->county)) {
								$addresses[] = $row->county;
							}
							if (!empty($row->postcode)) {
								$addresses[] = $row->postcode;
							}
							if (count($addresses) > 0) {
								$options[$row->addressID] = implode(", ", $addresses);
							}
						}
					}
					echo form_dropdown('addressID', $options, set_value('addressID', $this->crm_library->htmlspecialchars_decode($addressID), FALSE), 'id="addressID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Session Type', 'typeID');
					$typeID = NULL;
					if (isset($doc_info->typeID)) {
						$typeID = $doc_info->typeID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($lesson_types->num_rows() > 0) {
						foreach ($lesson_types->result() as $row) {
							$options[$row->typeID] = $row->name;
						}
					}
					echo form_dropdown('typeID', $options, set_value('typeID', $this->crm_library->htmlspecialchars_decode($typeID), FALSE), 'id="typeID" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Location', 'field_location');
					$location = NULL;
					if (isset($doc_info->details['location'])) {
						$location = $doc_info->details['location'];
					}
					$data = array(
						'name' => 'location',
						'id' => 'field_location',
						'class' => 'form-control',
						'value' => set_value('location', $this->crm_library->htmlspecialchars_decode($location), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Assessor <em>*</em>', 'byID');
					$byID = NULL;
					if (isset($doc_info->byID)) {
						$byID = $doc_info->byID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' . $row->surname;
						}
					}
					echo form_dropdown('byID', $options, set_value('byID', $this->crm_library->htmlspecialchars_decode($byID), FALSE), 'id="byID" class="form-control select2"');
				?></div>
	            <div class='form-group'><?php
					echo form_label($this->settings_library->get_label('brand') . ' <em>*</em>', 'brandID');
					$brandID = NULL;
					if (isset($doc_info->brandID)) {
						$brandID = $doc_info->brandID;
					}
					$options = array(
						'' => 'Select'
					);
					if ($brands->num_rows() > 0) {
						foreach ($brands->result() as $row) {
							$options[$row->brandID] = $row->name;
						}
					}
					echo form_dropdown('brandID', $options, set_value('brandID', $this->crm_library->htmlspecialchars_decode($brandID), FALSE), 'id="brandID" class="form-control select2"');
					?></div>
				<div class='form-group'><?php
					echo form_label('Date <em>*</em>', 'date');
					$date = NULL;
					if (isset($doc_info->date)) {
						$date = mysql_to_uk_date($doc_info->date);
					}
					$data = array(
						'name' => 'date',
						'id' => 'date',
						'class' => 'form-control datepicker',
						'value' => set_value('date', $this->crm_library->htmlspecialchars_decode($date), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Expiry <em>*</em>', 'expiry');
					$expiry = NULL;
					if (isset($doc_info->expiry)) {
						$expiry = mysql_to_uk_date($doc_info->expiry);
					}
					$data = array(
						'name' => 'expiry',
						'id' => 'expiry',
						'class' => 'form-control datepicker',
						'value' => set_value('expiry', $this->crm_library->htmlspecialchars_decode($expiry), FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Person/Group at Risk <em>*</em>', 'field_who');
					$who = NULL;
					if (isset($doc_info->details['who'])) {
						$who = $doc_info->details['who'];
					}
					$data = array(
						'name' => 'who',
						'id' => 'field_who',
						'class' => 'form-control',
						'value' => set_value('who', $this->crm_library->htmlspecialchars_decode($who), FALSE),
						'maxlength' => 255
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label('Final Assessment &amp; Comments <em>*</em>', 'field_final');
					$final = NULL;
					if (isset($doc_info->details['final'])) {
						$final = $doc_info->details['final'];
					}
					// convert pre-wysiwyg fields to html
					if ($final == strip_tags($final)) {
						$final = '<p>' . nl2br($final) . '</p>';
					}
					$data = array(
						'name' => 'final',
						'id' => 'field_final',
						'class' => 'form-control wysiwyg',
						'value' => set_value('final', $this->crm_library->htmlspecialchars_decode($final), FALSE)
					);
					echo form_textarea($data);
				?></div>
			</div>
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

if ($doc_id != NULL) {
	?><div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Description of Task/Process</h3>
			</div>
		</div>
		<div class="card-body">
			<?php
			$smart_tags = array(
				'{company}' => $this->auth->account->company
			);
			$desc = $this->settings_library->get('safety_risk_desc');
			foreach ($smart_tags as $key => $value) {
				$desc = str_replace($key, $value, $desc);
			}
			echo $desc;
			?>
		</div>
	</div>
	<div class='card card-custom'>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Hazards</h3>
			</div>
			<div class="card-toolbar">
	            <?php echo anchor('customers/safety/hazard/' . $doc_id . '/new', 'Add Hazard', ['class' => 'btn btn-success font-weight-bold']); ?>
	        </div>
		</div>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Hazard
						</th>
						<th>
							Potential Effect
						</th>
						<th class="min">
							Likelihood 1-5
						</th>
						<th class="min">
							Severity 1-5
						</th>
						<th class="min">
							Risk 1-25
						</th>
						<th>
							Minimise Risk By (Control Measures)
						</th>
						<th class="min">
							Residual Risk 1-25
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ($hazards->num_rows() == 0) {
						?><tr>
							<td colspan="8">None</td>
						</tr><?php
					} else {
						foreach ($hazards->result() as $row) {
							?>
							<tr>
								<td class="name">
									<?php echo anchor('customers/safety/hazard/' . $row->hazardID, $row->hazard); ?>
								</td>
								<td>
									<?php echo nl2br($row->potential_effect); ?>
								</td>
								<td class="min">
									<?php echo $row->likelihood; ?>
								</td>
								<td class="min">
									<?php echo $row->severity; ?>
								</td>
								<td class="min">
									<?php echo $row->risk; ?>
								</td>
								<td>
									<?php echo nl2br($row->control_measures); ?>
								</td>
								<td class="min">
									<?php echo $row->residual_risk; ?>
								</td>
								<td>
									<div class='text-right'>
										<a class='btn btn-warning btn-sm' href='<?php echo site_url('customers/safety/hazard/' . $row->hazardID); ?>' title="Edit">
											<i class='far fa-pencil'></i>
										</a>
										<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('customers/safety/hazard/remove/' . $row->hazardID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a>
									</div>
								</td>
							</tr>
							<?php
						}
					}
					?>
					<tr>
						<td>
							<p><strong>Likelihood of occurrence</strong></p>
							<ol>
								<li>Highly unlikely ever to occur</li>
								<li>Could occur but very rarely</li>
								<li>Could occur rarely</li>
								<li>Could occur from time to time</li>
								<li>Likely to occur often</li>
							</ol>
						</td>
						<td>
							<p><strong>Severity of outcome</strong></p>
							<ol>
								<li>Slight inconvenience</li>
								<li>Minor injury requiring first aid</li>
								<li>Medical attention required</li>
								<li>Major injury leading to hospitalisation</li>
								<li>Fatality or serious injury leading to disability</li>
							</ol>
						</td>
						<td colspan="4">
							<p><strong>Risk = Likelihood x Severity</strong></p>
							<p>&lt;7 = Tolerable<br />
							8-16 = Not acceptable unless strict control measures in place and monitored throughout activity<br />
							16-25= Un Safe - Do Not Use or Do Activity</p>
						</td>
						<td colspan="2">

						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div><?php
}
