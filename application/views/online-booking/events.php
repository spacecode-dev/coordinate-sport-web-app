<h1 class="h3 semibold">Event Search</h1>
<?php
display_messages('fas');
echo form_open($search_submit_url, array(
	'class' => 'row events-search'
));
	$some_search_fields = FALSE;
	if ($this->settings_library->get('onlinebooking_search_location', $this->online_booking->accountID) == 1) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
		  	<div class="form-group">
				<?php
				echo form_label('Location', 'location');
				$data = array(
					'name' => 'location',
					'id' => 'location',
					'class' => 'form-control',
					'value' => $search_fields['location'],
					'placeholder' => 'Location'
				);
				echo form_input($data);
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($this->settings_library->get('onlinebooking_search_age', $this->online_booking->accountID) == 1) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
			<div class="form-group">
				<?php
				echo form_label('Participant\'s Age', 'age');
				$data = array(
					'name' => 'age',
					'id' => 'age',
					'class' => 'form-control',
					'value' => $search_fields['age'],
					'step' => 1,
					'min' => 1
				);
				echo form_number($data);
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($this->settings_library->get('onlinebooking_search_activity', $this->online_booking->accountID) == 1 && $activities->num_rows() > 0) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
			<div class="form-group">
				<?php
				echo form_label('Activity', 'activityID');
				$options = array(
					'' => 'All'
				);
				foreach ($activities->result() as $row) {
					$options[$row->activityID] = $row->name;
				}
				echo form_dropdown('activityID', $options, $search_fields['activityID'], 'id="activityID" class="form-control select2"');
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($this->settings_library->get('onlinebooking_search_type', $this->online_booking->accountID) == 1 && $lesson_types->num_rows() > 0) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
			<div class="form-group">
				<?php
				echo form_label('Type', 'typeID');
				$options = array(
					'' => 'All'
				);
				foreach ($lesson_types->result() as $row) {
					$options[$row->typeID] = $row->name;
				}
				echo form_dropdown('typeID', $options, $search_fields['typeID'], 'id="typeID" class="form-control select2"');
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($this->settings_library->get('onlinebooking_search_brand', $this->online_booking->accountID) == 1 && $brands->num_rows() > 0) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
			<div class="form-group">
				<?php
				echo form_label($this->settings_library->get_label('brand', $this->online_booking->accountID), 'brandID');
				$options = array(
					'' => 'All'
				);
				foreach ($brands->result() as $row) {
					$options[$row->brandID] = $row->name;
				}
				echo form_dropdown('brandID', $options, $search_fields['brandID'], 'id="brandID" class="form-control select2"');
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($this->settings_library->get('onlinebooking_search_name', $this->online_booking->accountID) == 1) {
		?><div class="col-xs-12 col-sm-6 col-md-fifth">
		  	<div class="form-group">
				<?php
				echo form_label('Event Name', 'name');
				$data = array(
					'name' => 'name',
					'id' => 'name',
					'class' => 'form-control',
					'value' => html_entity_decode($search_fields['name'], ENT_QUOTES, 'UTF-8'),
					'placeholder' => 'Event Name'
				);
				echo form_input($data);
				?>
			</div>
		</div><?php
		$some_search_fields = TRUE;
	}
	if ($some_search_fields) {
		?><div class="col-xs-12 col-md-fifth">
			<div class="form-group">
				<label class="hidden-xs hidden-sm">&nbsp;</label>
				<button type="submit" class="btn btn-block">Search <i class="fas fa-search"></i></button>
			</div>
		</div><?php
	}
echo form_close(); ?>
<div class="row events-nav">
	<div class="col-xs-12 col-md-4">
		<a href="<?php echo site_url('list'); ?>" class="btn btn-block">List</a>
	</div>
	<div class="col-xs-12 col-md-4">
		<a href="<?php echo site_url('calendar'); ?>" class="btn btn-block">Calendar</a>
	</div>
	<div class="col-xs-12 col-md-4">
		<a href="<?php echo site_url('map'); ?>" class="btn btn-block">Map</a>
	</div>
