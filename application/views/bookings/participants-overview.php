<?php
if ($print_view === 2 || $print_view === 3) {
	?><!DOCTYPE HTML>
	<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta content='width=device-width, initial-scale=1' name='viewport'>
		<link rel="stylesheet" href="<?php echo $this->crm_library->asset_url('dist/css/components/print.css'); ?>" />
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css " />
	</head>
	<body>
	<?php if (in_array($booking_info->register_type, array('children', 'individuals', 'individuals_bikeability', 'children_bikeability')) && $print_view == 2) { echo "<h3>".$booking_info->name." - ".$block_info->name."</h3>"; } ?>
	<?php if($print_view == 2){ ?>
	<div class="intro">
			<h1><?php echo $title; ?></h1>
			<p>Overview</p>
			<p class="noprint"><a href="#" class="print">Print</a></p>
		</div><?php
	}
} else {
	display_messages();
	if ($bookingID != NULL) {
		$data = array(
			'bookingID' => $bookingID,
			'tab' => $tab,
			'type' => $type,
			'is_project' => $booking_info->project,
			'type' => $booking_info->type
		);
		$this->load->view('bookings/tabs.php', $data);
	}
}
if($print_view !== 3){
	if ($print_view !== 2) {
		$form_classes = 'card card-custom card-search';
		if ($search_fields['search'] == '') { $form_classes .= " card-collapsed"; }
		echo form_open($page_base . '#results', ['class' => $form_classes]); ?>
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
							<strong><label for="field_contact">Contact</label></strong>
						</p>
						<?php
						$data = array(
							'name' => 'search_contact',
							'id' => 'field_contact',
							'class' => 'form-control',
							'value' => $search_fields['contact']
						);
						echo form_input($data);
						?>
					</div>
					<?php
					if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
						?><div class='col-sm-2'>
							<p>
								<strong><label for="field_child">Child</label></strong>
							</p>
							<?php
							$data = array(
								'name' => 'search_child',
								'id' => 'field_child',
								'class' => 'form-control',
								'value' => $search_fields['child']
							);
							echo form_input($data);
							?>
						</div><?php
					}
					?>
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
		<div id="results"></div><?php
	}

	// tabs
	$data = array(
		'print_view' => $print_view,
		'lessonID' => $lessonID,
		'page_base' => $page_base,
		'tabs' => $tabs,
	);
	$this->load->view('bookings/participants-print-tabs');

	if (count($items) == 0 && $print_view !== 2) {
		?>
		<div class="alert alert-info">
			No participants found.<?php if ($print_view !== 2) { ?> Do you want to <?php echo anchor('booking/book/' . $blockID, 'create one'); ?>?<?php } ?>
		</div>
		<?php
	} else {
		?><div class='card card-custom'>
			<div class='responsive-table fixed-<?php
		switch ($booking_info->register_type) {
			case 'children':
			case 'children_shapeup':
				echo '2';
				break;
			case 'children_bikeability':
				echo '3 with-checkboxes';
				break;
			case 'individuals':
			case 'individuals_shapeup':
				echo '1';
				break;
			case 'individuals_bikeability':
				echo '2 with-checkboxes';
				break;

		} ?>'>

		<?php if($print_view !== 1){?>
			<div class="visible-xs">
				<div class="col-md-3">
					<label> Date: </label>
					<select class="form-control" name="filterdate_overview" id="filterdate_overview">
						<?php
						$firstdate = '';
						$firstdate1 = '';
						if ($booking_info->type == 'booking') {
							$headings = array();
							 $date = $block_info->startDate;
							$x =0;
							while (strtotime($date) <= strtotime($block_info->endDate)) {
								$day = strtolower(date("l", strtotime($date)));
								if (in_array($day, $booking_info->days) && array_key_exists($date, $lesson_ids)) {
									if($date != null){
										echo "<option value='".str_replace("/","",mysql_to_uk_date($date))."'>".mysql_to_uk_date($date)."</option>";
										if($x == 0) { $x++; $firstdate = str_replace("/","",mysql_to_uk_date($date)); $firstdate1 = mysql_to_uk_date($date); }
									}
								}
								$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));

							}

						} else {

							$x =0;
							foreach ($days as $day) {
								if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
									echo '<option value="'.ucwords($day).'">'.ucwords($day).'</option>';
									if($x == 0) { $x++;
										$firstdate = ucwords($day);
									}
								}
							}
						}
						?>
					</select>
				</div>
				<br />
			</div>
		<?php } ?>

		<?php if($print_view !== 1){
			if ($booking_info->type == 'booking') {
				foreach($lesson_ids as $date=>$lessons){
					$n = str_replace("/","", mysql_to_uk_date($date));
					$val = implode(",",$lessons);
					echo "<input type='hidden' name='".$n."' id='".$n."' value='".$val."' />";
				}
			}else{
				foreach($lesson_ids as $date=>$lessons){
					$n = $date;
					$val = implode(",",$lessons);
					echo "<input type='hidden' name='".ucwords($n)."' id='".ucwords($n)."' value='".$val."' />";
				}
			}
		?>

		<div class="visible-xs">
			<div class="col-md-3">
				<label> Time: </label>
				<select class="form-control" name="filtertime" id="filtertime">
				<?php
					$firsttime = '';
					$timeArray = array();
					$x = 0;
					if ($booking_info->type == 'booking') {
						foreach ($lesson_ids as $date=>$lessons) {
							foreach ($lessons as $lessonID=>$name) {
								// if cancelled, skip
								if (isset($cancellations[$lessonID][$date])) {

								} else {
									if(!in_array($name, $timeArray)){
										if($firstdate1 == mysql_to_uk_date($date)){
											echo "<option value='".str_replace(":","",$name)."'>".$name."</option>";
											$timeArray[] = $name;
											if($x == 0) { $x++; $firsttime = str_replace(":","",$name); }
										}
									}
								}

							}
						}
					} else {
						foreach ($lesson_ids as $day=>$lessons) {
							foreach ($lessons as $lessonID=>$name) {
								if ($booking_info->type == 'booking') {
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										if (strtolower(date("l", strtotime($date))) == $day) {
											// if cancelled, skip
											if (isset($cancellations[$lessonID][$date])) {

											} else {
												if (in_array($day, $days)) {
													if($firstdate1 == mysql_to_uk_date($date)){
														if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
															if(!in_array($name, $timeArray)){
																echo "<option value='".str_replace(":","",$name)."'>".$name."</option>";
																$timeArray[] = $name;
																if($x == 0) { $x++; $firsttime = str_replace(":","",$name); }
															}
														}
													}
												}

												foreach ($days as $day) {
													if($firstdate1 == mysql_to_uk_date($date)){
														if(!in_array($name, $timeArray)){
															echo "<option value='".str_replace(":","",$name)."'>".$name."</option>";
															$timeArray[] = $name;
															if($x == 0) { $x++; $firsttime = str_replace(":","",$name); }
														}
													}
												}
											}
										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}
								} else {
									if(!in_array($name, $timeArray)){
										echo "<option value='".str_replace(":","",$name)."'>".$name."</option>";
										$timeArray[] = $name;
										if($x == 0) { $x++; $firsttime = str_replace(":","",$name); }
									}
								}
							}
						}
					}
					?>
				</select>
			</div>
			<br />
		</div>

		<div class="visible-xs" style="margin-bottom:25px">
			<div class="col-md-3">
				<label> Filters: </label><br />
				<select class="select2" data-placeholder="Please select" style="width:100%" name="filteroverview" multiple id="filteroverview">
					<option value="signin"> Signed In </option>
					<option value="notsignin"> Not Signed In </option>
					<option value="signout"> Signed Out </option>
					<option value="notsignout"> Not Signed Out </option>
					<option value="medical"> Medical Information </option>
					<option value="paymentdue"> Payment Due </option>
				</select>
			</div>
		</div>
		<?php } ?>
			<div class="scrollable-area">
				<table class='table table-striped table-bordered bulk-checkboxes <?php echo ($print_view !== 1)?'hidden-xs':'' ?>' id="participants_overview">
						<thead>
							<tr>
								<?php
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th class="noprint"></th><?php
								}
								if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
									?><th></th><?php
								}
								?>
								<?php if (in_array($booking_info->register_type, array('adults_children', 'individuals', 'children','individuals_bikeability', 'children_bikeability'))) {
									switch($booking_info->register_type){
										case "individuals":
										case "individuals_bikeability":
											if(isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1){
												echo '<th></th>';
											}
											if(isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1){
												echo '<th></th>';
											}
											if(isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1){
												echo '<th></th>';
											}
											break;

										case "children":
										case "children_bikeability":
											if(isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1){
												echo '<th></th>';
											}
											if(isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1){
												echo '<th></th>';
											}
											if(isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1){
												echo '<th></th>';
											}
											break;

										default:
											if((isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1) || (isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1)){
												echo '<th></th>';
											}
											if((isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1) || (isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1)){
												echo '<th></th>';
											}
											if((isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1) || (isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1)){
												echo '<th></th>';
											}
											break;
									}?>
								<?php } ?>
								<th></th>
								<th></th>
								<?php
								$multiplier = 2;
								// if bikeability or shapeup, allow twice the number of columns per lesson
								if (substr($booking_info->register_type, -12) == '_bikeability' || substr($booking_info->register_type, -8) == '_shapeup') {
									$multiplier = 4;
								}
								if ($booking_info->type == 'booking') {
									$headings = array();
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$day = strtolower(date("l", strtotime($date)));
										if (in_array($day, $booking_info->days) && array_key_exists($date, $lesson_ids)) {
											$headings[$date] = '<th scope="col" colspan="' . count($lesson_ids[$date])*$multiplier . '">' . ucwords($day) . '<br />' . mysql_to_uk_date($date) . '</th>';
										}
										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}
									// sort and show
									ksort($headings);
									echo implode("\n", $headings);
								} else {
									foreach ($days as $day) {
										if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
											?><th colspan="<?php echo count($lesson_ids[$day])*$multiplier; ?>"><?php echo ucwords($day); ?></th><?php
										}
									}
									if ($print_view === 2) {
										foreach ($days as $day) {
											?><th></th><?php
										}
									}
								}
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th></th><?php
								}
								if (substr($booking_info->register_type, -8) == '_shapeup') {
									?><th colspan="2">5% Weight Loss</th>
									<th colspan="2">Target Weight</th>
									<th colspan="2">Current Weight Loss</th>
									<th>% Weight Lost</th><?php
								}
								?>
								<th></th>
							</tr>
							<tr>
								<?php
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th class="bulk-checkbox noprint">

									</th><?php
								}
								if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
									?><th></th><?php
								}
								?>
								<?php if (in_array($booking_info->register_type, array('adults_children', 'individuals', 'children','individuals_bikeability', 'children_bikeability'))) {
									switch($booking_info->register_type){
										case "individuals":
										case "individuals_bikeability":
											if(isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1){
												echo '<th></th>';
											}
											if(isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1){
												echo '<th></th>';
											}
											if(isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1){
												echo '<th></th>';
											}
											break;

										case "children":
										case "children_bikeability":
											if(isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1){
												echo '<th></th>';
											}
											if(isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1){
												echo '<th></th>';
											}
											if(isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1){
												echo '<th></th>';
											}
											break;

										default:
											if((isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1) || (isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1)){
												echo '<th></th>';
											}
											if((isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1) || (isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1)){
												echo '<th></th>';
											}
											if((isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1) || (isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1)){
												echo '<th></th>';
											}
											break;
										}?>
								<?php } ?>
								<th></th>
								<th></th>
								<?php
								$cols = 3;
								if ($booking_info->type == 'booking') {
									foreach ($lesson_ids as $date=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											// if cancelled, skip
											if (isset($cancellations[$lessonID][$date])) {
												?><th colspan="<?php echo $multiplier; ?>">&nbsp;</th><?php
											} else {
												?><th colspan="2"><?php echo $name; ?></th><?php
												if (substr($booking_info->register_type, -12) == '_bikeability') {
													?><th>

													</th><?php
												} else if (substr($booking_info->register_type, -8) == '_shapeup') {
													?><th>

													</th><?php
												}
											}

										}
									}
								} else {
									foreach ($lesson_ids as $day=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											if ($booking_info->type == 'booking') {
												$date = $block_info->startDate;
												while (strtotime($date) <= strtotime($block_info->endDate)) {
													if (strtolower(date("l", strtotime($date))) == $day) {
														// if cancelled, skip
														if (isset($cancellations[$lessonID][$date])) {
															?><th colspan="2">&nbsp;</th><?php
														} else {
															if (in_array($day, $days)) {
																if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
																	?><th colspan="2"><?php echo $name; ?></th><?php
																}
															}
															foreach ($days as $day) {
																?><th colspan="2"><?php echo $name; ?></th><?php
															}
														}
													}
													$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
												}
											} else {
												?><th colspan="2"><?php echo $name; ?></th><?php
											}
											if (substr($booking_info->register_type, -12) == '_bikeability') {
												?><th></th><?php
											} else if (substr($booking_info->register_type, -8) == '_shapeup') {
												?><th></th><?php
											}
										}
									}
									if ($print_view === 2) {
										foreach ($days as $day) {
											?><th></th><?php
										}
									}
								}
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th></th><?php
									$cols++;
								}
								if (substr($booking_info->register_type, -8) == '_shapeup') {
									?><th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th><?php

								}
								if ($print_view === 2) {
									?><th></th><?php

								} else {
									?><th></th><?php
								}
								?>
							</tr>
							<tr>
								<?php
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th class="bulk-checkbox noprint">
										<input type="checkbox" />
									</th><?php
								}
								if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
									?><th>Account Holder</th><?php
								}
								?>
								<th>Name</th>
								<?php if (in_array($booking_info->register_type, array('adults_children', 'individuals', 'children','individuals_bikeability', 'children_bikeability'))) {
									switch($booking_info->register_type){
										case "individuals":
										case "individuals_bikeability":
											if(isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1){
												echo '<td>Medical Notes</td>';
											}
											if(isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1){
												echo '<td>Disability Information</td>';
											}
											if(isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1){
												echo '<td>Behavioural Information</td>';
											}
											break;

										case "children":
										case "children_bikeability":
											if(isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1){
												echo '<td>Medical Notes</td>';
											}
											if(isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1){
												echo '<td>Disability Information</td>';
											}
											if(isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1){
												echo '<td>Behavioural Information</td>';
											}
											break;

										default:
											if((isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1) || (isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1)){
												echo '<td>Medical Notes</td>';
											}
											if((isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1) || (isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1)){
												echo '<td>Disability Information</td>';
											}
											if((isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1) || (isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1)){
												echo '<td>Behavioural Information</td>';
											}
											break;

									}
									?><?php
								}
								?>
								<th>Payment</th>
								<?php
								$cols = 3;
								if ($booking_info->type == 'booking') {
									foreach ($lesson_ids as $date=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											// if cancelled, skip
											if (isset($cancellations[$lessonID][$date])) {
												?><th colspan="<?php echo $multiplier; ?>">&nbsp;</th><?php
											} else {
												?><th colspan="">Sign In</th>
												<th colspan="">Sign Out</th><?php
												if (substr($booking_info->register_type, -12) == '_bikeability') {
													?><th>
														Level
													</th><?php
												} else if (substr($booking_info->register_type, -8) == '_shapeup') {
													?><th>
														Weight (kg)
													</th><?php
												}
											}
											$cols = $cols + $multiplier;
										}
									}
								} else {
									foreach ($lesson_ids as $day=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											if ($booking_info->type == 'booking') {
												$date = $block_info->startDate;
												while (strtotime($date) <= strtotime($block_info->endDate)) {
													if (strtolower(date("l", strtotime($date))) == $day) {
														// if cancelled, skip
														if (isset($cancellations[$lessonID][$date])) {
															?><th>&nbsp;</th><?php
														} else {
															if (in_array($day, $days)) {
																if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
																	?><th>Sign In</th>
																	<th>Sign Out</th><?php
																}
															}
															foreach ($days as $day) {
																?><th>Sign In</th>
																	<th>Sign Out</th><?php
																$cols += 2;
															}
														}
													}
													$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
												}
											} else {
												?><th>Sign In</th>
												<th>Sign Out</th><?php
											}
											if (substr($booking_info->register_type, -12) == '_bikeability') {
												?><th>Level</th><?php
												$cols++;
											} else if (substr($booking_info->register_type, -8) == '_shapeup') {
												?><th>Weight (kg)</th><?php
												$cols++;
											}
											$cols += 2;
										}
									}
									if ($print_view === 2) {
										foreach ($days as $day) {
											?><th><?php echo strtoupper(substr($day, 0, 1)); ?></th><?php
											$cols++;
										}
									}
								}
								if (substr($booking_info->register_type, -12) == '_bikeability') {
									?><th>Overall Level</th><?php
									$cols++;
								}
								if (substr($booking_info->register_type, -8) == '_shapeup') {
									?><th>kg</th>
									<th>lbs</th>
									<th>kg</th>
									<th>lbs</th>
									<th>kg</th>
									<th>lbs</th>
									<th></th><?php
									$cols += 7;
								}
								if ($print_view === 2) {
									?><th>Notes</th><?php
									$cols++;
								} else {
									?><th></th><?php
								}
								?>
							</tr>
						</thead>
						<tbody>
							<?php
							if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
								$field = 'contactID';
							} else {
								$field = 'childID';
							}

							if (count($items) > 0) {
								$prev_record = NULL;
								foreach ($items as $itemNumber => $item) {
									?>
									<tr>
										<?php
										if (substr($booking_info->register_type, -12) == '_bikeability') {
											?><td class="noprint"><input type="checkbox"></td><?php
										}
										if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										?><td>
												<?php

													echo $item->booker_first . ' ' . $item->booker_last;
													if (!empty($item->booker_title)) {
														echo ' (' . ucwords($item->booker_title) . ')';
													}

												?>
											</td>
											<td class="name">
												<?php
												$image_data = @unserialize($item->child_profile_pic);
												if ($image_data !== FALSE) {
													echo "<a href='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$item->childID)."' data-fancybox='gallery' class='profileimage' >".$item->child_first . ' ' . $item->child_last."</a>";
												}else{
													echo $item->child_first . ' ' . $item->child_last;
												}
												?>
											</td><?php
										} elseif($booking_info->register_type === "adults_children") {
											?><td class="name">
												<?php
												if($item->childID != ""){
													$image_data = @unserialize($item->child_profile_pic);
													if ($image_data !== FALSE) {
														echo "<a href='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$item->childID)."' data-fancybox='gallery' class='profileimage' >";
														echo $item->child_first . ' ' . $item->child_last;
														echo "</a>";
													}else{
														echo $item->child_first . ' ' . $item->child_last;
													}
												} else {
													$image_data = @unserialize($item->profile_pic);
													if ($image_data !== FALSE) {
														echo "<a href='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$item->contactID)."' data-fancybox='gallery' class='profileimage' >";
														echo $item->contact_first . ' ' . $item->contact_last;
														echo "</a>";
													}else{
														echo $item->contact_first . ' ' . $item->contact_last;
													}
												}
												if (!empty($item->contact_title)) {
													echo ' (' . ucwords($item->contact_title) . ')';
												}

												?>
											</td><?php
											}else {
											?><td class="name">
												<?php
												$image_data = @unserialize($item->profile_pic);
												if ($image_data !== FALSE) {
													echo "<a href='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$item->contactID)."' data-fancybox='gallery' class='profileimage' >";
													echo $item->contact_first . ' ' . $item->contact_last;
													if (!empty($item->contact_title)) {
														echo ' (' . ucwords($item->contact_title) . ')';
													}
													echo "</a>";
												}else{
													echo $item->contact_first . ' ' . $item->contact_last;
													if (!empty($item->contact_title)) {
														echo ' (' . ucwords($item->contact_title) . ')';
													}
												}
												?>
											</td><?php
										}
										?>
										<?php if (in_array($booking_info->register_type, array('adults_children', 'individuals', 'children','individuals_bikeability', 'children_bikeability'))) {
											switch($booking_info->register_type){
												case "individuals":
												case "individuals_bikeability":
													if(isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1){
														echo '<td class="name">'.$item->ac_medical.'</td>';
													}
													if(isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1){
														echo '<td class="name">'.$item->ac_disability_info.'</td>';
													}
													if(isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1){
														echo '<td class="name">'.$item->ac_behavioural_info.'</td>';
													}
													break;

												case "children":
												case "children_bikeability":
													if(isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1){
														echo '<td class="name">'.$item->medical.'</td>';
													}
													if(isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1){
														echo '<td class="name">'.$item->disability_info.'</td>';
													}
													if(isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1){
														echo '<td class="name">'.$item->behavioural_info.'</td>';
													}
													break;

												default:
													if($item->childID != "") {
														if(isset($participant_profile_display['medical']) && $participant_profile_display['medical'] == 1){
															echo '<td class="name">'.$item->medical.'</td>';
														}
														if(isset($participant_profile_display['disability_info']) && $participant_profile_display['disability_info'] == 1){
															echo '<td class="name">'.$item->disability_info.'</td>';
														}
														if(isset($participant_profile_display['behavioural_information']) && $participant_profile_display['behavioural_information'] == 1){
															echo '<td class="name">'.$item->behavioural_info.'</td>';
														}
													}else {
														if(isset($ac_profile_display['medical']) && $ac_profile_display['medical'] == 1){
															echo '<td class="name">'.$item->ac_medical.'</td>';
														}
														if(isset($ac_profile_display['disability_info']) && $ac_profile_display['disability_info'] == 1){
															echo '<td class="name">'.$item->ac_disability_info.'</td>';
														}
														if(isset($ac_profile_display['behavioural_information']) && $ac_profile_display['behavioural_information'] == 1){
															echo '<td class="name">'.$item->ac_behavioural_info.'</td>';
														}
													}
													break;

												}
										} ?>
										<td>
											<?php
											if ($item->participant_total == 0) {
												echo "<span style=\"color:green;\">Free</span>";
											} else if ($item->participant_balance == 0) {
												echo "<span style=\"color:green;\">Paid: " . currency_symbol() . number_format($item->participant_total, 2) . "</span>";
											} else {
												echo "<span style=\"color:red;\">Due: " . currency_symbol() . number_format($item->participant_balance, 2) . "</span>";
												echo " (Paid: " . currency_symbol() . number_format(($item->participant_total - $item->participant_balance), 2) . ")";
											}
											if (!empty($item->childcarevoucher_providerID)) {
												echo " (Childcare Voucher)";
											}
											?>
										</td>
										<?php
										if ($booking_info->type == 'booking') {
											foreach ($lesson_ids as $date=>$lessons) {
												foreach ($lessons as $lessonID=>$name) {
													?><td class="has_icon register_toggle">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="Attended">
																	<i class='far fa-check'></i>
																</a>
																<br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
																echo "</span>";
															} else {
																?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Attending">
																	<i class='far fa-times'></i>
																</a>
																<br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
																echo "</span>";

															}
														}
														?>
													</td>
													<td class="has_icon register_toggle">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='".$item->pin."' />";
															else
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='0' />";
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id = "<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
																echo "</span>";
															} else {
																?>
																<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id = "<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Signout">
																	<i class='far fa-times'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
																echo "</span>";

															}
														}
														?>
													</td><?php
													if (substr($booking_info->register_type, -12) == '_bikeability') {
														?><td><?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															?><select class="bikeability_level select2" data-action="<?php echo site_url('bookings/participants/bikeability/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] != 1) { echo ' disabled="disabled"'; } ?>>
																<option value="">-</option>
																<?php
																foreach ($bikeability_levels as $level => $label) {
																	?><option value="<?php echo $level; ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['bikeability_level'] === $level) { echo ' selected="selected"'; } ?>><?php echo $level . ' ' . $label; ?></option><?php
																}
																?>
															</select><?php
														}
														?></td><?php
													} else if (substr($booking_info->register_type, -8) == '_shapeup') {
														?><td><?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															?><input type="number" min="0" step=".1" class="shapeup_weight form-control" data-action="<?php echo site_url('bookings/participants/shapeup_weight/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>" value="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['shapeup_weight']; ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] != 1) { echo ' disabled="disabled"'; } ?>><?php
														}
														?></td><?php
													}
												}
											}
										} else {

											foreach ($lesson_ids as $day=>$lessons) {
												foreach ($lessons as $lessonID=>$name) {
													?><td class="has_icon register_toggle">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-success btn-sm' title="Attended">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
																echo "</span>";
															} else {
																?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Attending">
																	<i class='far fa-times'></i>
																</a><br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
																echo "</span>";
															}
														}
														?>
													</td>
													<td class="has_icon register_toggle">
													<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='".$item->pin."' />";
															else
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='0' />";
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-success btn-sm' title="Not Signout" id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
																echo "</span>";
															} else {
																?>
																<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Signin" id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>">
																	<i class='far fa-times'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
																echo "</span>";
															}
														}
													?>
													</td>
													<?php
													if (substr($booking_info->register_type, -12) == '_bikeability') {
														?><td><?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															?><select class="bikeability_level" data-action="<?php echo site_url('bookings/participants/bikeability/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] != 1) { echo ' disabled="disabled"'; } ?>>
																<option value="">-</option>
																<?php
																foreach ($bikeability_levels as $level => $label) {
																	?><option value="<?php echo $level; ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['bikeability_level'] === $level) { echo ' selected="selected"'; } ?>><?php echo $level . ' ' . $label; ?></option><?php
																}
																?>
															</select><?php
														}
														?></td><?php
													} else if (substr($booking_info->register_type, -8) == '_shapeup') {
														?><td><?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															?><input type="number" min="0" step=".1" class="shapeup_weight form-control" data-action="<?php echo site_url('bookings/participants/shapeup_weight/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>" value="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['shapeup_weight']; ?>"<?php if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] != 1) { echo ' disabled="disabled"'; } ?>><?php
														}
														?></td><?php
													}
												}
											}
											if ($print_view === 2) {
												foreach ($days as $day) {
													?><td></td><?php
												}
											}
										}
										if (substr($booking_info->register_type, -12) == '_bikeability') {
											?><td>
												<?php
												$action = 'bookings/participants/bikeability/';
												switch($booking_info->register_type) {
													case 'children_bikeability':
														$action .= 'child';
														break;
													case 'individuals_bikeability':
														$action .= 'contact';
														break;
												}
												$action .= '/' . $item->cartID . '/' . $item->$field;
												?>
												<select class="bikeability_level select2" data-action="<?php echo site_url($action); ?>">
													<option value="">-</option>
													<?php
													foreach ($bikeability_levels_overall as $level => $label) {
														?><option value="<?php echo $level; ?>"<?php if ($item->bikeability_level_overall == $level) { echo ' selected="selected"'; } ?>><?php echo $label; ?></option><?php
													}
													?>
												</select>
											</td><?php
										} else if (substr($booking_info->register_type, -8) == '_shapeup') {
											?><td class="target_loss_kg"></td>
											<td class="target_loss_lbs"></td>
											<td class="target_weight_kg"></td>
											<td class="target_weight_lbs"></td>
											<td class="current_loss_kg"></td>
											<td class="current_loss_lbs"></td>
											<td class="percent_lost"></td><?php
										}
										if ($print_view === 2) {

											$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
											if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }
											$notes = '';
											if($prefix == 'child')
												$notes = $item->child_notes;
											else if($prefix == 'contact')
												$notes = $item->contact_notes;


											?><?php
											echo "<td class=''> <textarea name='monitor_register_value' data-url='".site_url('bookings/participants/update_monitoring_field/'.$item->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$item->$field."/notes")."' rows='1' style='min-height:auto; min-width:auto; resize: none;' class='break-word'>".set_value("monitor_register_value_popup", $notes)."</textarea>";


											?></td><?php
										} else {
											?><td>
												<div class='text-right'>
													<a class='btn btn-success btn-sm' href='<?php echo site_url('participants/bookings/view/' . $item->cartID); ?>' title="View">
														<i class='far fa-globe'></i>
													</a>
													<a class='btn btn-warning btn-sm' href='<?php echo site_url('booking/cart/edit/' . $item->cartID . '/' . $blockID); ?>' title="Edit">
														<i class='far fa-pencil'></i>
													</a>
													<?php
													if ($item->cartID != $prev_record) {
														?> <a class='btn btn-info btn-sm' href='<?php echo site_url('participants/view/' . $item->familyID); ?>' title="Participant Account">
															<i class='far fa-users'></i>
														</a><?php
													}
													?>
												</div>
											</td><?php
										}
										?>
									</tr>
									<?php
									$prev_record = $item->cartID;
								}
							}
							if ($print_view === 2) {
								if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
									$cols--;
								}
								for ($i = 0; $i < 30; $i++) {
									?><tr class="printonly"><?php
										for ($col = 0; $col < $cols; $col++) {
											?><td>&nbsp;</td><?php
										}
									?></tr><?php
								}
							}
							?>
						</tbody>
						<?php
						if (substr($booking_info->register_type, -12) == '_bikeability') {
							?><tfoot class="bulk-actions noprint">
								<tr>
									<td></td>
									<?php
									if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										?><td></td><?php
									}
									?>
									<td></td>
									<td></td>
									<?php
									if ($booking_info->type == 'booking') {
										foreach ($lesson_ids as $date=>$lessons) {
											foreach ($lessons as $lessonID=>$name) {
												// if cancelled, skip
												echo str_repeat("<td></td>",$multiplier);
											}
										}
									} else {
										foreach ($lesson_ids as $day=>$lessons) {
											foreach ($lessons as $lessonID=>$name) {
												if ($booking_info->type == 'booking') {
													$date = $block_info->startDate;
													while (strtotime($date) <= strtotime($block_info->endDate)) {
														if (strtolower(date("l", strtotime($date))) == $day) {
															// if cancelled, skip
															if (isset($cancellations[$lessonID][$date])) {
																?><td></td><?php
															} else {
																if (in_array($day, $days)) {
																	if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
																		?><td></td><?php
																	}
																}
																foreach ($days as $day) {
																	?><td></td><?php
																}
															}
														}
														$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
													}
												} else {
													?><td></td><?php
												}
												if (substr($booking_info->register_type, -12) == '_bikeability') {
													?><td><select class="bulk select2">
														<option value="">Bulk Level</option>
														<?php
														foreach ($bikeability_levels as $key => $value) {
															?><option value="<?php echo $key; ?>"><?php echo $key . ' ' . $value; ?></option><?php
														}
														?>
														<option value="remove">Remove Level</option>
													</select></td><?php
												}
											}
										}
										if ($print_view === 2) {
											foreach ($days as $day) {
												?><td></td><?php
											}
										}
									}
									if (substr($booking_info->register_type, -12) == '_bikeability') {
										?><td><select class="bulk select2">
											<option value="">Bulk Level</option>
											<?php
											foreach ($bikeability_levels_overall as $key => $value) {
												?><option value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
											}
											?>
											<option value="remove">Remove Level</option>
										</select></td><?php
									}
									if ($print_view === 2) {
										?><td></td><?php
									} else {
										?><td></td><?php
									}
									?>
								</tr>
							</tfoot><?php
						}
						?>
					</table>

					<!-- Mobile View -->
					<?php if($print_view !== 1){
						?>
					<table class='table table-striped table-bordered bulk-checkboxes visible-xs' id="participants_overview1">
						<thead>
							<tr>
								<?php
								if ($booking_info->type == 'booking') {
									foreach ($lesson_ids as $date=>$lessons) {
										$timeArray = array();
										foreach ($lessons as $lessonID=>$name) {
											// if cancelled, skip
											if (isset($cancellations[$lessonID][$date])) {
												?><th colspan="<?php echo $multiplier; ?>">&nbsp;</th><?php
											} else {
												if(!in_array($name, $timeArray)){
													$timeArray[] = $name;
												?>
													<th colspan="1" class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th2_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name)) ?>"><?php echo $name; ?></th><?php
												}
											}

										}
									}
								} else {
									foreach ($lesson_ids as $day=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											if ($booking_info->type == 'booking') {
												$date = $block_info->startDate;
												while (strtotime($date) <= strtotime($block_info->endDate)) {
													$timeArray = array();
													if (strtolower(date("l", strtotime($date))) == $day) {
														// if cancelled, skip
														if (isset($cancellations[$lessonID][$date])) {
															?><th colspan="1">&nbsp;</th><?php
														} else {
															if (in_array($day, $days)) {
																if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
																	if(!in_array($name, $timeArray)){
																		$timeArray[] = $name;
																		?><th colspan="1" class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th2_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name)) ?>"><?php echo $name; ?></th><?php
																	}
																}
															}
															foreach ($days as $day) {
																if(!in_array($name, $timeArray)){
																	$timeArray[] = $name;
																	?><th colspan="1" class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th2_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>"><?php echo $name; ?></th><?php
																}
															}
														}
													}
													$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
												}
											} else {
												foreach ($days as $day1) {
													if (isset($lesson_ids[$day1]) && count($lesson_ids[$day1]) > 0 && $day1 == $day) {
														?><th colspan="1" class="<?php echo (((ucwords($day1).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th2_<?php echo ucwords($day1).str_replace(":","",$name) ?>"><?php echo $name; ?></th><?php
													}
												}
											}

										}
									}

								}

								$multiplier = 2;
								// if bikeability or shapeup, allow twice the number of columns per lesson
								if (substr($booking_info->register_type, -12) == '_bikeability' || substr($booking_info->register_type, -8) == '_shapeup') {
									$multiplier = 4;
								}


								if ($booking_info->type == 'booking') {
									$headings = array();
									$date = $block_info->startDate;
									while (strtotime($date) <= strtotime($block_info->endDate)) {
										$ddd = array();
										$day = strtolower(date("l", strtotime($date)));
										if (in_array($day, $booking_info->days) && array_key_exists($date, $lesson_ids)) {
											$tempdata = $lesson_ids[$date];
											if(count($tempdata) > 0){
												foreach($tempdata as $k => $v){
													if(!in_array($date, $ddd)){
														$ddd[] = $date;
														echo'<th scope="col" class="'.(((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$v)) != ($firstdate.$firsttime))?'d-none':'').' mobilescreen" id="th1_'.str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$v).'" colspan="'.count($lesson_ids[$date])*$multiplier.'">' .  mysql_to_uk_date($date) . '</th>';
													}
												}
											}else{
												if(!in_array($date, $ddd)){
													$ddd[] = $date;
													echo '<th scope="col" class="'.((str_replace("/","",mysql_to_uk_date($date)) != $firstdate)?'d-none':'').' mobilescreen" id="th1_'.str_replace("/","",mysql_to_uk_date($date)).'" colspan="'.count($lesson_ids[$date])*$multiplier.'">' .  mysql_to_uk_date($date) . '</th>';
												}
											}
										}

										$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
									}
									// sort and show
									//ksort($headings);
									//echo implode("\n", $headings);
								} else {
									foreach ($lesson_ids as $day=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											foreach ($days as $day1) {
												if (isset($lesson_ids[$day1]) && count($lesson_ids[$day1]) > 0 && $day1 == $day) {
													?><th colspan="" <?php echo (((ucwords($day1).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th1_<?php echo ucwords($day1).str_replace(":","",$name)?>"><?php echo ucwords($day1); ?></th><?php
												}
											}
										}
									}
								}

								?>
							</tr>

							<tr>
								<th>Name</th>

								<?php
								$cols = 3;
								if ($booking_info->type == 'booking') {
									foreach ($lesson_ids as $date=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											// if cancelled, skip
											if (isset($cancellations[$lessonID][$date])) {
												?><th colspan="<?php echo $multiplier; ?>">&nbsp;</th><?php
											} else {
												?><th colspan="" class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th3_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign In</th>
												<th colspan="" class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th4_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign Out</th><?php
											}
											$cols = $cols + $multiplier;
										}
									}
								} else {
									foreach ($lesson_ids as $day=>$lessons) {
										foreach ($lessons as $lessonID=>$name) {
											if ($booking_info->type == 'booking') {
												$date = $block_info->startDate;
												while (strtotime($date) <= strtotime($block_info->endDate)) {
													if (strtolower(date("l", strtotime($date))) == $day) {
														// if cancelled, skip
														if (isset($cancellations[$lessonID][$date])) {
															?><th>&nbsp;</th><?php
														} else {
															if (in_array($day, $days)) {
																if (isset($lesson_ids[$day]) && count($lesson_ids[$day]) > 0) {
																	?><th class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th3_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign In</th>
																	<th class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th4_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign Out</th><?php
																}
															}
															foreach ($days as $day) {
																?><th class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th3_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign In</th>
																	<th class="<?php echo (((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th4_<?php echo str_replace("/","",mysql_to_uk_date($date).str_replace(":","",$name))?>">Sign Out</th><?php
																$cols += 2;
															}
														}
													}
													$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
												}
											} else {
												foreach ($days as $day1) {
													if (isset($lesson_ids[$day1]) && count($lesson_ids[$day1]) > 0 && $day == $day1) {
														?><th class="<?php echo (((ucwords($day1).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th3_<?php echo ucwords($day1).str_replace(":","",$name)?>">Sign In</th>
														<th class="<?php echo (((ucwords($day).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'') ?> mobilescreen" id="th4_<?php echo ucwords($day1).str_replace(":","",$name)?>">Sign Out</th><?php
													}
												}
											}
											$cols += 2;
										}
									}

								}

								?>
							</tr>
						</thead>
						<tbody>
							<?php
							if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
								$field = 'contactID';
							} else {
								$field = 'childID';
							}
							$count = 0;
							if (count($items) > 0) {
								$prev_record = NULL;
								foreach ($items as $item) {
									$count++;

									if ($item->participant_total == 0) {
										$paid = '';
									} else if ($item->participant_balance == 0) {
										$paid = '';
									} else {
										$paid = 'paymentdue';
									}

									if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										$medical = $item->medical;
									} else {
										$medical = $item->contact_medical;
									}
									$medical = ($medical != '' && $medical != null)?'medical':'';

									$sign = $signout = "";
									if ($booking_info->type == 'booking') {
										foreach ($lesson_ids as $date=>$lessons) {
											foreach ($lessons as $lessonID=>$name) {
												if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
													if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] == 1) {
														$sign .= ' signin'.date("dmY",strtotime($date)).str_replace(":","",$name);
													}else{
														$sign .= ' notsignin'.date("dmY",strtotime($date)).str_replace(":","",$name);

													}
													if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout'] == 1) {
														$signout .= ' signout'.date("dmY",strtotime($date)).str_replace(":","",$name);;
													}else{
														$signout .= ' notsignout'.date("dmY",strtotime($date)).str_replace(":","",$name);;
													}
												}
											}
										}
									}else{
										foreach ($lesson_ids as $day=>$lessons) {
											foreach ($lessons as $lessonID=>$name) {
												if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
													if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] == 1) {
														$sign .= ' signin'. ucwords($day).str_replace(":","",$name);
													}else{
														$sign .= ' notsignin'. ucwords($day).str_replace(":","",$name);
													}
													if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout'] == 1) {
														$signout .= ' signout'. ucwords($day).str_replace(":","",$name);;
													}else{
														$signout .= ' notsignout'. ucwords($day).str_replace(":","",$name);;
													}
												}
											}
										}
									}
									?>
									<tr id="tr_<?php echo $count ?>" class="<?php echo $medical ?> <?php echo $paid ?> <?php echo $sign ?> <?php echo $signout ?>">
										<?php
										if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
										?>
											<td class="name">
												<?php
												echo "<a href='".site_url('bookings/participants/viewdetailoverview/' .$blockID."/".$item->childID)."' >".$item->child_first . ' ' . $item->child_last." </a>";

												?>
											</td><?php
										} elseif($booking_info->register_type === "adults_children") {
											?><td class="name">
												<?php
												if($item->childID != ""){
													echo "<a href='".site_url('bookings/participants/viewdetailoverview/' .$blockID."/".$item->childID)."' >".$item->child_first . ' ' . $item->child_last."</a>";
												} else {
													echo "<a href='".site_url('bookings/participants/viewdetailoverview/' .$blockID."/".$item->contactID)."' >".$item->contact_first . ' ' . $item->contact_last."</a>";
												}
												if (!empty($item->contact_title)) {
													echo ' (' . ucwords($item->contact_title) . ')';
												}

												?>
											</td><?php
											}else {
											?><td class="name">
												<?php
												echo "<a href='".site_url('bookings/participants/viewdetailoverview/' .$blockID."/".$item->contactID)."' >";
												echo $item->contact_first . ' ' . $item->contact_last;
												if (!empty($item->contact_title)) {
													echo ' (' . ucwords($item->contact_title) . ')';
												}
												echo "</a>";
												?>
											</td><?php
										}


										if ($booking_info->type == 'booking') {
											foreach ($lesson_ids as $date=>$lessons) {
												foreach ($lessons as $lessonID=>$name) {
													?><td class="has_icon register_toggle mobilescreen <?php echo ((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'' ?> <?php echo $count ?>td1_<?php echo str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name) ?>" id="">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="Attended">
																	<i class='far fa-check'></i>
																</a>
																<br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
																echo "</span>";
															} else {
																?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Attending">
																	<i class='far fa-times'></i>
																</a>
																<br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
																echo "</span>";

															}
														}
														?>
													</td>
													<td class="has_icon register_toggle mobilescreen <?php echo ((str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'' ?> <?php echo $count ?>td2_<?php echo str_replace("/","",mysql_to_uk_date($date)).str_replace(":","",$name) ?>" id="">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
															if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='".$item->pin."' />";
															else
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='0' />";
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-success btn-sm' title="NotSignout" id="click_mini_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
																echo "</span>";
															} else {
																?>
																<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Signout" id="click_mini_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>">
																	<i class='far fa-times'></i>
																</a><br /><span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
																echo "</span>";
															}
														}
														?>
													</td><?php
												}
											}
										} else {
											foreach ($lesson_ids as $day=>$lessons) {
												foreach ($lessons as $lessonID=>$name) {
													?><td class="has_icon register_toggle mobilescreen <?php echo ((ucwords($day).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'' ?> <?php echo $count ?>td1_<?php echo ucwords($day).str_replace(":","",$name) ?>" id="">
														<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-success btn-sm' title="Attended">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
																echo "</span>";
															} else {
																?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Attending">
																	<i class='far fa-times'></i>
																</a><br />
																<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
																echo "</span>";
															}
														}
														?>
													</td>
													<td class="has_icon register_toggle mobilescreen <?php echo ((ucwords($day).str_replace(":","",$name)) != ($firstdate.$firsttime))?'d-none':'' ?> <?php echo $count ?>td2_<?php echo ucwords($day).str_replace(":","",$name) ?>" id="">
													<?php
														if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
															if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='".$item->pin."' />";
															else
																echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='0' />";

															if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout'] == 1) {
																?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-success btn-sm' title="NotSignout" id="click_mini_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>">
																	<i class='far fa-check'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
																echo "</span>";
															} else {

																?>
																<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-warning btn-sm' title="Signout" id="click_mini_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>">
																	<i class='far fa-times'></i>
																</a><br />
																<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
																if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
																	echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
																echo "</span>";
															}
														}
													?>
													</td>
													<?php
												}
											}
										}
										?>
									</tr>
									<?php
								}
								echo "<input type='hidden' name='count' id='count' value='".$count."' />";
							}
							?>
						</tbody>
					</table>
					<?php } ?>
				</div>
			</div>
		</div>

		<?php
	}
}else{

	echo "
	<div style='margin-bottom:25px;'>
		<a href='".site_url('bookings/participants/print/' .$blockID)."'>Back</a>
	</div>";

	if (count($items) > 0) {
		$count = 0;
		$prev_record = NULL;
		foreach ($items as $itemNumber => $row) {
			if($row->childID == $familyID || $row->contactID == $familyID){
				echo "<div style='text-align:center; margin-bottom:20px;'>";
				$image_data = @unserialize($row->child_profile_pic);
				if ($image_data !== FALSE) {
					echo "<img src='".$this->crm_library->asset_url("attachment/participant_child/profile_pic/".$row->childID)."' width='150px' />
						<br />";
				}else{
					$image_data = @unserialize($row->profile_pic);
					if ($image_data !== FALSE) {
						echo "<img src='".$this->crm_library->asset_url("attachment/participant/profile_pic/".$row->contactID)."' width='150px' />
							<br />";
					}
				}
				$childName = '';
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$childName = $row->child_first . ' ' . $row->child_last;
				}
				$contactName = '';

				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					if ($row->cartID != $prev_record) {
						$contactName = $row->booker_first . ' ' . $row->booker_last;
						if (!empty($row->booker_title)) {
							$contactName .= ' (' . ucwords($row->booker_title) . ')';
						}
					}
				} elseif($booking_info->register_type === "adults_children") {
					if($row->childID != ""){
						$contactName = $row->child_first . ' ' . $row->child_last;
					} else {
						$contactName = $row->contact_first . ' ' . $row->contact_last;
					}
					if (!empty($row->contact_title)) {
						$contactName .= ' (' . ucwords($row->contact_title) . ')';
					}

				}else {
					$contactName = $row->contact_first . ' ' . $row->contact_last;
					if (!empty($row->contact_title)) {
						$contactName .= ' (' . ucwords($row->contact_title) . ')';
					}
				}


				$age = '';

				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$age = calculate_age($row->dob);
				}

				if ($booking_info->register_type === "adults_children") {
					if($row->contactID != ''){
						$age = calculate_age($row->contact_dob);
					}else{
						$age = calculate_age($row->child_dob);
					}
				}

				if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$age = calculate_age($row->contact_dob);
				}

				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					$medical = $row->medical;
				} else {
					$medical = $row->contact_medical;
				}

				$numbers = array();
				// use contact phone if set, else booker
				if (!empty($row->contactID)) {
					if (!empty($row->mobile)) {
						$numbers[] = $row->mobile;
					}
					if (!empty($row->phone)) {
						$numbers[] = $row->phone;
					}
					if (!empty($row->workPhone)) {
						$numbers[] = $row->workPhone;
					}
				} else {
					if (!empty($row->booker_mobile)) {
						$numbers[] = $row->booker_mobile;
					}
					if (!empty($row->booker_phone)) {
						$numbers[] = $row->booker_phone;
					}
					if (!empty($row->booker_workPhone)) {
						$numbers[] = $row->booker_workPhone;
					}
				}
				$photo = '';
				if (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup'))) {
					if ($row->type == 'child') {
						if($row->photoConsent == 1) {
							$photo = "<span class='btn btn-success btn-sm no-action' style='padding:0' title='Yes'>
											<i class='far fa-check'></i>
										</span>";
						} else {
							$photo = "<span class='btn btn-danger btn-sm no-action' style='padding:0' title='No'>
											<i class='far fa-times'></i>
										</span>";
						}
					}
				}
				$prefix = (in_array($booking_info->register_type, array('children', 'children_bikeability', 'children_shapeup')) ? "child" : "contact");
				if ($booking_info->register_type=="adults_children") { $prefix = (!empty($items[$itemNumber]->childID) ? "child" : "contact"); }

				$notes = "";
				if($prefix == "child"){
					$notes = $row->child_notes;
				}else if($prefix == "contact"){
					$notes = $row->contact_notes;
				}

				if (in_array($booking_info->register_type, array('individuals', 'individuals_bikeability', 'individuals_shapeup'))) {
					$field = 'contactID';
				} else {
					if ($booking_info->register_type=="adults_children") {
						$field = !empty($items[$itemNumber]->childID) ? "childID" : "contactID";
					}
					else {
						$field = 'childID';
					}
				}

				$monitor = '';
				for($i=1;$i<=20;$i++) {
					if (!empty($booking_info->{"monitoring".$i})) {
						if ($booking_info->{"monitoring".$i."_entry_type"}=="2") {
							$monitor.="<p style='padding:2px'><a href='javascript:void(0)' style='text-decoration:none; color:#000' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\")'><b>".$booking_info->{"monitoring".$i}.":</b> </a>".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i} : "")."</p>";
						}else {
						}
					}
				}
				$j = 0;
				for($i=1;$i<=20;$i++) {
					if (!empty($booking_info->{"monitoring".$i})) {
						if ($booking_info->{"monitoring".$i."_entry_type"}=="2") {
						}else {
							$j++;
							if($j > 2){
								if($j == 3)
									$monitor.= '<p style="text-align:center; font-size:20px" id="collapse"><a href="javascript:void(0)" onClick="hide_show(\'collapse\')"><b><i class="far fa-angle-down"> </i> </b></a></p>';
								$monitor.="<p style='padding:2px' class='expand d-none'><a href='javascript:void(0)' style='text-decoration:none' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\",\"".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/".$i)."\")'><b><i class='far fa-circle'></i>&nbsp;&nbsp;".$booking_info->{"monitoring".$i}.":</b></a> ".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."</p>";
							}else{
								$monitor.="<p style='padding:2px'><a href='javascript:void(0)' style='text-decoration:none' onClick='viewModel(\"".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."\",\"".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/".$i)."\")'><b><i class='far fa-circle'></i>&nbsp;&nbsp;".$booking_info->{"monitoring".$i}.":</b></a> ".(isset($items[$itemNumber]->{$prefix."_monitoring".$i}) ? $items[$itemNumber]->{$prefix."_monitoring".$i}: "")."</p>";
							}

						}
					}
				}
				$paid = '';
				if ($row->participant_total == 0) {
					$paid = "<p style=\"color:green;padding:2px\"><b>Free</b></p>";
				} else if ($row->participant_balance == 0) {
					$paid = "<p style=\"color:green;padding:2px\"><b>Paid</b>: " . currency_symbol() . number_format($row->participant_total, 2) . "</p>";
				} else {
					$paid = "<p style=\"color:red;padding:2px\"><b>Due</b>: " . currency_symbol() . number_format($row->participant_balance, 2) ." (Paid: " . currency_symbol() . number_format(($row->participant_total - $row->participant_balance), 2) . ")"."</p>";
				}
				if (!empty($row->childcarevoucher_providerID)) {
					$paid = " <p style='padding:2px'>(Childcare Voucher) </p>";
				}


				$monitor.= '<p id="expand" style="text-align:center; font-size:20px" class="d-none"><a href="javascript:void(0)" onClick="hide_show(\'expand\')"><b><i class="far fa-angle-up"> </i> </b></a></p>';

				echo "<b>".(($childName != "")?$childName:$contactName)."</b></div>
				<hr style='margin:0 0 5% 0'>
				<div>
					$paid
					<p style='padding:2px'> <b> Age: </b> ".$age."</p>
					<p style='padding:2px'> <b> Contact: </b> ".$contactName."</p>
					<p style='padding:2px'> <b> Telephone: </b> ".implode(", ", $numbers)."</p>
					<p style='padding:2px'> <b> Photo consent: </b> ".$photo."</p>
					<p style='padding:2px;'> <b> Medical: </b> ".$medical."</p>";
				echo $monitor;
				echo "<hr style='margin:5% 0 5% 0'>";
				echo "<p style='padding:2px'> <b> <a href='javascript:void(0)' style='text-decoration:none' onClick='viewModel(\"".$notes."\",\"".site_url('bookings/participants/update_monitoring_field/'.$row->cartID.'/'.$bookingID."/".$booking_info->accountID."/".$prefix."/".$row->$field."/notes")."\")'> Notes: </b> </a> ".$notes."</p>";
				echo "<hr style='margin:5% 0 5% 0'>";
				echo "</div>";
				$prev_record = $row->cartID;
			}
		}
	}

	?>

	<table class='table table-striped table-bordered' style="margin-top:20px;" id="participants_overview">
		<thead>
			<tr>
				<th> Date </th>
				<th> Time </th>
				<th> Sign in </th>
				<th> Sign out </th>
			</tr>
		</thead>
		<tbody>
			<?php

			if (count($items) > 0) {
				$count = 0;
				foreach ($items as $item) {
					$count++;
					if($familyID == $item->childID || $item->contactID == $familyID){

						if ($booking_info->type == 'booking') {
							foreach ($lesson_ids as $date=>$lessons) {
								foreach ($lessons as $lessonID=>$name) {
									if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
										echo "<tr>
										<td>".mysql_to_uk_date($date)."</td>
										<td>".$name."</td>";
										?><td class="has_icon register_toggle">
											<?php
											if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
												if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attended'] == 1) {
													?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-success btn-sm' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" title="Attended">
														<i class='far fa-check'></i>
													</a>
													<br />
													<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
														echo date("d-m-Y H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
													echo "</span>";
												} else {
													?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' class='btn btn-warning btn-sm' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" title="Attending">
														<i class='far fa-times'></i>
													</a>
													<br />
													<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time'] != NULL)
														echo date("d-m-Y H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['attend_time']));
													echo "</span>";

												}
											}
											?>
										</td>
										<td class="has_icon register_toggle">
											<?php
											if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$date])) {
												if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
													echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='".$item->pin."' />";
												else
													echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']."' value='0' />";
												if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout'] == 1) {
													?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
														<i class='far fa-check'></i>
													</a><br />
													<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
														echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
													echo "</span>";
												} else {

													?>
													<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Signout">
														<i class='far fa-times'></i>
													</a><br />
													<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['sessionID'] ?>"><?php
													if($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time'] != NULL)
														echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$date]['signout_time']));
													echo "</span>";

												}
											}
											?>
										</td><?php
										echo "</tr>";
									}
								}
							}
						} else {
							foreach ($lesson_ids as $day=>$lessons) {
								foreach ($lessons as $lessonID=>$name) {
									if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
									echo "<tr>
										<td>".mysql_to_uk_date($day)."</td>
										<td>".$name."</td>";
									?><td class="has_icon register_toggle">
										<?php

											if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attended'] == 1) {
												?><a href='<?php echo site_url('bookings/participants/unattend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-success btn-sm' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" title="Attended">
													<i class='far fa-check'></i>
												</a><br />
												<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
												if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
													echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
												echo "</span>";
											} else {
												?><a href='<?php echo site_url('bookings/participants/attend/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' class='btn btn-warning btn-sm' data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" title="Attending">
													<i class='far fa-times'></i>
												</a><br />
												<span class="time1_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
												if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time'] != NULL)
													echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['attend_time']));
												echo "</span>";
											}

										?>
									</td>
									<td class="has_icon register_toggle">
									<?php
										if (isset($booked_lessons[$item->cartID][$item->$field][$lessonID][$day])) {
											if(isset($participant_profile_display["pin"]) && $participant_profile_display["pin"] == 1)
												echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='".$item->pin."' />";
											else
												echo "<input type='hidden' id='hidden_".$booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']."' value='0' />";
											if ($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout'] == 1) {
												?><a href='<?php echo site_url('bookings/participants/notsignout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-success btn-sm' title="NotSignout">
													<i class='far fa-check'></i>
												</a><br />
												<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
												if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
													echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
												echo "</span>";
											} else {
												?>
												<a href='<?php echo site_url('bookings/participants/signout/' . $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID']); ?>' id="click_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" data-id="<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>" class='btn btn-warning btn-sm' title="Signout">
													<i class='far fa-times'></i>
												</a><br />
												<span class="time_<?php echo $booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['sessionID'] ?>"><?php
												if($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time'] != NULL)
													echo date("H:i:s",strtotime($booked_lessons[$item->cartID][$item->$field][$lessonID][$day]['signout_time']));
												echo "</span>";
											}
										}
									?>
									</td>
									</tr>
									<?php
									}
								}
							}
						}
					}
				}
			}
			?>
		</tbody>
	</table>

	<div class="modal fade" id="myModal1" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body">
					<div style="padding:5% 0;">
						<a href="javascript:void(0)" data-dismiss="modal" style="float:right; color:#000"> <i class="far fa-times-circle"> </i></a>
						<br />
						<input type="text" name="monitor_register_value_popup" style="width:100%;padding:5% 0; font-size:16px;" id="monitor_register_value_popup" />
					</div>
				</div>
			</div>
		</div>
	</div>

	<?php


}
?>
	<!-- Data Model for Profile Picture -->
	<div class="modal fade" id="myModal" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-body" id="verification" style="padding:15% 0">
					<div style="">
						<span style="padding:0 4%">
							<a href="javascript:void(0)" onClick="pickupPin()" class="btn btn-primary" style="width:40%; padding:5% 0; font-size:20px; text-align: center"> Pickup Pin </a>
						</span>
						<span style="padding:0 4%">
							<a href="javascript:void(0)" onClick="singoutModel()"  class="btn btn-primary" style="width:40%; padding:5% 0; font-size:20px; text-align: center"> Sign Out </a>
						</span>

					</div>
				</div>
				<div id="verification_pin" style="display:none">
					<div class="modal-body">
						<div style="text-align:center; color:#fff; background-color:#000; padding: 2% 0">
							<h4 class="modal-title">Please enter pick up PIN to sign out</h4>
						</div>
						<br />
						<input type="hidden" name="familyID" id="familyID" />
						<input type="hidden" name="URL" id="URL" />
						<span id="errormsg" style="color:red; display:none"> Please enter a valid PIN <br /></span>
						<div style="padding:5% 0">
							<span style="margin:4%;">
								<input type="text" autofocus name="pin1" class="inputs" id="pin1" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin2" class="inputs" id="pin2" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin3" class="inputs" id="pin3" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
							<span style="margin:4%">
								<input type="text" name="pin4" class="inputs" id="pin4" maxlength="1" style="padding:4% 2%; width:<?php echo ($print_view === 1)?'15%':'10%'?>; font-size:20px; text-align: center" />
							</span>
						</div>
					</div>
					<div class="modal-footer">
						<a href="javascript:void(0)" onClick="resetPin()" class="btn btn-default">CANCEL</button>
						<a href="javascript:void(0)" onClick="pinVerified()" class="btn btn-primary">OK</a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" id="hidden_flag_skip" value="0" />
<?php
if ($print_view === 2 || $print_view === 3) {
		?><script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
		<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
		<script src="<?php echo $this->crm_library->asset_url('dist/js/components/print.js'); ?>"></script>
		</body>
	</html><?php
}
