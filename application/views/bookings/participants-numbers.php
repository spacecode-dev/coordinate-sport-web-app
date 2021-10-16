<?php
display_messages();
if ($bookingID != NULL && !in_array($this->auth->user->department, array('coaching', 'fulltimecoach'))) {
	$data = array(
		'bookingID' => $bookingID,
		'tab' => $tab,
		'type' => $type,
		'is_project' => $booking_info->project,
		'type' => $booking_info->type
	);
	$this->load->view('bookings/tabs.php', $data);
}
if ($sessions->num_rows() == 0) {
	?>
	<div class="alert alert-info">
		No sessions found.
	</div>
	<?php
} else {
	echo form_open($page_base);
		?><div class='card card-custom'>
			<div class='table-responsive'>
				<table class='table table-striped table-bordered participant-numbers'>
					<thead>
						<tr>
							<th>Session</th>
							<?php
							$date = $block_info->startDate;
							while (strtotime($date) <= strtotime($block_info->endDate)) {
								$day = strtolower(date('l', strtotime($date)));
								if (array_key_exists($day, $booking_info->days)) {
									?><th><?php echo ucwords($day); ?><br /><?php echo mysql_to_uk_date($date); ?></th><?php
								}
								$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
							}
							?>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ($sessions->result() as $lesson) {
							$lesson->lessonIDs = explode(',', $lesson->lessonIDs);
							$lesson_desc = substr($lesson->startTime, 0, 5) . " to " . substr($lesson->endTime, 0, 5);
							if (!empty($lesson->activity)) {
								$lesson_desc .= " - " . $lesson->activity;
							} else if (!empty($lesson->activity_other)) {
								$lesson_desc .= " - " . $lesson->activity_other;
							}
							if (!empty($lesson->type)) {
								$lesson_desc .= " - " . $lesson->type;
							} else if (!empty($lesson->type_other)) {
								$lesson_desc .= " - " . $lesson->type_other;
							}
							?><tr>
								<td><?php echo $lesson_desc; ?></td>
								<?php
								$date = $block_info->startDate;
								while (strtotime($date) <= strtotime($block_info->endDate)) {
									$day = strtolower(date('l', strtotime($date)));
									if (array_key_exists($day, $booking_info->days)) {
										$actual_lessons = array();
										foreach ($lesson->lessonIDs as $lessonID) {
											if (in_array($lessonID, $booking_info->days[$day])) {
												// if cancelled, skip
												if (isset($cancellations[$lessonID][$date])) {
													$actual_lessons[] = 'Cancelled';
												} else {
													// check if session doesn't span full block
													if ((isset($partial_lessons[$lessonID]) && strtotime($date) < strtotime($partial_lessons[$lessonID]['startDate'])) || (isset($partial_lessons[$lessonID]) && strtotime($date) > strtotime($partial_lessons[$lessonID]['endDate']))) {
														// no lesson
														//$actual_lessons[] = 'No Session';
													} else {
														$actual_lesson = '<input type="number" class="form-control" name="attendance[' . $lessonID . '][' . $date . ']" value="';
														if (isset($attendance[$lessonID][$date])) {
															$actual_lesson .= intval($attendance[$lessonID][$date]);
														} else {
															$actual_lesson .= 0;
														}
														$actual_lesson .= '" />';
														$actual_lessons[] = $actual_lesson;
													}
												}
											}
										}
										if (count($actual_lessons) == 0) {
											?><td>-</td><?php
										} else {
											?><td><?php echo implode("<br />", $actual_lessons); ?></td><?php
										}
									}
									$date = date ("Y-m-d", strtotime("+1 day", strtotime($date)));
								}
								?>
							</tr><?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-2">
				<button class='btn btn-primary btn-submit' type="submit">
					Save
				</button>
			</div>
		</div>
		<?php
	echo form_close();
}
