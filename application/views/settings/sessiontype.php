<?php
display_messages();
echo form_open_multipart($submit_to);
	 echo form_fieldset('', ['class' => 'card card-custom overflow-visible']);
		?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-book text-contrast'></i></span>
				<h3 class="card-label">Details</h3>
			</div>
		</div>
		<div class="card-body">
			<div class="row ">
				<div class='col-md-6 form-group'><?php
					echo form_label('Name <em>*</em>', 'field_name');
					$name = NULL;
					if (isset($type_info->name)) {
						$name = $type_info->name;
					}
					$data = array(
						'name' => 'name',
						'id' => 'field_name',
						'class' => 'form-control',
						'value' => set_value('name', $this->crm_library->htmlspecialchars_decode($name), FALSE),
						'maxlength' => 100
					);
					echo form_input($data);
				?></div>
				<?php
				if ($this->auth->has_features('online_booking')) {
					?>
					<div class='col-md-6 form-group'><?php
						echo form_label('Colour', 'field_colour');
						$colour = NULL;
						if (isset($type_info->colour)) {
							$colour = $type_info->colour;
							if (substr($colour, 0, 1) !== '#') {
								$colour = colour_to_hex($colour);
							}
						}
						$data = array(
							'name' => 'colour',
							'id' => 'field_colour',
							'class' => 'form-control colorpicker',
							'value' => set_value('colour', $this->crm_library->htmlspecialchars_decode($colour), FALSE),
							'maxlength' => 20
						);
						echo form_input($data);
						?><small class="text-muted form-text">Shown on online booking. Defaults to colour from <?php echo strtolower($this->settings_library->get_label('brand')); ?>, if not set.</small>
					</div>
					<?php
				}
				?>
			</div>
		</div><?php
	echo form_fieldset_close();
	echo form_fieldset('', ['class' => 'card card-custom']);
	   ?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Functionality</h3>
			</div>
	   </div>
	   <div class="card-body">
			<div class="multi-columns">
			   <div class='form-group'><?php
					echo form_label('Show on Dashboard', 'show_dashboard');
					$data = array(
						'name' => 'show_dashboard',
						'id' => 'show_dashboard',
						'value' => 1
					);
					$show_dashboard = NULL;
					if (isset($type_info->show_dashboard)) {
						$show_dashboard = $type_info->show_dashboard;
					}
					if (set_value('show_dashboard', $this->crm_library->htmlspecialchars_decode($show_dashboard), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">Shows next 10 upcoming events on dashboard</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Exclude from Automatic Discount', 'exclude_autodiscount');
					$data = array(
						'name' => 'exclude_autodiscount',
						'id' => 'exclude_autodiscount',
						'value' => 1
					);
					$exclude_autodiscount = NULL;
					if (isset($type_info->exclude_autodiscount)) {
						$exclude_autodiscount = $type_info->exclude_autodiscount;
					}
					if (set_value('exclude_autodiscount', $this->crm_library->htmlspecialchars_decode($exclude_autodiscount), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">Exclude from automatic discount calculations</small>
				</div>
				<div class='form-group'><?php
					echo form_label('Show Label on Payment Register', 'show_label_register');
					$data = array(
						'name' => 'show_label_register',
						'id' => 'show_label_register',
						'value' => 1
					);
					$show_label_register = NULL;
					if (isset($type_info->show_label_register)) {
						$show_label_register = $type_info->show_label_register;
					}
					if (set_value('show_label_register', $this->crm_library->htmlspecialchars_decode($show_label_register), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">The label will be displayed on the payment register instead of the time</small>
				</div>
				<?php
				if ($this->auth->has_features('session_evaluations')) {
					?><div class='form-group'><?php
						echo form_label('Session Evaluations', 'session_evaluations');
						$data = array(
							'name' => 'session_evaluations',
							'id' => 'session_evaluations',
							'value' => 1
						);
						$session_evaluations = NULL;
						if (isset($type_info->session_evaluations)) {
							$session_evaluations = $type_info->session_evaluations;
						}
						if (set_value('session_evaluations', $this->crm_library->htmlspecialchars_decode($session_evaluations), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
						<small class="text-muted form-text">Require evaluations from head coaches for each session of this type</small>
					</div><?php
				}
				if ($this->auth->has_features('online_booking')) {
					?><div class='form-group'><?php
						echo form_label('Hide from Search Dropdown on Bookings Site', 'exclude_online_booking_search');
						$data = array(
							'name' => 'exclude_online_booking_search',
							'id' => 'exclude_online_booking_search',
							'value' => 1
						);
						$exclude_online_booking_search = NULL;
						if (isset($type_info->exclude_online_booking_search)) {
							$exclude_online_booking_search = $type_info->exclude_online_booking_search;
						}
						if (set_value('exclude_online_booking_search', $this->crm_library->htmlspecialchars_decode($exclude_online_booking_search), FALSE) == 1) {
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
					<div class='form-group'><?php
						echo form_label('Exclude from price summary on event page on online booking', 'exclude_online_booking_price_summary');
						$data = array(
							'name' => 'exclude_online_booking_price_summary',
							'id' => 'exclude_online_booking_price_summary',
							'value' => 1
						);
						$exclude_online_booking_price_summary = NULL;
						if (isset($type_info->exclude_online_booking_price_summary)) {
							$exclude_online_booking_price_summary = $type_info->exclude_online_booking_price_summary;
						}
						if (set_value('exclude_online_booking_price_summary', $this->crm_library->htmlspecialchars_decode($exclude_online_booking_price_summary), FALSE) == 1) {
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
					<div class='form-group'><?php
						echo form_label('Exclude from availability status calculation on event page on online booking', 'exclude_online_booking_availability_status');
						$data = array(
							'name' => 'exclude_online_booking_availability_status',
							'id' => 'exclude_online_booking_availability_status',
							'value' => 1
						);
						$exclude_online_booking_availability_status = NULL;
						if (isset($type_info->exclude_online_booking_availability_status)) {
							$exclude_online_booking_availability_status = $type_info->exclude_online_booking_availability_status;
						}
						if (set_value('exclude_online_booking_availability_status', $this->crm_library->htmlspecialchars_decode($exclude_online_booking_availability_status), FALSE) == 1) {
							$data['checked'] = TRUE;
						}
						?><div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Yes
								<span></span>
							</label>
						</div>
					</div><?php
				}
				?>
				<div class='form-group'><?php
					echo form_label('Birthday Tab', 'birthday_tab');
					$data = array(
						'name' => 'birthday_tab',
						'id' => 'birthday_tab',
						'value' => 1
					);
					$birthday_tab = NULL;
					if (isset($type_info->birthday_tab)) {
						$birthday_tab = $type_info->birthday_tab;
					}
					if (set_value('birthday_tab', $this->crm_library->htmlspecialchars_decode($birthday_tab), FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Yes
							<span></span>
						</label>
					</div>
					<small class="text-muted form-text">When activated, a birthday tab will appear under bookings with this session type</small>							</div>
				<?php
				if ($this->auth->has_features('timesheets')) {
					?><div class='form-group'><?php
						echo form_label('Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('head') . ')');
						$extra_time_head = 0;
						if (isset($type_info->extra_time_head) && $type_info->extra_time_head >= 0) {
							$extra_time_head = $type_info->extra_time_head;
						}
						$data = array(
							'name' => 'extra_time_head',
							'id' => 'extra_time_head',
							'class' => 'form-control',
							'value' => set_value('extra_time_head', $this->crm_library->htmlspecialchars_decode($extra_time_head), FALSE),
							'maxlength' => 3,
							'min' => 0,
							'step' => 1
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<div class="input-group-append"><span class="input-group-text">Minutes</span></div>
						</div>
						<small class="text-muted form-text">Added to timesheet</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('lead') . ')');
						$extra_time_lead = 0;
						if (isset($type_info->extra_time_lead) && $type_info->extra_time_lead >= 0) {
							$extra_time_lead = $type_info->extra_time_lead;
						}
						$data = array(
							'name' => 'extra_time_lead',
							'id' => 'extra_time_lead',
							'class' => 'form-control',
							'value' => set_value('extra_time_lead', $this->crm_library->htmlspecialchars_decode($extra_time_lead), FALSE),
							'maxlength' => 3,
							'min' => 0,
							'step' => 1
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<div class="input-group-append"><span class="input-group-text">Minutes</span></div>
						</div>
						<small class="text-muted form-text">Added to timesheet</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Extra Time Per Session (' . $this->settings_library->get_staffing_type_label('assistant') . ')');
						$extra_time_assistant = 0;
						if (isset($type_info->extra_time_assistant) && $type_info->extra_time_assistant >= 0) {
							$extra_time_assistant = $type_info->extra_time_assistant;
						}
						$data = array(
							'name' => 'extra_time_assistant',
							'id' => 'extra_time_assistant',
							'class' => 'form-control',
							'value' => set_value('extra_time_assistant', $this->crm_library->htmlspecialchars_decode($extra_time_assistant), FALSE),
							'maxlength' => 3,
							'min' => 0,
							'step' => 1
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<div class="input-group-append"><span class="input-group-text">Minutes</span></div>
						</div>
						<small class="text-muted form-text">Added to timesheet</small>
					</div>
					<div class='form-group'><?php
						echo form_label('Hourly Rate Override');

						$hourly_rate = 0;
						if (isset($type_info->hourly_rate) && $type_info->hourly_rate >= 0) {
							$hourly_rate = $type_info->hourly_rate;
						}
						$data = array(
							'name' => 'hourly_rate',
							'id' => 'hourly_rate',
							'class' => 'form-control',
							'value' => set_value('hourly_rate', $this->crm_library->htmlspecialchars_decode($hourly_rate), FALSE),
							'maxlength' => 10,
							'min' => 0,
							'step' => 1
						);
						?><div class="input-group">
							<?php echo form_number($data); ?>
							<div class="input-group-append"><span class="input-group-text">Per hour</span></div>
						</div>
						<small class="text-muted form-text text-red">This will override any preset payrates</small>
					</div>
					<?php
				}
				if ($this->auth->has_features('mileage')) {
				?><div class='form-group'><?php
					echo form_label('Exclude from Mileage', 'exclude_mileage_session');
					$data = array(
						'name' => 'exclude_mileage_session',
						'id' => 'exclude_mileage_session',
						'value' => 1
					);
					$exclude_mileage_session = NULL;
					if (isset($type_info->exclude_mileage_session)) {
						$exclude_mileage_session = $type_info->exclude_mileage_session;
					}
					if (set_value('exclude_mileage_session', $this->crm_library->htmlspecialchars_decode($exclude_mileage_session), FALSE) == 1) {
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
				<?php
				}
				?>
			   </div>
		   </div><?php
	   echo form_fieldset_close();
	   ?><div class='form-actions d-flex justify-content-between'>
	   	<button class='btn btn-primary btn-submit' type="submit">
	   		<i class='far fa-save'></i> Save
	   	</button>
	   	<a href="<?php echo site_url($return_to); ?>" class="btn btn-default">Cancel</a>
	   </div>
<?php echo form_close();
