<?php
display_messages();
echo form_open_multipart($submit_to);
	echo form_fieldset('', ['class' => 'card card-custom']);
		?><div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-folder-open text-contrast'></i></span>
				<h3 class="card-label">Plan Information</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Name <em>*</em>', 'name');
					$name = NULL;
					if (isset($plan_info->name)) {
						$name = $plan_info->name;
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
				<div class='form-group'><?php
					echo form_label('Default Project Types <em>*</em>', 'default_project_types');
					$default_project_types = NULL;
					if (isset($plan_info->default_project_types)) {
						$default_project_types = $plan_info->default_project_types;
					}
					$data = array(
						'name' => 'default_project_types',
						'id' => 'default_project_types',
						'class' => 'form-control',
						'value' => set_value('default_project_types', $this->crm_library->htmlspecialchars_decode($default_project_types), FALSE),
						'maxlength' => 200
					);
					echo form_textarea($data);
					?><small class="form-text text-muted">One per line</small>
				</div>
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-cog text-contrast'></i></span>
				<h3 class="card-label">Features</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Bookings - Timetable', 'bookings_timetable');
					$data = array(
						'name' => 'bookings_timetable',
						'id' => 'bookings_timetable',
						'value' => 1
					);
					$bookings_timetable = NULL;
					if (isset($plan_info->bookings_timetable)) {
						$bookings_timetable = $plan_info->bookings_timetable;
					}
					if (set_value('bookings_timetable', $this->crm_library->htmlspecialchars_decode($bookings_timetable), FALSE) == 1) {
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
					echo form_label('Bookings - Your Timetable', 'bookings_timetable_own');
					$data = array(
						'name' => 'bookings_timetable_own',
						'id' => 'bookings_timetable_own',
						'value' => 1
					);
					$bookings_timetable_own = NULL;
					if (isset($plan_info->bookings_timetable_own)) {
						$bookings_timetable_own = $plan_info->bookings_timetable_own;
					}
					if (set_value('bookings_timetable_own', $this->crm_library->htmlspecialchars_decode($bookings_timetable_own), FALSE) == 1) {
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
					echo form_label('Bookings - Contracts', 'bookings_bookings');
					$data = array(
						'name' => 'bookings_bookings',
						'id' => 'bookings_bookings',
						'value' => 1
					);
					$bookings_bookings = NULL;
					if (isset($plan_info->bookings_bookings)) {
						$bookings_bookings = $plan_info->bookings_bookings;
					}
					if (set_value('bookings_bookings', $this->crm_library->htmlspecialchars_decode($bookings_bookings), FALSE) == 1) {
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
					echo form_label('Bookings - Projects', 'bookings_projects');
					$data = array(
						'name' => 'bookings_projects',
						'id' => 'bookings_projects',
						'value' => 1
					);
					$bookings_projects = NULL;
					if (isset($plan_info->bookings_projects)) {
						$bookings_projects = $plan_info->bookings_projects;
					}
					if (set_value('bookings_projects', $this->crm_library->htmlspecialchars_decode($bookings_projects), FALSE) == 1) {
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
					echo form_label('Bookings - Exceptions', 'bookings_exceptions');
					$data = array(
						'name' => 'bookings_exceptions',
						'id' => 'bookings_exceptions',
						'value' => 1
					);
					$bookings_exceptions = NULL;
					if (isset($plan_info->bookings_exceptions)) {
						$bookings_exceptions = $plan_info->bookings_exceptions;
					}
					if (set_value('bookings_exceptions', $this->crm_library->htmlspecialchars_decode($bookings_exceptions), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('customers', TRUE) . ' - Schools', 'customers_schools');
					$data = array(
						'name' => 'customers_schools',
						'id' => 'customers_schools',
						'value' => 1
					);
					$customers_schools = NULL;
					if (isset($plan_info->customers_schools)) {
						$customers_schools = $plan_info->customers_schools;
					}
					if (set_value('customers_schools', $this->crm_library->htmlspecialchars_decode($customers_schools), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('customers', TRUE) . ' - Prospective Schools', 'customers_schools_prospects');
					$data = array(
						'name' => 'customers_schools_prospects',
						'id' => 'customers_schools_prospects',
						'value' => 1
					);
					$customers_schools_prospects = NULL;
					if (isset($plan_info->customers_schools_prospects)) {
						$customers_schools_prospects = $plan_info->customers_schools_prospects;
					}
					if (set_value('customers_schools_prospects', $this->crm_library->htmlspecialchars_decode($customers_schools_prospects), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('customers', TRUE) . ' - Organisations', 'customers_orgs');
					$data = array(
						'name' => 'customers_orgs',
						'id' => 'customers_orgs',
						'value' => 1
					);
					$customers_orgs = NULL;
					if (isset($plan_info->customers_orgs)) {
						$customers_orgs = $plan_info->customers_orgs;
					}
					if (set_value('customers_orgs', $this->crm_library->htmlspecialchars_decode($customers_orgs), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('customers', TRUE) . ' - Prospective Organisations', 'customers_orgs_prospects');
					$data = array(
						'name' => 'customers_orgs_prospects',
						'id' => 'customers_orgs_prospects',
						'value' => 1
					);
					$customers_orgs_prospects = NULL;
					if (isset($plan_info->customers_orgs_prospects)) {
						$customers_orgs_prospects = $plan_info->customers_orgs_prospects;
					}
					if (set_value('customers_orgs_prospects', $this->crm_library->htmlspecialchars_decode($customers_orgs_prospects), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('participants', TRUE), 'participants');
					$data = array(
						'name' => 'participants',
						'id' => 'participants',
						'value' => 1
					);
					$participants = NULL;
					if (isset($plan_info->participants)) {
						$participants = $plan_info->participants;
					}
					if (set_value('participants', $this->crm_library->htmlspecialchars_decode($participants), FALSE) == 1) {
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
					echo form_label('Staff Management', 'staff_management');
					$data = array(
						'name' => 'staff_management',
						'id' => 'staff_management',
						'value' => 1
					);
					$staff_management = NULL;
					if (isset($plan_info->staff_management)) {
						$staff_management = $plan_info->staff_management;
					}
					if (set_value('staff_management', $this->crm_library->htmlspecialchars_decode($staff_management), FALSE) == 1) {
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
					echo form_label('Settings', 'settings');
					$data = array(
						'name' => 'settings',
						'id' => 'settings',
						'value' => 1
					);
					$settings = NULL;
					if (isset($plan_info->settings)) {
						$settings = $plan_info->settings;
					}
					if (set_value('settings', $this->crm_library->htmlspecialchars_decode($settings), FALSE) == 1) {
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
					echo form_label('All Addons', 'addons_all');
					$data = array(
						'name' => 'addons_all',
						'id' => 'addons_all',
						'value' => 1
					);
					$addons_all = NULL;
					if (isset($plan_info->addons_all)) {
						$addons_all = $plan_info->addons_all;
					}
					if (set_value('addons_all', $this->crm_library->htmlspecialchars_decode($addons_all), FALSE) == 1) {
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
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']);	?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-dashboard text-contrast'></i></span>
				<h3 class="card-label">Dashboard Widgets</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label('Bookings', 'dashboard_bookings');
					$data = array(
						'name' => 'dashboard_bookings',
						'id' => 'dashboard_bookings',
						'value' => 1
					);
					$dashboard_bookings = 1;
					if (isset($plan_info->dashboard_bookings)) {
						$dashboard_bookings = $plan_info->dashboard_bookings;
					}
					if (set_value('dashboard_bookings', $this->crm_library->htmlspecialchars_decode($dashboard_bookings), FALSE) == 1) {
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
					echo form_label('Staff', 'dashboard_staff');
					$data = array(
						'name' => 'dashboard_staff',
						'id' => 'dashboard_staff',
						'value' => 1
					);
					$dashboard_staff = 1;
					if (isset($plan_info->dashboard_staff)) {
						$dashboard_staff = $plan_info->dashboard_staff;
					}
					if (set_value('dashboard_staff', $this->crm_library->htmlspecialchars_decode($dashboard_staff), FALSE) == 1) {
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
					echo form_label($this->settings_library->get_label('participants', TRUE), 'dashboard_participants');
					$data = array(
						'name' => 'dashboard_participants',
						'id' => 'dashboard_participants',
						'value' => 1
					);
					$dashboard_participants = 1;
					if (isset($plan_info->dashboard_participants)) {
						$dashboard_participants = $plan_info->dashboard_participants;
					}
					if (set_value('dashboard_participants', $this->crm_library->htmlspecialchars_decode($dashboard_participants), FALSE) == 1) {
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
					echo form_label('Health & Safety', 'dashboard_health_safety');
					$data = array(
						'name' => 'dashboard_health_safety',
						'id' => 'dashboard_health_safety',
						'value' => 1
					);
					$dashboard_health_safety = 1;
					if (isset($plan_info->dashboard_health_safety)) {
						$dashboard_health_safety = $plan_info->dashboard_health_safety;
					}
					if (set_value('dashboard_health_safety', $this->crm_library->htmlspecialchars_decode($dashboard_health_safety), FALSE) == 1) {
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
					echo form_label('Equipment', 'dashboard_equipment');
					$data = array(
						'name' => 'dashboard_equipment',
						'id' => 'dashboard_equipment',
						'value' => 1
					);
					$dashboard_equipment = 1;
					if (isset($plan_info->dashboard_equipment)) {
						$dashboard_equipment = $plan_info->dashboard_equipment;
					}
					if (set_value('dashboard_equipment', $this->crm_library->htmlspecialchars_decode($dashboard_equipment), FALSE) == 1) {
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
					echo form_label('Availability', 'dashboard_availability');
					$data = array(
						'name' => 'dashboard_availability',
						'id' => 'dashboard_availability',
						'value' => 1
					);
					$dashboard_availability = 1;
					if (isset($plan_info->dashboard_availability)) {
						$dashboard_availability = $plan_info->dashboard_availability;
					}
					if (set_value('dashboard_availability', $this->crm_library->htmlspecialchars_decode($dashboard_availability), FALSE) == 1) {
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
					echo form_label('Employee of the Month', 'dashboard_employee_of_month');
					$data = array(
						'name' => 'dashboard_employee_of_month',
						'id' => 'dashboard_employee_of_month',
						'value' => 1
					);
					$dashboard_employee_of_month = 1;
					if (isset($plan_info->dashboard_employee_of_month)) {
						$dashboard_employee_of_month = $plan_info->dashboard_employee_of_month;
					}
					if (set_value('dashboard_employee_of_month', $this->crm_library->htmlspecialchars_decode($dashboard_employee_of_month), FALSE) == 1) {
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
					echo form_label('Staff Birthdays', 'dashboard_staff_birthdays');
					$data = array(
						'name' => 'dashboard_staff_birthdays',
						'id' => 'dashboard_staff_birthdays',
						'value' => 1
					);
					$dashboard_staff_birthdays = 1;
					if (isset($plan_info->dashboard_staff_birthdays)) {
						$dashboard_staff_birthdays = $plan_info->dashboard_staff_birthdays;
					}
					if (set_value('dashboard_staff_birthdays', $this->crm_library->htmlspecialchars_decode($dashboard_staff_birthdays), FALSE) == 1) {
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
			</div>
		</div>
	<?php echo form_fieldset_close(); ?>
	<?php echo form_fieldset('', ['class' => 'card card-custom']); ?>
		<div class='card-header'>
			<div class="card-title">
				<span class="card-icon"><i class='far fa-tag text-contrast'></i></span>
				<h3 class="card-label">Label Overrides</h3>
			</div>
		</div>
		<div class="card-body">
			<div class='multi-columns'>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('brand', TRUE) . ' (Singular)', 'label_brand');
					$label_brand = NULL;
					if (isset($plan_info->label_brand)) {
						$label_brand = $plan_info->label_brand;
					}
					$data = array(
						'name' => 'label_brand',
						'id' => 'label_brand',
						'class' => 'form-control',
						'value' => set_value('label_brand', $this->crm_library->htmlspecialchars_decode($label_brand), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('brands', TRUE) . ' (Plural)', 'label_brands');
					$label_brands = NULL;
					if (isset($plan_info->label_brands)) {
						$label_brands = $plan_info->label_brands;
					}
					$data = array(
						'name' => 'label_brands',
						'id' => 'label_brands',
						'class' => 'form-control',
						'value' => set_value('label_brands', $this->crm_library->htmlspecialchars_decode($label_brands), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('customer', TRUE) . ' (Singular)', 'label_customer');
					$label_customer = NULL;
					if (isset($plan_info->label_customer)) {
						$label_customer = $plan_info->label_customer;
					}
					$data = array(
						'name' => 'label_customer',
						'id' => 'label_customer',
						'class' => 'form-control',
						'value' => set_value('label_customer', $this->crm_library->htmlspecialchars_decode($label_customer), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('customers', TRUE) . ' (Plural)', 'label_customers');
					$label_customers = NULL;
					if (isset($plan_info->label_customers)) {
						$label_customers = $plan_info->label_customers;
					}
					$data = array(
						'name' => 'label_customers',
						'id' => 'label_customers',
						'class' => 'form-control',
						'value' => set_value('label_customers', $this->crm_library->htmlspecialchars_decode($label_customers), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('participant', TRUE) . ' (Singular)', 'label_participant');
					$label_participant = NULL;
					if (isset($plan_info->label_participant)) {
						$label_participant = $plan_info->label_participant;
					}
					$data = array(
						'name' => 'label_participant',
						'id' => 'label_participant',
						'class' => 'form-control',
						'value' => set_value('label_participant', $this->crm_library->htmlspecialchars_decode($label_participant), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
				?></div>
				<div class='form-group'><?php
					echo form_label($this->settings_library->get_label('participants', TRUE) . ' (Plural)', 'label_participants');
					$label_participants = NULL;
					if (isset($plan_info->label_participants)) {
						$label_participants = $plan_info->label_participants;
					}
					$data = array(
						'name' => 'label_participants',
						'id' => 'label_participants',
						'class' => 'form-control',
						'value' => set_value('label_participants', $this->crm_library->htmlspecialchars_decode($label_participants), FALSE),
						'maxlength' => 20
					);
					echo form_input($data);
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