</div>
<?php
switch ($view_type) {
	case 'map':
		?><div class="row events-map">
			<div class="col-xs-12">
				<?php
				// get markers
				$markers = array();

				// loop events
				foreach ($blocks as $blockID => $block) {
					if (is_array($block->coordinates)) {
						$markers[] = array(
							'label' => $block->booking,
							'link' => site_url('event/' . $block->blockID),
							'color' => $block->colour,
							'lat' => $block->coordinates[0],
							'lng' => $block->coordinates[1]
						);
					}
				}

				// show user search
				if (isset($search_fields['location_coordinates']) && $search_fields['location_coordinates'] != '') {
					$markers[] = array(
						'label' => 'Search Location',
						'link' => NULL,
						'color' => 'location',
						'lat' => $search_fields['location_coordinates']['lat'],
						'lng' => $search_fields['location_coordinates']['lng']
					);
				}
				?>
				<script>
					var map_markers = <?php echo json_encode($markers); ?>;
				</script>
				<div id="map"></div>
			</div>
		</div><?php
		break;
	case 'calendar':
		?><div class="row calendar">
			<div class="col-xs-12">
				<div class="row month-nav">
					<div class="col-xs-2 col-sm-1 text-center"><?php if (!empty($prev_month) && !empty($prev_year)) {
						?><a href="<?php echo site_url('calendar/' . $prev_year . '/' . $prev_month); ?>" class="prev"><i class="fas fa-chevron-left"></i></a><?php
					} ?></div>
					<div class="col-xs-8 col-sm-10 text-center month"><h2 class="h4 semibold"><?php echo $month_year; ?></h2></div>
					<div class="col-xs-2 col-sm-1 text-center"><?php if (!empty($next_month) && !empty($next_year)) {
						?><a href="<?php echo site_url('calendar/' . $next_year . '/' . $next_month); ?>" class="next"><i class="fas fa-chevron-right"></i></a><?php
					} ?></div>
				</div>
				<?php
				$calendar = new donatj\SimpleCalendar($month_year);
				$calendar->setStartOfWeek('Monday');
				foreach ($blocks as $blockID => $block) {
					if (count($block->dates) > 0) {
						foreach ($block->dates as $date => $lessons) {
							$calendar->addDailyHtml('<a href="' . site_url('event/' . $blockID) . '" title="' . $block->block . '" data-block="' . $blockID . '">' . $block->booking . '</a>', $date);
						}
					}
				}
				$calendar->show();
				?>
			</div>
		</div><?php
}
?>
<div class="row events">
	<div class="col-xs-12">
		<?php
		if (count($blocks) == 0) {
			?><div class="alert alert-info" role="alert">No events found.</div><?php
		} else {
			foreach ($blocks as $blockID => $block) {
				?><div class="event" id="event-<?php echo $blockID; ?>">
					<div class="row flex sm-nowrap">
						<?php
						if (count($block->images) > 0) {
							?><div class="col-xs-12 col-sm-3 order-1 order-sm-2 img" <?php if (!empty($block->colour)) {
								echo ' style="background:rgba(' . implode(',', hex_to_rgb($block->colour)) . ', .2);"';
								} ?>>
								<?php
								$data = array(
									'src' => $block->images[0]['thumb'],
									'alt' => $block->booking
								);
								$img = img($data);
								echo anchor('event/' . $block->blockID, $img);
								?>
							</div><?php
						}
						?>
						<div class="col-xs-12 info <?php if (count($block->images) > 0) { echo ' col-sm-9 order-2 order-sm-1'; } ?>"<?php if (!empty($block->colour)) {
							echo ' style="border-left-color: ' . $block->colour . '"';
							} ?>>
							<h2><a href="<?php echo site_url('event/' . $block->blockID); ?>"><?php echo $block->booking; ?></a></h2>
							<p class="event-specs">
								<?php
								if (!empty($block->location)) {
									?><span><i class="fas fa-map-marker-alt"></i> <?php echo $block->location; ?></span> <?php
								}
								?>
								<span><i class="fas fa-arrow-circle-right"></i> <?php echo $block->block; ?></span>
								<span><i class="fas fa-calendar-alt"></i> <?php echo mysql_to_uk_date($block->startDate); ?> to <?php echo mysql_to_uk_date($block->endDate); ?></span>
							</p>
							<?php
							if (!empty($block->website_description)) {
								echo "<p>" . substr($block->website_description, 0, strpos($block->website_description, "\n")) . "</p>";
							}
							if (!empty($block->availability_status)) {
								?><p class="availability <?php echo $block->availability_status_class; ?>">
									<i class="fas fa-circle"></i> Availability - <?php echo $block->availability_status; ?>
								</p><?php
							}
							?>
							<p><a href="<?php echo site_url('event/' . $block->blockID); ?>" class="btn btn-hollow btn-block">More Information</a></p>
						</div>
					</div>
				</div><?php
			}
			echo $this->pagination_library->display($page_base);

		}
		?>
	</div>
</div>
