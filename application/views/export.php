<?php
$data = array(
	'tab' => $tab,
);
$this->load->view('tabs_export.php', $data);

display_messages();
echo form_open_multipart($submit_to, 'class="export"');
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
					echo form_label('Export To <em>*</em>', 'export');
					$options = array(
						'' => 'Select',
						'newsletter' => 'Newsletter',
						'sms' => 'SMS Software'
					);
					echo form_dropdown('export', $options, set_value('export', NULL, FALSE), 'id="export" class="form-control select2"');
				?></div>
				<div class='form-group'><?php
					echo form_label('Type <em>*</em>', 'type');
					$options = array(
						'' => 'Select',
						'customers' => $this->settings_library->get_label('customers'),
						'participants' => $this->settings_library->get_label('participants')
					);
					echo form_dropdown('type', $options, set_value('type', NULL, FALSE), 'id="type" class="form-control select2"');
				?></div>
				
				<div class='form-group options'><?php
					echo form_label('Options');
					$data = array(
						'name' => 'schools',
						'id' => 'schools',
						'value' => 1
					);
					if ($this->input->post() && set_value('schools') == 1) {
						$data['checked'] = TRUE;
					} else if (!$this->input->post()) {
						$data['checked'] = TRUE;
					}
					?><div class="checkbox-single">
						<label class="checkbox">
							<?php echo form_checkbox($data); ?>
							Schools
							<span></span>
						</label>
					</div>
					<?php
					$data = array(
						'name' => 'organisations',
						'id' => 'organisations',
						'value' => 1
					);
					if ($this->input->post() && set_value('organisations') == 1) {
						$data['checked'] = TRUE;
					} else if (!$this->input->post()) {
						$data['checked'] = TRUE;
					}
					?><div class="customers">
						<div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Organisations
								<span></span>
							</label>
						</div>
					</div>
					<?php
					$data = array(
						'name' => 'customers',
						'id' => 'customers',
						'value' => 1
					);
					if ($this->input->post() && set_value('customers') == 1) {
						$data['checked'] = TRUE;
					} else if (!$this->input->post()) {
						$data['checked'] = TRUE;
					}
					?><div class="customers">
						<div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Active <?php echo $this->settings_library->get_label('customers', TRUE); ?>
								<span></span>
							</label>
						</div>
					</div>
					<?php
					$data = array(
						'name' => 'prospects',
						'id' => 'prospects',
						'value' => 1
					);
					if ($this->input->post() && set_value('prospects') == 1) {
						$data['checked'] = TRUE;
					} else if (!$this->input->post()) {
						$data['checked'] = TRUE;
					}
					?><div class="customers">
						<div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Prospects
								<span></span>
							</label>
						</div>
					</div>
					<?php
					$data = array(
						'name' => 'main_contact_only',
						'id' => 'main_contact_only',
						'value' => 1
					);
					if (set_value('main_contact_only', NULL, FALSE) == 1) {
						$data['checked'] = TRUE;
					}
					?><div class="customers families">
						<div class="checkbox-single">
							<label class="checkbox">
								<?php echo form_checkbox($data); ?>
								Main Contact Only
								<span></span>
							</label>
						</div>
					</div>
				</div>
				<div class='form-group families'><?php
					echo form_label('Bookings From', 'bookings_from');
					$data = array(
						'name' => 'bookings_from',
						'id' => 'bookings_from',
						'class' => 'form-control datepicker',
						'value' => set_value('bookings_from', NULL, FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group families'><?php
					echo form_label('Bookings To', 'bookings_to');
					$data = array(
						'name' => 'bookings_to',
						'id' => 'bookings_to',
						'class' => 'form-control datepicker',
						'value' => set_value('bookings_to', NULL, FALSE),
						'maxlength' => 10
					);
					echo form_input($data);
				?></div>
				<div class='form-group hide_option'><?php
					echo form_label('Filter', 'filter');
					$options = array(
						'Ethnicity' => "Ethnicity",
						'Gender' => "Gender",
						'Age' => "Age",
						'Disability' => "Disability"
					);
					echo form_dropdown('filter[]', $options, set_value('filter', NULL, FALSE), 'id="filter" class="form-control select2" multiple');
				?></div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<div class='form-actions d-flex justify-content-between'>
		<button class='btn btn-primary btn-submit' type="submit">
			<i class='far fa-save'></i> Export
		</button>
	</div>
<?php echo form_close();
