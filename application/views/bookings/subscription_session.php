<?php
if (!$in_crm) {
	?><h1 class="with-line"><?php echo $title; ?></h1><?php
}
echo form_open();
echo form_hidden(array(
	'process' => 1
));
if($register_type === 'adults_children'){
	echo form_hidden(array(
		'adults_children' => 1
	));
}
?>
<input type="hidden" name="already_in_cart" id="already_in_cart" value="1" />
<input type="hidden" name="initialFlag" id="initialFlag" value="0" />
<input type="hidden" name="participantFlag" id="participantFlag" value="0" />
<input type="hidden" name="cartID" id="cartID" value="<?php echo $cartID ?>" />
<fieldset class="card card-custom d-none">
		<div class="card-header">
			<div class="card-title">
				<?php if($register_type === 'adults_children'){ ?>
					<h4 class="with-line light">Select <?php echo $this->settings_library->get_label('adults_children', $this->cart_library->accountID); ?></h4>
				<?php }else{ ?>
					<h4 class="with-line light">Select <?php echo $this->settings_library->get_label('participants', $this->cart_library->accountID); ?></h4>
				<?php } ?>
			</div>
		</div>
		<div class='card-body'>
	<div id="select-participants" class="">
		<?php
		$flag = 1;$childFlag=1;$valId='';
		if (count($participants) > 0) {
			foreach ($participants as $participantID => $participant) {
				if(isset($participant->type) &&  $participant->type === 'parent' && $flag){
					echo '<h5 class="light">Select '.$this->settings_library->get_label('adults', $this->cart_library->accountID).'</h5>';
					$flag = 0;
					$valId = 'a';
				}
				if(isset($participant->type) &&  $participant->type === 'child' && $childFlag){
					echo '<h5 class="light">Select '.$this->settings_library->get_label('participants', $this->cart_library->accountID).'</h5>';
					$childFlag = 0;
					$identifierChildFlag = 1;
					$valId = 'p';
				}
				$data = array(
					'name' => 'participants[]',
					'value' => $participantID,
					'data-dob' => $participant->dob
				);
				if(count($subs) > 0){
					$data['data-subscription-id'] = $participant->subID;
				} else {
					$data['data-subscription-id'] = NULL;
				}
				if (in_array($participantID, $selected_participants)) {
					$data['checked'] = TRUE;
				}
				?><div class="checkbox d-block p-0 m-0">
				<label class="fancy-checkbox<?php if (empty($participant->dob)) { echo ' disabled'; } ?>">
					<?php
					if (!empty($participant->dob)) {
						echo form_checkbox($data); ?>
						<i class="far fa-circle unchecked"></i>
						<i class="far fa-check-circle checked"></i><?php
					} else {
						?><i class="far fa-ban"></i><?php
					}
					?>
					<span class="name"><?php echo $participant->first_name .  ' ' . $participant->last_name; if (empty($participant->dob)) { echo ' (Missing Date of Birth)'; } ?></span>
				</label>
				</div><?php
			}
		}
		?>
	</div>
	<script>
		var selected_lessons = <?php echo json_encode($selected_lessons); ?>;
		var already_booked_sessions = <?php echo json_encode($already_booked_sessions); ?>;
	</script>
	<p class="mt-4 mb-0">
		<?php if($register_type === 'adults_children'){ ?>
			<a href="<?php echo site_url($new_adults_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('adult', $this->cart_library->accountID); ?></a>&nbsp;&nbsp;<a href="<?php echo site_url($new_participants_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('participant', $this->cart_library->accountID); ?></a>
		<?php }else{?>
			<a href="<?php echo site_url($new_participants_link); ?>" class="lightbox">Add <?php echo $this->settings_library->get_label('participant', $this->cart_library->accountID); ?></a>
		<?php }?>
	</p>
	<script>
		function lightbox_callback(new_participant) {
			$.magnificPopup.close();
			var participant_html = '<div class="checkbox">';
			participant_html += '<label class="fancy-checkbox">';
			participant_html += '<input type="checkbox" name="participants[]" value="' + new_participant.participantID + '"  data-dob="' + new_participant.dob + '" data-subscription-id="" checked="checked">';
			participant_html += '<i class="far fa-circle unchecked"></i>';
			participant_html += '<i class="far fa-check-circle checked"></i>';
			participant_html += ' <span class="name">' + new_participant.name + '</span>';
			participant_html += '</label>';
			participant_html += '</div>';
			$('#select-participants').append(participant_html);
			update_participants(false);

			$('#select-participants :checkbox[value=' + new_participant.participantID + ']').trigger("change");
			location.reload();
		}
	</script>
		</div>
	</fieldset>

	<div id="step2">
	<fieldset class="card card-custom">
		<div class="card-body">
			<?php
			if(count($subs) > 0):
				?>
				<div id="subscriptions_section" class="d-none">
					<?php foreach($subs as $participantID => $subscription):
						?>
						<fieldset class="subscriptions <?php if($subscriptions_only) echo 'subs_only' ?>">
							<div id="subscription-<?php echo $participantID ?>" class="pb-4">
								<h4 class="with-line light">Select Subscriptions - <?php echo $participants[$participantID]->first_name . ' ' . $participants[$participantID]->last_name ?></h4>
								<?php
								$counter = 0;
								foreach ($subscription as $subID => $sub) {

									$data = array(
										'name' => 'subscriptions[' . $participantID . ']',
										'value' => $subID,
										'data-sub-price' => $sub['price'],
										'id' => 'sub-' . $participantID . '-' . $subID,
										'class' => 'subscriptions',
										'data-sub-now' => $sub['no_of_sessions_per_week']
									);
									if(array_key_exists($participantID, $selected_subs) && $selected_subs[$participantID] == $subID) {
										$data['checked'] = TRUE;
									}
									?><div class="checkbox">
									<label class="fancy-checkbox">
										<?php
										echo form_checkbox($data); ?>
										<i class="far fa-circle unchecked"></i>
										<i class="far fa-check-circle checked"></i>
										<span class="name"><?php echo $sub['label'] ?></span>
										<?php if($sub['no_of_sessions_per_week'] != "" && $sub['no_of_sessions_per_week'] != "0"){ ?>
										<br />
										<span id="sp-<?php echo $participantID . '-' . $subID ?>" <?php (array_key_exists($participantID, $selected_subs) && $selected_subs[$participantID] == $subID)?'style="display:none"':'' ?>><b> This subscription will allow you to attend <?php echo $sub['no_of_sessions_per_week'] ?> sessions every week, please select the sessions you would like to attend. You will be able to change these later. </b></span>
										<?php } ?>
										<input type="hidden" name="now-<?php echo $participantID . '-' . $subID ?>" id="now-<?php echo $participantID . '-' . $subID ?>" value="<?php echo $sub['no_of_sessions_per_week']?>" />
										<input type="hidden" name="cut_off-<?php echo $participantID . '-' . $subID ?>" id="cut_off-<?php echo $participantID . '-' . $subID ?>" value="<?php echo $sub['session_cut_off']?>" />

									</label>
									</div><?php
								}?>
							</div>
						</fieldset>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<div id="sessions">
			<h4 class="with-line light">Select Sessions</h4>
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true"><?php
				$i = 0;
				foreach ($blocks as $block) {
					$in_past = FALSE;
					$last_block_date = end($block->dates);
					if (strtotime($block->endDate . ' 23:59:59') < time()) {
						$in_past = TRUE;
					}
					$i++;
					?><div class="panel panel-default<?php if ($in_past === TRUE) { echo ' in-past'; } ?>" data-block="<?php echo $block->blockID; ?>">
						<div class="panel-heading" role="tab" id="heading<?php echo $i; ?>">
								<h4 class="panel-title">
									<a role="button"<?php if ($block->blockID != $blockID) { echo ' class="collapsed"'; } ?> data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i; ?>" aria-expanded="<?php if ($block->blockID == $blockID) { echo 'true'; } else { echo 'false'; } ?>" aria-controls="collapse<?php echo $i; ?>">
										<?php echo $block->block; ?>
										<?php

											// show block total if all sessions required or fixed auto discount
											if (intval($block->require_all_sessions) === 1 || ($block->autodiscount === 'fixed' && $block->autodiscount_amount > 0)) {
												?> <span class="block_totals">
													<span class="price" style="display:none"><?php echo currency_symbol(); ?><span>0</span></span>
													<span class="discounted_price" style="display:none"></span>
												</span><?php
											}

									?>
								</a>
							</h4>
						</div>
					<div id="collapse<?php echo $i; ?>" class="panel-collapse collapse <?php if ($block->blockID == $blockID) { echo 'show in'; } ?>" role="tabpanel" aria-labelledby="heading<?php echo $i; ?>">
						<div class="panel-body">
							<table class="sessions table table-striped table-bordered1 <?php echo $in_crm ? "in-crm" : ""; ?>" data-require_all_sessions="<?php echo intval($block->require_all_sessions); ?>"<?php
							if ($block->block_price !== NULL) {
								echo ' data-block_price="' . $block->block_price . '"';
							}
							if ($block->autodiscount !== 'off') {
								echo ' data-autodiscount_participants="0"';
							}
							if ($block->autodiscount === 'fixed' && $block->autodiscount_amount > 0) {
								echo ' data-fixed_autodiscount="' . $block->autodiscount_amount . '"';
							}
							?>>
								<thead<?php echo $in_crm ? " style=\"color: #464E5F;\"" : "";?>>
								<tr>
									<th></th>
									<?php
									if (count($block->lesson_columns) > 0) {
										foreach ($block->lesson_columns as $label => $lessonIDs) {
											$label_parts = explode('!#!', $label);
											$label = array();
											if (array_key_exists(2, $label_parts) && !empty($label_parts[2])) {
												$label[] = $label_parts[2];
											} else if (array_key_exists(2, $label_parts) && !empty($label_parts[1])) {
												$label[] = $label_parts[1];
											}
											$label[] = $label_parts[0];
											?><th scope="col" class="text-center"><?php echo implode('<br>', $label); ?></th><?php
										}
									}
									?>
								</tr>
								</thead>
								<tbody>
								<?php
								if (count($block->dates) > 0) {
									$lessons_shown = array();
									foreach ($block->dates as $date => $lessons) {
										if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
											// if all sessions already shown, skip
											if (count(array_keys($lessons)) == count(array_intersect(array_keys($lessons), $lessons_shown))) {
												continue;
											}
										}
										$days_shown[] = strtolower(date('l', strtotime($date)));
										?><tr data-date="<?php echo $date; ?>">
										<th scope="row"><?php
											if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
												echo date('l', strtotime($date));
											} else {
												echo date('D jS M', strtotime($date));
											}
											?></th>
										<?php
										foreach ($block->lesson_columns as $label => $lessonIDs) {
											$date_lessons = array_keys($lessons);
											$result = array_intersect($lessonIDs, $date_lessons);
											if (is_array($result) && count($result) > 0) {
												foreach ($result as $lessonID) {
													$this_lesson = $lessons[$lessonID];
													$show_as_sold_out =  !$in_crm || ($in_crm && !in_array($lessonID, array_keys(!is_array($selected_lessons) ? array() : $selected_lessons)));// if auto discount and booking tags, check match that of contact
													if (count($block->booking_tags) > 0 && count(array_intersect($block->booking_tags, $this->cart_library->contact_tags)) == 0) {
														// if not, set autodiscount to 0
														$this_lesson['autodiscount'] = 0;
													}
													// if fixed block price, set auto discount to 0
													if ($block->require_all_sessions == 1 && $block->block_price !== NULL) {
														$this_lesson['autodiscount'] = 0;
													}
													// if all weeks boooking, multiply price/autodiscount
													if ($block->booking_type == 'booking' && in_array($block->booking_requirement, array('all', 'remaining'))) {
														$lessons_shown[] = $lessonID;
														$multiplier = 0;
														$tmp_dates = $block->dates;
														foreach ($tmp_dates as $tmp_lessons) {
															if (array_key_exists($lessonID, $tmp_lessons)) {
																$multiplier++;
															}
														}
														$this_lesson['price'] = $this_lesson['price']*$multiplier;
														$this_lesson['autodiscount'] = $this_lesson['autodiscount']*$multiplier;
													}
													$availability_excluding = ($this_lesson['target_participants']-$this_lesson['actual_participants'])+(!isset($selected_lessons[$lessonID][$date]) ? 0 : count($selected_lessons[$lessonID][$date]));
													?><td class="text-center lesson<?php
													if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) {
														echo ' sold-out';
													}
													if ($this_lesson['cutoff'] < time()) {
														echo ' in-past';
													}
													?>" data-date="<?php echo $date;?>" data-lessonID="<?php echo $lessonID; ?>" data-blockID="<?php echo $block->blockID; ?>" data-min_age="<?php echo $this_lesson['min_age']; ?>" data-max_age="<?php echo $this_lesson['max_age']; ?>" data-price="<?php echo $this_lesson['price']; ?>" <?php echo ($in_crm ? 'data-available_excluding="'.$availability_excluding.'"' : ""); ?> data-available="<?php echo $this_lesson['available']; ?>"<?php if ($this_lesson['autodiscount'] !== FALSE) { echo ' data-autodiscount="' . $this_lesson['autodiscount'] . '"'; } ?> data-discount="<?php echo $this_lesson['discount']; ?>"<?php if ($this_lesson['cutoff'] < time()) {
														echo ' title="Past Booking"';
													} ?>>
													<?php
													if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) {
														?><span>Sold Out</span><?php
													} else if(!isset($selected_lessons[$lessonID][$date]) && isset($subscription_status) && $subscription_status != 'active'){
														?><span>-</span><?php
													}else {
													$attribute = "";
													if(isset($selected_lessons[$lessonID][$date]) && count($selected_lessons[$lessonID][$date]) > 0){
														foreach($selected_lessons[$lessonID][$date] as $participantID){
															foreach($subs[$participantID] as $index => $value) {
																if (count($subscription_status)> 0 && isset($subscription_status[$participantID]) && array_key_exists($index, $subscription_status[$participantID])) {
																	$last_action_date = $subscription_status[$participantID][$index]['valid'];
																	$frequency = $subs[$participantID][$index]['frequency'];
																	switch($frequency){
																		case "weekly":
																			$validity = strtotime("+7 day", strtotime($last_action_date));
																			$valid_till = date('d-m-Y', $validity);
																			if(strtotime($date) > strtotime($valid_till)){
																				$attribute .= $participantID.",";
																			}
																			break;
																		case "monthly":
																			$validity = strtotime("+1 month", strtotime($last_action_date));
																			$valid_till = date('d-m-Y', $validity);
																			if(strtotime($date) > strtotime($valid_till)){
																				$attribute .= $participantID.",";
																			}
																			break;
																		case "yearly":
																			$validity = strtotime("+1 year", strtotime($last_action_date));
																			$valid_till = date('d-m-Y', $validity);
																			if(strtotime($date) > strtotime($valid_till)){
																				$attribute .= $participantID.",";
																			}
																			break;
																	}
																}
															}
														}
													}
													$attribute = rtrim($attribute,",");
														?><label class="fancy-checkbox<?php if ($this_lesson['sold_out'] === TRUE && $show_as_sold_out) { echo " d-none hidden"; } ?>" subs-inactive="<?php echo $attribute;?>">
														<?php
														$data = array(
															'class' => 'lesson-toggle'
														);
														echo form_checkbox($data);
														?>
														<i class="far fa-circle unchecked"></i>
														<i class="far fa-check-circle checked"></i>
														<i class="far fa-ban disabled"></i>
														<br>
													<?php if(!$subscriptions_only){ ?>
														<span class="price" style="display:none"><?php
															if ($this_lesson['price'] > 0) {
																echo currency_symbol() . '<span>' . $this_lesson['price'] . '</span>';
															} else {
																echo 'Free';
															}
															?></span>
													<?php } ?>
														<span class="discounted_price" style="display:none"></span>
														<div class="participants">
														</div>
														<?php
												}
														if ($in_crm) {
															?>
															<div class="availability <?php echo $block->availability_status_class; ?>">
																<i class="fas fa-circle"></i> Availability - <?php echo $block->availability_status; ?>
															</div>
															<?php
														}
														?>
														</label>
													</td><?php
													break;
												}
											} else {
												echo '<td class="text-center no-lesson">-</div>';
											}
										}
										?></tr><?php
									}
								}
								?>
								</tbody>
							</table>
						</div>
					</div><?php
				}
				?></div>
			</div>
			<button id='add-to-cart' class='btn btn-primary'>
				Update Sessions
			</button>
		</div>
	</fieldset>

<?php

?>
<?php
echo form_close();
?>
