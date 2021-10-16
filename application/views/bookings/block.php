<?php
display_messages();

if ($bookingID != NULL) {
	$data = array(
		'bookingID' => $bookingID,
		'blockID' => $blockID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
echo form_open_multipart($submit_to, 'class="block'.($number_of_sessions>0 ? " has-sessions" : "").'" id="blockform"');
	echo "<input type='hidden' name='flag' id='flag' value='0' />";
	 echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='row'>
				<div class='col-md-6 form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($block_info->name)) {
						$name = $block_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
					if ($booking_info->project == 1) {
					?>
					<small class="text-muted form-text">
						If applicable, this name will also appear on the bookings site.
					</small>
					<?php } ?>
				</div>
				<div class="col-md-6 form-group">
					<div class="row">
						<div class='col-md-6 form-group'><?php
							echo form_label('Start Date <em>*</em>', 'startDate');
							$startDate = NULL;
							if (isset($block_info->startDate)) {
								$startDate = mysql_to_uk_date($block_info->startDate);
							}
							$data = array(
								'name' => 'startDate',
								'id' => 'startDate',
								'class' => 'form-control datepicker',
								'value' => set_value('startDate', $this->crm_library->htmlspecialchars_decode($startDate), FALSE),
								'maxlength' => 10,
								'data-mindate' => $booking_info->startDate,
								'data-maxdate' => $booking_info->endDate
							);
							echo form_input($data);
						?></div>
				
						<div class='col-md-6 form-group'><?php
							echo form_label('End Date <em>*</em>', 'endDate');
							$endDate = NULL;
							if (isset($block_info->endDate)) {
								$endDate = mysql_to_uk_date($block_info->endDate);
							}
							$data = array(
								'name' => 'endDate',
								'id' => 'endDate',
								'class' => 'form-control datepicker',
								'value' => set_value('endDate', $this->crm_library->htmlspecialchars_decode($endDate), FALSE),
								'maxlength' => 10,
								'data-mindate' => $booking_info->startDate,
								'data-maxdate' => $booking_info->endDate
							);
							echo form_input($data);
						?></div>
					</div>
				</div><?php
				if ($booking_info->type == 'event' || $booking_info->project == 1) {
					?><div class='col-md-6 form-group'><?php
						echo form_label('Misc. Income (' . currency_symbol() . ')', 'misc_income');
						$misc_income = NULL;
						if (isset($block_info->misc_income) && $block_info->misc_income > 0) {
							$misc_income = $block_info->misc_income;
						}
						$data = array(
							'name' => 'misc_income',
							'id' => 'misc_income',
							'class' => 'form-control',
							'value' => set_value('misc_income', $this->crm_library->htmlspecialchars_decode($misc_income), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?>
					<small class="text-muted form-text">
						This can be any income in addition to that received from participant customer session pricing. For example, if sessions are free then this field can be used to add a set amount to the P&L.
					</small>
					</div><?php
				}
				
				if ($booking_info->type == 'event' || $booking_info->project == 1)  {
					?>
					<div class="col-md-6 form-group">
						<div class="row">
							<div class='col-md-6 form-group'><?php
								echo form_label('Minimum Age', 'min_age');
								$min_age = NULL;
								if (isset($block_info->min_age)) {
									$min_age = $block_info->min_age;
								}
								$data = array(
									'name' => 'min_age',
									'id' => 'min_age',
									'class' => 'form-control',
									'value' => set_value('min_age', $this->crm_library->htmlspecialchars_decode($min_age), FALSE),
									'maxlength' => 3
								);
								?><div class="input-group"><?php
								echo form_input($data);
								?><div class="input-group-append"><span class="input-group-text">Years</span></div></div>
								<?php
								$default_min_age = $this->settings_library->get('min_age');
								if (!empty($booking_info->min_age)) {
									$default_min_age = $booking_info->min_age;
								}
								?>
								<small class="text-muted form-text">If not set, <?php
								if (empty($default_min_age)) {
									echo 'no limits';
								} else {
									echo 'a default of ' . $default_min_age;
								}
								?> will apply. Can be overridden per session.</small>
							</div>
					
							<div class='col-md-6 form-group'><?php
								echo form_label('Maximum Age', 'max_age');
								$max_age = NULL;
								if (isset($block_info->max_age)) {
									$max_age = $block_info->max_age;
								}
								$data = array(
									'name' => 'max_age',
									'id' => 'max_age',
									'class' => 'form-control',
									'value' => set_value('max_age', $this->crm_library->htmlspecialchars_decode($max_age), FALSE),
									'maxlength' => 3
								);
								?><div class="input-group"><?php
								echo form_input($data);
								?><div class="input-group-append"><span class="input-group-text">Years</span></div></div>
								<?php
								$default_max_age = $this->settings_library->get('max_age');
								if (!empty($booking_info->max_age)) {
									$default_max_age = $booking_info->max_age;
								}
								?>
								<small class="text-muted form-text">If not set, <?php
								if (empty($default_max_age)) {
									echo 'no limits';
								} else {
									echo 'a default of ' . $default_max_age;
								}
								?>
									will apply. Can be overridden per session.</small>
							</div>
						</div>
					</div>
					<div class="col-md-6 avoid-break">
						<div class='form-group'><?php
							echo form_label('Send Thanks Email', 'thanksemail');
							$data = array(
								'name' => 'thanksemail',
								'id' => 'thanksemail',
								'data-togglecheckbox' => 'thanksemail_text',
								'value' => 1
							);
							$thanksemail = NULL;
							if (isset($block_info->thanksemail)) {
								$thanksemail = $block_info->thanksemail;
							}
							if (set_value('thanksemail', $this->crm_library->htmlspecialchars_decode($thanksemail), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div>
							<small class="text-muted form-text">This email will be sent to participant customers at the end date of the block.</small>
						</div>
						
						<div class='form-group'><?php
							echo form_label('Thanks Email <em>*</em>', 'thanksemail_text');
							$thanksemail_text = $this->settings_library->get('email_block_thanks');
							if (isset($block_info->thanksemail_text) && !empty($block_info->thanksemail_text)) {
								$thanksemail_text = $block_info->thanksemail_text;
							}
							$data = array(
								'name' => 'thanksemail_text',
								'id' => 'thanksemail_text',
								'class' => 'form-control wysiwyg',
								'value' => set_value('thanksemail_text', $this->crm_library->htmlspecialchars_decode($thanksemail_text), FALSE),
							);
							echo form_textarea($data);
							?><small class="text-muted form-text">Available tags: {contact_title}, {contact_first}, {contact_last}, {event_name}, {block_name}, {website}</small>
						</div>
					</div>
				<?php
				}
				
				?><div class='col-md-6 form-group'><?php
					echo form_label('Provisional', 'provisional');
					$data = array(
						'name' => 'provisional',
						'id' => 'provisional',
						'value' => 1
					);
					$provisional = NULL;
					if (isset($block_info->provisional)) {
						$provisional = $block_info->provisional;
					}
					if (set_value('provisional', $this->crm_library->htmlspecialchars_decode($provisional), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">This can be used if you are provisionally scheduling sessions that will not be displayed to delivery staff.</small>
				</div>
				<div class='col-md-6 form-group'><?php
					echo form_label('Terms & Conditions', 'terms_accepted');
					$data = array(
						'name' => 'terms_accepted',
						'id' => 'terms_accepted',
						'value' => 1
					);
					$terms_accepted = NULL;
					if (isset($block_info->terms_accepted)) {
						$terms_accepted = $block_info->terms_accepted;
					}
					if (set_value('terms_accepted', $this->crm_library->htmlspecialchars_decode($terms_accepted), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">Customer has returned or accepted the Terms &amp; Conditions for this block of sessions</small>
				</div>
				<div class='col-md-6 form-group'><?php
					echo form_label('Staffing Notes', 'staffing_notes');
					$staffing_notes = NULL;
					if (isset($block_info->staffing_notes)) {
						$staffing_notes = $block_info->staffing_notes;
					}
					$data = array(
						'name' => 'staffing_notes',
						'id' => 'staffing_notes',
						'class' => 'form-control',
						'value' => set_value('staffing_notes', $this->crm_library->htmlspecialchars_decode($staffing_notes), FALSE)
					);
					echo form_textarea($data);
					?><small class="text-muted form-text">
						This note will be shown to staff when scheduling sessions.
					</small>
				</div>
			</div>
		</div><?php
	echo form_fieldset_close();
	if ($booking_info->type == 'booking' || (($booking_info->type == 'event' || $booking_info->project == 1) && $this->auth->has_features('online_booking') && $booking_info->public == 1)) {
		echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-chart-bar text-contrast'></i></span>
					<h3 class="card-label">Bookings Site</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<?php
					if (($booking_info->type == 'event' || $booking_info->project == 1) && $this->auth->has_features('online_booking') && $booking_info->public == 1) {
						?><div class='form-group'>
							<?php
							echo form_label('Show on Bookings Site', 'public');
							$data = array(
								'name' => 'public',
								'id' => 'public',
								'value' => 1,
								'data-togglecheckbox' => 'require_all_sessions'
							);
							$public = NULL;
							if (isset($block_info->public)) {
								$public = $block_info->public;
							} else {
								// get from booking if new block
								$public = $booking_info->public;
							}
							if (set_value('public', $this->crm_library->htmlspecialchars_decode($public), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div>
						</div>
						<div class='form-group'>
							<?php
							echo form_label('Require All Sessions to be Booked', 'require_all_sessions');
							$data = array(
								'name' => 'require_all_sessions',
								'id' => 'require_all_sessions',
								'value' => 1,
								'data-togglecheckbox' => 'block_price'
							);
							$require_all_sessions = NULL;
							if (isset($block_info->require_all_sessions)) {
								$require_all_sessions = $block_info->require_all_sessions;
							}
							if (set_value('require_all_sessions', $this->crm_library->htmlspecialchars_decode($require_all_sessions), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div>
							<small class="text-muted form-text">If checked, when the participant chooses one session, all other sessions within the block will be automatically checked</small>
						</div>
						<div class='form-group'><?php
							echo form_label('Set Block Price (' . currency_symbol() . ')', 'block_price');
							$block_price = NULL;
							if (isset($block_info->block_price) && $block_info->block_price > 0) {
								$block_price = $block_info->block_price;
							}
							$data = array(
								'name' => 'block_price',
								'id' => 'block_price',
								'class' => 'form-control',
								'value' => set_value('block_price', $this->crm_library->htmlspecialchars_decode($block_price), FALSE),
								'maxlength' => 10
							);
							echo form_input($data);
							?>
							<small class="text-muted form-text">If not set, the total of the remaining sessions will be used as the block price.</small>
						</div><?php
					}

					if ($booking_info->type == 'booking') {
						?><div class='form-group'>
							<div class='form-group'><?php
							echo form_label($this->settings_library->get_label('customer') . ' Bookable', 'org_bookable');
							$data = array(
								'name' => 'org_bookable',
								'id' => 'org_bookable',
								'data-togglecheckbox' => 'website_description',
								'value' => 1
							);
							$org_bookable = NULL;
							if (isset($block_info->org_bookable)) {
								$org_bookable = $block_info->org_bookable;
							}
							if (set_value('org_bookable', $this->crm_library->htmlspecialchars_decode($org_bookable), FALSE) == 1) {
								$data['checked'] = TRUE;
							}
							?><div class="checkbox-single">
								<label class="checkbox">
									<?php echo form_checkbox($data); ?>
									Yes
									<span></span>
								</label>
							</div>
							<small class="text-muted form-text">If checked, customers (schools or organisations) can book this block if their tags match at least one of those in the booking</small>
							</div>
							<?php
							if ($this->auth->has_features('online_booking')) {
								?><div class='form-group'><?php
								echo form_label('Web Site Description', 'website_description');
								$website_description = NULL;
								if (isset($block_info->website_description) && !empty($block_info->website_description)) {
									$website_description = $block_info->website_description;
								}
								$data = array(
									'name' => 'website_description',
									'id' => 'website_description',
									'class' => 'form-control',
									'value' => set_value('website_description', $this->crm_library->htmlspecialchars_decode($website_description), FALSE),
								);
								echo form_textarea($data);
								?><small class="text-muted form-text">For customer login</small>
								</div><?php
							} ?>
						</div><?php
					}
					?>
				</div>
			</div><?php
		echo form_fieldset_close();
	}
	if ($booking_info->type == 'event' || $booking_info->project == 1) {
		echo form_fieldset('', ['class' => 'card card-custom card-collapsed']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-chart-bar text-contrast'></i></span>
					<h3 class="card-label">Targets</h3>
				</div>
				<div class="card-toolbar">
					<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
						<i class="ki ki-arrow-down icon-nm"></i>
					</a>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'><?php
						echo form_label('Profit (' . currency_symbol() . ')', 'target_profit');
						$target_profit = NULL;
						if (isset($block_info->target_profit) && $block_info->target_profit > 0) {
							$target_profit = $block_info->target_profit;
						}
						$data = array(
							'name' => 'target_profit',
							'id' => 'target_profit',
							'class' => 'form-control',
							'value' => set_value('target_profit', $this->crm_library->htmlspecialchars_decode($target_profit), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Costs (' . currency_symbol() . ')', 'target_costs');
						$target_costs = NULL;
						if (isset($block_info->target_costs) && $block_info->target_costs > 0) {
							$target_costs = $block_info->target_costs;
						}
						$data = array(
							'name' => 'target_costs',
							'id' => 'target_costs',
							'class' => 'form-control',
							'value' => set_value('target_costs', $this->crm_library->htmlspecialchars_decode($target_costs), FALSE),
							'maxlength' => 10
						);
						echo form_input($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Weekly ' . $this->settings_library->get_label('participants'), 'target_weekly');
						$target_weekly = NULL;
						if (isset($block_info->target_weekly) && $block_info->target_weekly > 0) {
							$target_weekly = $block_info->target_weekly;
						}
						$data = array(
							'name' => 'target_weekly',
							'id' => 'target_weekly',
							'class' => 'form-control',
							'value' => set_value('target_weekly', $this->crm_library->htmlspecialchars_decode($target_weekly), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div>
					<div class='form-group'><?php
						echo form_label($this->settings_library->get_label('participant') . ' Sessions', 'target_total');
						$target_total = NULL;
						if (isset($block_info->target_total) && $block_info->target_total > 0) {
							$target_total = $block_info->target_total;
						}
						$data = array(
							'name' => 'target_total',
							'id' => 'target_total',
							'class' => 'form-control',
							'value' => set_value('target_total', $this->crm_library->htmlspecialchars_decode($target_total), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Unique ' . $this->settings_library->get_label('participants'), 'target_unique');
						$target_unique = NULL;
						if (isset($block_info->target_unique) && $block_info->target_unique > 0) {
							$target_unique = $block_info->target_unique;
						}
						$data = array(
							'name' => 'target_unique',
							'id' => 'target_unique',
							'class' => 'form-control',
							'value' => set_value('target_unique', $this->crm_library->htmlspecialchars_decode($target_unique), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Retained ' . $this->settings_library->get_label('participants'), 'target_retention');
						$target_retention = NULL;
						if (isset($block_info->target_retention) && $block_info->target_retention > 0) {
							$target_retention = $block_info->target_retention;
						}
						$data = array(
							'name' => 'target_retention',
							'id' => 'target_retention',
							'class' => 'form-control',
							'value' => set_value('target_retention', $this->crm_library->htmlspecialchars_decode($target_retention), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div>
					<div class='form-group'><?php
						echo form_label('Retained Weeks', 'target_retention_weeks');
						$target_retention_weeks = NULL;
						if (isset($block_info->target_retention_weeks) && $block_info->target_retention_weeks > 0) {
							$target_retention_weeks = $block_info->target_retention_weeks;
						}
						$data = array(
							'name' => 'target_retention_weeks',
							'id' => 'target_retention_weeks',
							'class' => 'form-control',
							'value' => set_value('target_retention_weeks', $this->crm_library->htmlspecialchars_decode($target_retention_weeks), FALSE),
							'maxlength' => 10
						);
						echo form_number($data);
					?></div>
				</div>
			</div><?php
		echo form_fieldset_close();
	}
	if ($type == 'booking') {
		echo form_fieldset('', ['class' => 'card card-custom']);
			?><div class='card-header'>
				<div class="card-title">
					<span class="card-icon"><i class='far fa-map-marker-alt text-contrast'></i></span>
					<h3 class="card-label">Location</h3>
				</div>
			</div>
			<div class="card-body">
				<div class='multi-columns'>
					<div class='form-group'><?php
						echo form_label('Customer or Venue <em>*</em>', 'orgID');
						$orgID = NULL;
						if (isset($booking_info->orgID)) {
							$orgID = $booking_info->orgID;
						}
						// override if set in block
						if (isset($block_info->orgID) && !empty($block_info->orgID)) {
							$orgID = $block_info->orgID;
						}
						
						if ($orgs->num_rows() > 0) {
							foreach ($orgs->result() as $row) {
								$options[$row->orgID] = $row->name;
							}
						}
						echo form_dropdown('orgID', $options, set_value('orgID', $this->crm_library->htmlspecialchars_decode($orgID), FALSE), 'id="orgID" class="form-control select2"');
						?><small class="text-muted form-text">If you wish to assign this block to another customer, select them here</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Delivery Address <em>*</em>', 'addressID');
						$addressID = NULL;
						if (isset($block_info->addressID)) {
							$addressID = $block_info->addressID;
						}
						if (isset($booking_info->addressID)) {
							$addressID = $booking_info->addressID;
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
									$options[$row->addressID] = array(
										'name' => implode(", ", $addresses),
										'extras' => 'data-org="' . $row->orgID . '"'
									);
								}
							}
						}
						echo form_dropdown_advanced('addressID', $options, set_value('addressID', $this->crm_library->htmlspecialchars_decode($addressID), FALSE), 'id="addressID" class="form-control select2"');
					?></div>
					<input type="hidden" name="old_address_id" id="old_address_id" value="<?php echo $this->crm_library->htmlspecialchars_decode($addressID) ?>" />
				</div>
			</div>
		<?php echo form_fieldset_close();
	}
	?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Save
		</button>
		<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	</div>
<?php echo form_close(); ?>
<!-- Data Model for Profile Picture -->
<div class="modal fade" id="myModal" role="dialog">
	<div class="modal-dialog modal-dialog-centered" style="width:50%; min-width:600px">
		<!-- Modal content-->
		<div class="modal-content" style="background-color:#2a89ec;">
			<div class="modal-body">
				<div style="text-align:center">
					<img src="<?php echo $this->crm_library->asset_url("public/images/warning-icons.png")?>" title="Warning Icon" width="50px" />
				</div>
				<div style="text-align:center">
					<p style="color:white; padding-top:3%">Would you like to apply this change to all session addresses within this block?</p><br />
				</div>
				<div style="text-align:center">
					<a href="javascript:void(0)" id="Yesbutton" class="btn btn-default"  style="background-color:#2D7190; border-color: #2D7190; color:#fff; padding:1% 5%"> Yes </a>&nbsp;&nbsp;
					<a href="javascript:void(0)" id="Nobutton" class="btn btn-default" style="background-color:#2D7190; border-color: #2D7190; color:#fff; padding:1% 5%"> No </a>
				</div>
			</div>
		</div>
	</div>
</div>
<?
