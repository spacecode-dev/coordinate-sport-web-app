<?php
display_messages();
if ($familyID != NULL) {
	$data = array(
		'familyID' => $familyID,
		'tab' => $tab
	);
	$this->load->view('participants/tabs.php', $data);
}
echo form_open(); ?>
<div class='card card-custom'>
	<div class="card-body">
		<div class="row">
			<div class='col-xs-6 col-sm-4 col-md-2'>
				<p>
					<strong><label for="account_balance">Account Balance</label></strong>
				</p>
				<?php
				$data = array(
					'id' => 'account_balance',
					'class' => 'form-control',
					'disabled' => 'disabled',
					'value' => $family->account_balance
				);
				?>
				<div class="input-group">
					<div class="input-group-append"><span class="input-group-text"><?php echo currency_symbol(); ?></span></div>
					<?php echo form_input($data); ?>
				</div>
			</div>
			<?php
			if ($this->settings_library->get('enable_credit_limits') == 1) {
				?><div class='col-xs-6 col-sm-4 col-md-2'>
					<p>
						<strong><label for="credit_limit">Credit Limit</label></strong>
					</p>
					<?php
					$credit_limit = $this->settings_library->get('default_credit_limit');
					if (!empty($family->credit_limit)) {
						$credit_limit = $family->credit_limit;
					}
					$data = array(
						'name' => 'credit_limit',
						'id' => 'credit_limit',
						'class' => 'form-control',
						'min' => 0,
						'step' => 0.01,
						'value' => set_value('credit_limit', $credit_limit, FALSE)
					);
					$max_credit_limit = $this->settings_library->get('max_credit_limit');
					if (!empty($max_credit_limit)) {
						$data['max'] = $max_credit_limit;
					}
					?>
					<div class="input-group">
						<div class="input-group-append"><span class="input-group-text"><?php echo currency_symbol(); ?></span></div>
						<?php echo form_number($data); ?>
					</div>
				</div>
				<div class='col-xs-12 col-sm-4 col-md-2'>
					<p>
						<strong><label>&nbsp;</label></strong>
					</p>
					<?php
					$data = array(
						'value' => 'Update',
						'class' => 'btn btn-primary'
					);
					echo form_submit($data);
					?>
				</div><?php
			}
			?>
		</div>
	</div>
