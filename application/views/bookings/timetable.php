<?php
$form_classes = 'card card-custom card-search';
if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
echo form_open($page_base . '#results', ['class' => $form_classes, 'id' => 'search-form']); ?>
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
		<input type="hidden" id="year-value" name="year" value="<? echo $year ?>">
		<input type="hidden" id="week-value" name="week" value="<? echo $week ?>">
		<input type="hidden" id="own-value" name="own" value="<? echo $only_own ?>">
		<div class='row'>
			<div class='col-sm-2 date-filter' <?php if ($view == 'standard'){ ?> style="display: none;"<?php } ?>>
				<p>
					<strong><label for="field_start_from">Date From</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_start_from',
					'id' => 'field_start_from',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_from']
				);
				echo form_input($data);
				?>
			</div>
			<div class='col-sm-2 date-filter' <?php if ($view == 'standard'){ ?> style="display: none;"<?php } ?>>
				<p>
					<strong><label for="field_start_to">Date To</label></strong>
				</p>
				<?php
				$data = array(
					'name' => 'search_start_to',
					'id' => 'field_start_to',
					'class' => 'form-control datepicker',
					'value' => $search_fields['date_to']
				);
				echo form_input($data);
				?>
			</div>
			<?php
			if ($only_own !== TRUE) {
				?><div class='col-sm-2'>
					<p>
						<strong><label for="field_staff_id">Staff</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($staff->num_rows() > 0) {
						foreach ($staff->result() as $row) {
							$options[$row->staffID] = $row->first . ' ' .$row->surname;
						}
					}
					echo form_dropdown('search_staff_id', $options, $search_fields['staff_id'], 'id="field_staff_id" class="select2 form-control"');
					?>
				</div><?php
			}
			?>
            <?php if ($view != 'map') { ?>
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
            <?php }
			if ($only_own !== TRUE) {
				?><div class='col-sm-2'>
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
                <?php if ($view != 'map') { ?>
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
                <?php if ($view != 'details') { ?>
                    <div class='col-sm-2'>
                        <p>
                            <strong><label for="field_main_contact">Main Contact</label></strong>
                        </p>
                        <?php
                        $options = array(
                            '' => 'Select'
                        );
                        if ($contacts->num_rows() > 0) {
                            foreach ($contacts->result() as $row) {
                                $options[$row->contactID] = $row->name;
                            }
                        }
                        echo form_dropdown('search_main_contact', $options, $search_fields['main_contact'], 'id="field_main_contact" class="select2 form-control"');
                        ?>
                    </div>
                <?php } } ?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_activity_id">Activity</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($activities->num_rows() > 0) {
						foreach ($activities->result() as $row) {
							$options[$row->activityID] = $row->name;
						}
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
					if ($lesson_types->num_rows() > 0) {
						foreach ($lesson_types->result() as $row) {
							$options[$row->typeID] = $row->name;
						}
					}
					$options['other'] = 'Other';
					echo form_dropdown('search_type_id', $options, $search_fields['type_id'], 'id="field_type_id" class="select2 form-control"');
					?>
				</div>
                <?php if ($view != 'map') { ?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="regionID">Region</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select'
					);
					if ($regions->num_rows() > 0) {
						foreach ($regions->result() as $row) {
							$options[$row->regionID] = $row->name;
						}
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
					if ($areas->num_rows() > 0) {
						foreach ($areas->result() as $row) {
							$options[$row->areaID] = array(
								'name' => $row->name,
								'extras' => 'data-region="' . $row->regionID . '"'
							);
						}
					}
					echo form_dropdown_advanced('search_area_id', $options, $search_fields['area_id'], 'id="areaID" class="select2 form-control"');
					?>
				</div>
                <?php }
			}
			?>
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
				if ($brands->num_rows() > 0) {
					foreach ($brands->result() as $row) {
						$options[$row->brandID] = $row->name;
					}
				}
				echo form_dropdown('search_brand_id', $options, $search_fields['brand_id'], 'id="brandID" class="select2 form-control"');
				?>
			</div>
			<?php if ($view == 'details'): ?>
				<div class='col-sm-2'>
					<p>
						<strong><label for="field_view">Status</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'FF0000' => 'Not Checked In (Red Pin)',
						'FFBF00' => 'Checked In Late (Amber Pin)',
						'008000' => 'Checked In On Time (Green Pin)',
						'0000FF' => 'Checked Out (Blue Pin)',
					);
					echo form_dropdown('checkin_status', $options, $search_fields['checkin_status'], 'id="field_checkin_status" class="select2 form-control"');
					?>
				</div>
			<?php endif; ?>
			<?php
			if ($view == 'standard' && $only_own !== TRUE && $this->auth->has_features('online_booking')) {
				?><div class='col-sm-2'>
					<p>
						<strong><label for="field_bookings_site">Bookings Site</label></strong>
					</p>
					<?php
					$options = array(
						'' => 'Select',
						'on' => 'On',
						'off' => 'Off'
					);
					echo form_dropdown('search_bookings_site', $options, $search_fields['bookings_site'], 'id="field_bookings_site" class="select2 form-control"');
					?>
				</div><?php
			}
			?>
		</div>
	</div>
	<div class='card-footer'>
		<div class="d-flex justify-content-between">
			<button class='btn btn-primary btn-submit' name="s" type="submit" value="search">
				<i class='far fa-search'></i> Search
			</button>
			<button class='btn btn-default' name="s" value="cancel">
				Cancel
			</button>
		</div>
	</div>
	<?php echo form_hidden('search', $search); ?>
<?php echo form_close(); ?>
<div id="results"></div>
<?php
switch ($view) {
	case 'map':
		$this->load->view('bookings/timetable_map.php', $data);
		break;
	case 'details':
		$this->load->view('bookings/timetable_details.php', $data);
		break;
	default:
		$this->load->view('bookings/timetable_standard.php', $data);
		break;
}
