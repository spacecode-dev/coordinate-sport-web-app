<?php
/**
 * @var $payroll_data CI_DB_result
 * @var $staff CI_DB_result
 * @var $quals array
 * @var $staff_list CI_DB_result
 * @var $payroll_data array
 * @var $search_fields array
 * @var $page_base string
 */

display_messages();
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'method' => 'get', 'id' => 'session-delivery-report-search']); ?>
	<div class="card-header">
		<div class="card-title">
			<h3 class="card-label">Search</h3>
		</div>
		<div class="card-toolbar">
			<a href="#" class="btn btn-icon btn-sm btn-hover-light-primary mr-1" data-card-tool="toggle" data-toggle="tooltip" data-placement="top" title="" data-original-title="Toggle Card">
				<i class="ki ki-arrow-down icon-nm"></i>
			</a>
		</div>
	</div>
	<div class="card-body">
		<div class='row'>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_from',
					'id' => 'field_date_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_date_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'date_to',
					'id' => 'field_date_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_staff_id">Staff</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($staff as $row) {
					$options[$row->staffID] = $row->first . ' ' .$row->surname;
				}
				echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_org"><?php echo $this->settings_library->get_label('customer'); ?></label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_org',
					'id' => 'field_org',
					'class' => 'form-control',
					'value' => $search_fields['org']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_class_size">Class Size</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_class_size',
					'id' => 'field_class_size',
					'class' => 'form-control',
					'value' => $search_fields['class_size'],
					'type' => 'number'
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_name">Event/Project</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_name',
					'id' => 'field_name',
					'class' => 'form-control',
					'value' => $search_fields['name']
				);
				echo form_input($data);
				?>
			</div>
 			<div class='col-sm-2'>
				<p>
					<strong><label for="field_postcode">Post Code</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_postcode',
					'id' => 'field_postcode',
					'class' => 'form-control',
					'value' => $search_fields['postcode']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_main_contact">Main Contact</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($contacts as $row) {
					$options[$row->contactID] = $row->name;
				}
				echo form_dropdown('search_main_contact', $options, $search_fields['main_contact'], 'id="field_main_contact" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_activity_id">Activity</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($activities as $row) {
					$options[$row->activityID] = $row->name;
				}
				$options['other'] = 'Other';
				echo form_dropdown('search_activity_id', $options, $search_fields['activity_id'], 'id="field_activity_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_type_id">Session Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select',
				);
				foreach ($lesson_types as $row) {
					$options[$row->typeID] = $row->name;
				}
				$options['other'] = 'Other';
				echo form_dropdown('search_type_id', $options, $search_fields['type_id'], 'id="field_type_id" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="regionID">Region</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($regions as $row) {
					$options[$row->regionID] = $row->name;
				}
				echo form_dropdown('search_region_id', $options, $search_fields['region_id'], 'id="regionID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="areaID">Area</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($areas as $row) {
					$options[$row->areaID] = array(
						'name' => $row->name,
						'extras' => 'data-region="' . $row->regionID . '"'
					);
				}
				echo form_dropdown_advanced('search_area_id', $options, $search_fields['area_id'], 'id="areaID" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_day">Day</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				if (count($days) > 0) {
					foreach ($days as $day) {
						$options[$day] = ucwords($day);
					}
				}
				echo form_dropdown('search_day', $options, $search_fields['day'], 'id="field_day" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="field_staffing_type">Staffing Type</label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($this->settings_library->staffing_types_defaults as $key => $label) {
					$options[$key] = $this->settings_library->get_staffing_type_label($key);
				}
				echo form_dropdown('search_staffing_type', $options, $search_fields['staffing_type'], 'id="field_staffing_type" class="select2 form-control"');
				?>
			</div>
			<div class='col-sm-2'>
				<p>
					<strong><label for="brandID"><?php echo $this->settings_library->get_label('brand'); ?></label></strong>
				</p>
				<?php
				$options = array(
					'' => 'Select'
				);
				foreach ($brands as $row) {
					$options[$row->brandID] = $row->name;
				}
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="brandID" class="select2 form-control"');
				?>
			</div>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' type="submit">
				<i class='far fa-search'></i> Search
			</button>
			<a class='btn btn-default' href="<?php echo site_url($page_base); ?>">
				Cancel
			</a>
		</div>
	</div>
	<?php echo form_hidden('search', 'true'); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
if (count($lessons) == 0) {
	?>
	<div class="alert alert-info">
		No data found.
	</div>
	<?php
} else {
	echo $this->pagination_library->display($page_base);
	$this->load->view('reports/session-delivery-table.php');
}