</div>
<?php echo form_close(); ?>
<div class='card card-custom'>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Account Holders</h3>
		</div>
	</div>
	<?php
	if ($contacts->num_rows() == 0) {
		?>
		<div class="card-body">
			<div class="alert alert-info">
				No contacts found. Do you want to <?php echo anchor('participants/contacts/'.$familyID.'/new', 'create one'); ?>?
			</div>
		</div>
		<?php
	} else {
		?><div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Account Type
						</th>
						<th>
							Phone
						</th>
						<th>
							Email
						</th>
						<th>
							Address
						</th>
						<th class="w-25">
							Book
						</th>
						<th>
							Main
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($contacts->result() as $row) {
						?>
						<tr>
							<td>
								<div class="name participant-club">
									<?php
									$profile_pic = @unserialize($row->profile_pic);
									if($profile_pic !== FALSE){
										$args = array(
											'alt' => 'Image',
											'src' => 'attachment/participant/profile_pic/thumb/'.$row->contactID,
											'class' => 'responsive-img'
										);
										echo '<div class="profile_pic">' . img($args) . '</div>';
									}else{
										echo "<div class='img-container bg-random-".substr($row->contactID, -1)."'>".substr(trim($row->first_name), 0, 1)."</div>";
									}
									echo anchor('participants/contacts/edit/' . $row->contactID, trim(ucwords($row->title) . ' ' . $row->first_name . ' ' . $row->last_name));
									if ($row->blacklisted == 1) {
										echo ' <span class="label label-red">Blacklisted</span>';
									}
									?>
								</div>
							</td>
							<td>
								<?php echo ucwords($row->relationship); ?>
							</td>
							<td>
								<?php
								$numbers = array();
								if (!empty($row->mobile)) {
									$numbers[] = $row->mobile;
								}
								if (!empty($row->phone)) {
									$numbers[] = $row->phone;
								}
								if (!empty($row->workPhone)) {
									$numbers[] = $row->workPhone;
								}
								if (count($numbers) > 0) {
									echo implode(", ", $numbers);
								}
								?>
							</td>
							<td class="has_icon">
								<?php
								if (!empty($row->email)) {
									?><a href="mailto:<?php echo $row->email; ?>" class="btn btn-default btn-sm"><i class="far fa-envelope"></i></a><?php
								}
								?>
							</td>
							<td>
								<?php
								$address_parts = array();
								if (!empty($row->address1)) {
									$address_parts[] = $row->address1;
								}
								if (!empty($row->address2)) {
									$address_parts[] = $row->address2;
								}
								if (!empty($row->address3)) {
									$address_parts[] = $row->address3;
								}
								if (!empty($row->town)) {
									$address_parts[] = $row->town;
								}
								if (!empty($row->county)) {
									$address_parts[] = $row->county;
								}
								if (!empty($row->postcode)) {
									$address_parts[] = $row->postcode;
								}
								if (count($address_parts) > 0) {
									echo implode(", ", $address_parts);
								}
								?>
							</td>
							<td class="w-25">
								<?php
								echo form_open('booking/cart/jump/' . $row->contactID, 'class="book_online"');
									$options = array(
										'' => 'Select event'
									);
									foreach ($upcoming_events_individuals as $blockID => $event) {
										$age = calculate_age($row->dob, $event['age_at']);
										if ((empty($event['min_age']) || $age >= $event['min_age']) && ((empty($event['max_age'])) || $age <= $event['max_age'])) {
											$options[$blockID] = $event['label'];
										}
									}
									echo form_dropdown('blockID', $options, NULL, 'class="blockID form-control select2"');
								echo form_close();
								?>
							</td>
							<td class="has_icon">
								<?php
								if($row->main == 1) {
									// set main contact for book dropdown
									$main_contact = $row->contactID;
									?><span class='btn btn-success btn-sm no-action' title="Yes">
										<i class='far fa-check'></i>
									</span><?php
								} else {
									?><a href="<?php echo site_url('participants/maincontact/' . $row->contactID); ?>" class='btn btn-danger btn-sm' title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right fixed-2-icons'>
									<?php
									if ($this->auth->has_features('participants')) {
										?><a class='btn btn-info btn-sm' href='<?php echo site_url('booking/cart/init/' . $row->contactID); ?>' title="View Online Booking Cart">
											<i class='far fa-shopping-cart'></i>
										</a><?php
									}
									?>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('participants/contacts/edit/' . $row->contactID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<?php
									if ($row->main != 1 || $children->num_rows() == 0) {
										?><a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('participants/contacts/remove/' . $row->contactID); ?>' title="Remove">
											<i class='far fa-trash'></i>
										</a><?php
									}
									?>
								</div>
							</td>
						</tr>
						<?php
					}
					// if no main contact, use first from above
					if (empty($main_contact)) {
						foreach ($contacts->result() as $row) {
							$main_contact = $row->contactID;
							break;
						}
					}
					?>
				</tbody>
			</table>
		</div><?php
	}
	?>
</div>
<div class='card card-custom'>
	<div class='card-header'>
		<div class="card-title">
			<span class="card-icon"><i class='far fa-user text-contrast'></i></span>
			<h3 class="card-label">Participants</h3>
		</div>
	</div>
	<?php
	if ($children->num_rows() == 0) {
		?>
		<div class="card-body">
			<div class="alert alert-info">
				No participants found. Do you want to <?php echo anchor('participants/participant/'.$familyID.'/new', 'create one'); ?>?
			</div>
		</div>
		<?php
	} else {
		?>
		<div class='table-responsive'>
			<table class='table table-striped table-bordered'>
				<thead>
					<tr>
						<th>
							Name
						</th>
						<th>
							Age
						</th>
						<th>
							School
						</th>
						<th>
							Medical
						</th>
						<th>
							Pickup PIN
						</th>
						<th class="w-300px">
							Book
						</th>
						<th class="w-30px">
							Photo Consent
						</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ($children->result() as $row) {
						?>
						<tr>
							<td>
								<div class="name participant-club">
									<?php
									$profile_pic = @unserialize($row->profile_pic);
									if($profile_pic !== FALSE){
										$args = array(
											'alt' => 'Image',
											'src' => 'attachment/participant_child/profile_pic/thumb/'.$row->childID,
											'class' => 'responsive-img'
										);
										echo '<div class="profile_pic">' . img($args) . '</div>';
									}else{
										echo "<div class='img-container bg-random-".substr($row->childID, -1)."'>".substr(trim($row->first_name), 0, 1)."</div>";
									}
									echo anchor('participants/participant/edit/' . $row->childID, trim($row->first_name . ' ' . $row->last_name)); ?>

								</div>
							</td>
							<td>
								<span title="<?php echo date("d/m/Y", strtotime($row->dob)); ?>"><?php echo $this->crm_library->get_age($row->dob); ?></span>
							</td>
							<td>
								<?php echo $row->school; ?>
							</td>
							<td>
								<?php echo nl2br($row->medical); ?>
							</td>
							<td>
								<?php echo ($row->pin != 0)?$row->pin:''; ?>
							</td>
							<td class="w-300px">
								<?php
								if (!empty($main_contact)) {
									echo form_open('booking/cart/jump/' . $main_contact . '/' . $row->childID, 'class="book_online"');
										$options = array(
											'' => 'Select event'
										);
										foreach ($upcoming_events as $blockID => $event) {
											$age = calculate_age($row->dob, $event['age_at']);
											if ((empty($event['min_age']) || $age >= $event['min_age']) && ((empty($event['max_age'])) || $age <= $event['max_age'])) {
												$options[$blockID] = $event['label'];
											}
										}
										echo form_dropdown('blockID', $options, NULL, 'class="blockID form-control select2"');
									echo form_close();
								}
								?>
							</td>
							<td class="has_icon ajax_toggle w-30px">
								<?php
								if($row->photoConsent == 1) {
									?><a class='btn btn-success btn-sm' href="<?php echo site_url('participants/photoconsent/' . $row->childID); ?>/no" title="Yes">
										<i class='far fa-check'></i>
									</a><?php
								} else {
									?><a class='btn btn-danger btn-sm' href="<?php echo site_url('participants/photoconsent/' . $row->childID); ?>/yes" title="No">
										<i class='far fa-times'></i>
									</a><?php
								}
								?>
							</td>
							<td>
								<div class='text-right fixed-2-icons'>
									<a class='btn btn-warning btn-sm' href='<?php echo site_url('participants/children/edit/' . $row->childID); ?>' title="Edit">
										<i class='far fa-pencil'></i>
									</a>
									<a class='btn btn-danger btn-sm confirm-delete' href='<?php echo site_url('participants/children/remove/' . $row->childID); ?>' title="Remove">
										<i class='far fa-trash'></i>
									</a>
								</div>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
			</table>
		</div><?php
	}
?>
</div>
